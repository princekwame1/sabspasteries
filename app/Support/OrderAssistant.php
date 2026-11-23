<?php

namespace App\Support;

use Anthropic\Client;
use App\Models\Faq;
use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Facades\Validator;

/**
 * Website order chat: Claude talks with the customer, collects the order details,
 * then calls the save_order tool, which runs the same validation as the order form.
 *
 * The conversation is kept as plain API-format arrays so it can live in the session
 * and be sent back unchanged on the next turn (thinking blocks included).
 */
class OrderAssistant
{
    /** Model turns allowed per customer message (each tool call costs one). */
    private const MAX_STEPS = 4;

    public function __construct(private Client $client) {}

    public static function enabled(): bool
    {
        return filled(config('services.anthropic.key')) && filter_var(config('services.anthropic.enabled'), FILTER_VALIDATE_BOOL);
    }

    public static function make(): self
    {
        return new self(new Client(
            apiKey: config('services.anthropic.key'),
            requestOptions: ['timeout' => 60, 'maxRetries' => 2],
        ));
    }

    /**
     * Send one customer message and run the model until it answers.
     *
     * @param  list<array<string, mixed>>  $history  API-format messages from earlier turns
     * @return array{history: list<array<string, mixed>>, reply: string, order: ?Order}
     */
    public function reply(array $history, string $text, ?int $savedOrderId = null): array
    {
        $history[] = ['role' => 'user', 'content' => $text];
        $order = null;
        $reply = '';

        for ($step = 0; $step < self::MAX_STEPS; $step++) {
            $response = $this->client->beta->messages->create(
                model: config('services.anthropic.model'),
                maxTokens: 4096,
                system: $this->systemPrompt(),
                messages: $history,
                tools: [$this->saveOrderTool()],
                outputConfig: ['effort' => 'low'],
                cacheControl: ['type' => 'ephemeral'],
                betas: ['server-side-fallback-2026-07-01'],
                fallbacks: 'default',
            );

            // Plain API-format blocks (thinking, text, tool_use, fallback...) kept verbatim for the next request.
            $content = json_decode(json_encode($response->content), true);
            $history[] = ['role' => 'assistant', 'content' => $content];

            if ($response->stopReason === 'refusal') {
                $reply = 'Sorry, I can’t help with that here. You can use the order form or message us on WhatsApp instead.';
                break;
            }

            $reply = trim(collect($content)->where('type', 'text')->pluck('text')->implode("\n\n"));

            if ($response->stopReason !== 'tool_use') {
                break;
            }

            $results = [];
            foreach ($content as $block) {
                if (($block['type'] ?? null) !== 'tool_use') {
                    continue;
                }
                [$result, $isError, $saved] = $block['name'] === 'save_order'
                    ? $this->saveOrder((array) $block['input'], $order?->id ?? $savedOrderId)
                    : ['Unknown tool.', true, null];
                $order ??= $saved;
                $results[] = ['type' => 'tool_result', 'tool_use_id' => $block['id'], 'content' => $result, 'is_error' => $isError];
            }
            $history[] = ['role' => 'user', 'content' => $results];
        }

        if ($reply === '') {
            $reply = $order
                ? 'Your order is in! Reference '.$order->reference().'. We will be in touch to confirm the details and price.'
                : 'Sorry, something went wrong on my side. Could you say that again?';
        }

        return ['history' => $history, 'reply' => $reply, 'order' => $order];
    }

    /**
     * Run the save_order tool: validate like the order form, then create the order.
     *
     * @param  array<string, mixed>  $input
     * @return array{0: string, 1: bool, 2: ?Order}  [result for the model, is_error, created order]
     */
    public function saveOrder(array $input, ?int $alreadySavedId = null): array
    {
        if ($alreadySavedId && ($existing = Order::find($alreadySavedId))) {
            return ['This chat already placed order '.$existing->reference().'. Do not create another one. Tell the customer the bakery will confirm, and that changes can be made when they get in touch.', true, null];
        }
        if (Setting::get('orders_open') === '0') {
            return ['Online orders are paused right now, so the order was not saved. Suggest WhatsApp or a phone call instead.', true, null];
        }

        $data = collect($input)
            ->only(array_keys(OrderIntake::rules(null)))
            ->map(fn ($v) => is_string($v) ? (trim($v) === '' ? null : trim($v)) : $v)
            ->all();
        $product = OrderIntake::product($data['product_id'] ?? null);
        if (! $product) {
            $data['product_id'] = null;
        }

        $validator = Validator::make($data, OrderIntake::rules($product), OrderIntake::messages($product));
        if ($validator->fails()) {
            return ['The order was NOT saved. Fix these with the customer, then call save_order again: '.implode(' ', $validator->errors()->all()), true, null];
        }

        $order = OrderIntake::create($validator->validated(), $product, 'chat');

        return ['Order saved. Reference '.$order->reference().'. Status: new (the bakery will contact the customer to confirm the design, price and deposit).', false, $order];
    }

    /** @return list<array<string, mixed>> */
    private function systemPrompt(): array
    {
        $s = Setting::values();
        $delivery = OrderIntake::deliveryEnabled();

        $menu = Product::active()->with('category:id,name')->orderBy('sort')->get()->map(function (Product $p) {
            $line = "- id {$p->id}: {$p->name}".($p->category ? " ({$p->category->name})" : '').' | '.$p->priceLabel().' | '.$p->noticeDays().' days notice';
            if ($sizes = $p->sizeList()) {
                $line .= ' | sizes: '.implode('; ', $sizes);
            }
            if ($p->summary) {
                $line .= ' | '.$p->summary;
            }

            return $line;
        })->implode("\n");

        $faqs = Faq::published()
            ->get()->map(fn ($f) => "Q: {$f->question}\nA: {$f->answer}")->implode("\n\n");

        $contact = collect([
            'WhatsApp' => $s['whatsapp'] ?? '',
            'Phone' => $s['phone'] ?? '',
            'Email' => $s['email'] ?? '',
            'Address' => $s['address'] ?? '',
            'Hours' => $s['hours'] ?? '',
        ])->filter()->map(fn ($v, $k) => "$k: $v")->implode("\n");

        $rules = <<<TXT
        You are the friendly order assistant on the {$s['business_name']} website, a home bakery making custom cakes, cupcakes, doughnuts and treats. You chat with customers to take their order details, then save the order with the save_order tool.

        How to talk:
        - Warm, brief and plain: one or two short sentences per message, and ask for one or two things at a time. Plain text only: no markdown, no bullet lists, no headings.
        - Use British English. Never invent prices, flavours, sizes or policies that are not listed below. If the price says "Price on request", say the bakery will confirm the price.
        - If asked something you do not know, say the bakery will answer when they confirm, or suggest WhatsApp.
        - Stay on the topic of the bakery and ordering. Politely decline anything else.

        What to collect, in a natural order:
        1. What they want: a menu item (use its id) or a description of a custom item, plus size, flavour and quantity where relevant.
        2. The occasion (optional), any message to write on the cake, design ideas, and allergies or dietary needs.
        3. The date needed (and optionally a time slot). Today is {TODAY}. Each item needs the listed days of notice; the default is {LEAD} days. If the date is too soon, say so and suggest the earliest date.
        4. Collection or delivery{DELIVERY}.
        5. Their name and phone number (required) and email (optional).

        Before saving, read back a one-line summary and ask them to confirm. Only call save_order after they confirm. If save_order returns an error, fix the problem with the customer and try again. After it succeeds, give them the order reference and explain the bakery will contact them to confirm the design, price and deposit. Reference photos can't be sent in this chat; suggest WhatsApp for photos.
        TXT;

        $rules = strtr($rules, [
            '{TODAY}' => today()->format('l j F Y').' ('.today()->toDateString().')',
            '{LEAD}' => (string) (int) ($s['lead_days'] ?? 3),
            '{DELIVERY}' => $delivery ? ' (delivery needs an address)' : ' (delivery is not offered right now: collection only)',
        ]);
        if (($s['orders_open'] ?? '1') === '0') {
            $rules .= "\n\nIMPORTANT: online orders are paused right now. Do not take orders; point customers to WhatsApp or phone.";
        }

        $facts = "Menu (id: name | price | notice | sizes | description):\n".($menu ?: '(no items listed; take custom orders as a description)')
            ."\n\nOccasions: ".implode(', ', Order::OCCASIONS)
            ."\nTime slots: ".collect(Order::TIME_SLOTS)->map(fn ($label, $key) => "$key = $label")->implode(', ')
            .($contact ? "\n\nContact details:\n".$contact : '')
            .($faqs ? "\n\nFrequently asked questions:\n".$faqs : '');

        return [['type' => 'text', 'text' => $rules."\n\n".$facts]];
    }

    /** @return array<string, mixed> */
    private function saveOrderTool(): array
    {
        $str = fn (string $description) => ['type' => ['string', 'null'], 'description' => $description];

        return [
            'name' => 'save_order',
            'description' => 'Save the customer’s confirmed order so the bakery receives it. Call only after the customer has confirmed the summary. Returns the order reference, or a list of problems to fix with the customer.',
            'strict' => true,
            'inputSchema' => [
                'type' => 'object',
                'additionalProperties' => false,
                'required' => ['product_id', 'item', 'size', 'flavour', 'quantity', 'occasion', 'cake_message', 'design_notes', 'allergies', 'needed_on', 'time_slot', 'fulfilment', 'delivery_address', 'name', 'phone', 'email'],
                'properties' => [
                    'product_id' => ['type' => ['integer', 'null'], 'description' => 'Menu item id, or null for a custom item described in "item".'],
                    'item' => $str('Description of the item when it is not on the menu; null when product_id is set.'),
                    'size' => $str('Chosen size, ideally one of the listed sizes.'),
                    'flavour' => $str('Flavour, if given.'),
                    'quantity' => ['type' => 'integer', 'description' => 'How many (1 for a single cake).'],
                    'occasion' => $str('One of the listed occasions, or null.'),
                    'cake_message' => $str('Text to write on the cake or plaque.'),
                    'design_notes' => $str('Colours, theme, decorations and any other design details.'),
                    'allergies' => $str('Allergies or dietary needs.'),
                    'needed_on' => ['type' => 'string', 'description' => 'Date needed, YYYY-MM-DD.'],
                    'time_slot' => $str('morning, afternoon or evening, or null.'),
                    'fulfilment' => ['type' => 'string', 'enum' => ['collection', 'delivery']],
                    'delivery_address' => $str('Full delivery address; null for collection.'),
                    'name' => ['type' => 'string', 'description' => 'Customer’s name.'],
                    'phone' => ['type' => 'string', 'description' => 'Customer’s phone number.'],
                    'email' => $str('Customer’s email, if given.'),
                ],
            ],
        ];
    }
}
