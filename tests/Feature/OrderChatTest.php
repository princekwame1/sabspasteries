<?php

namespace Tests\Feature;

use Anthropic\Client;
use App\Models\Order;
use App\Models\Product;
use App\Support\OrderAssistant;
use GuzzleHttp\Psr7\Response;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Tests\TestCase;

/** The order chat, run against canned Claude API responses (no network, no key). */
class OrderChatTest extends TestCase
{
    use RefreshDatabase;

    /** Fake HTTP transport: returns queued API responses and records each request body. */
    private object $http;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        config(['services.anthropic.key' => 'test-key', 'services.anthropic.enabled' => true]);

        $this->http = new class implements ClientInterface
        {
            /** @var list<array<string, mixed>> */
            public array $queue = [];

            /** @var list<array<string, mixed>> */
            public array $requests = [];

            /** @var list<string> */
            public array $headers = [];

            public function sendRequest(RequestInterface $request): ResponseInterface
            {
                $this->requests[] = json_decode((string) $request->getBody(), true);
                $this->headers[] = $request->getHeaderLine('anthropic-beta');

                return new Response(200, ['Content-Type' => 'application/json'], json_encode(array_shift($this->queue)));
            }
        };
        $this->app->instance(OrderAssistant::class, new OrderAssistant(new Client(
            apiKey: 'test-key',
            requestOptions: ['transporter' => $this->http, 'maxRetries' => 0],
        )));
    }

    /** @param list<array<string, mixed>> $content */
    private function apiReply(array $content, string $stop = 'end_turn'): array
    {
        return [
            'id' => 'msg_'.uniqid(), 'type' => 'message', 'role' => 'assistant', 'model' => 'claude-opus-5-5',
            'content' => $content, 'stop_reason' => $stop, 'stop_sequence' => null,
            'usage' => ['input_tokens' => 10, 'output_tokens' => 10],
        ];
    }

    private function saveOrderCall(array $overrides = []): array
    {
        $product = Product::first();

        return $this->apiReply([
            ['type' => 'thinking', 'thinking' => '', 'signature' => 'sig-abc'],
            ['type' => 'tool_use', 'id' => 'toolu_1', 'name' => 'save_order', 'input' => $overrides + [
                'product_id' => $product->id, 'item' => null, 'size' => null, 'flavour' => 'Vanilla', 'quantity' => 1,
                'occasion' => 'Birthday', 'cake_message' => 'Happy 30th Amy', 'design_notes' => 'Pink and gold', 'allergies' => null,
                'needed_on' => today()->addDays(14)->toDateString(), 'time_slot' => 'morning', 'fulfilment' => 'collection',
                'delivery_address' => null, 'name' => 'Amy Chat', 'phone' => '07123 456789', 'email' => null,
            ]],
        ], 'tool_use');
    }

    public function test_chat_is_hidden_without_an_api_key(): void
    {
        config(['services.anthropic.key' => null]);

        $this->get('/')->assertOk()->assertDontSee('Order by chat');
        $this->postJson('/chat', ['message' => 'hi'])->assertNotFound();
    }

    public function test_chat_shows_when_configured_and_replies(): void
    {
        $this->get('/')->assertOk()->assertSee('Order by chat');
        $this->http->queue[] = $this->apiReply([['type' => 'text', 'text' => 'Lovely! What date do you need it?']]);

        $this->postJson('/chat', ['message' => 'I want a birthday cake'])
            ->assertOk()
            ->assertJson(['reply' => 'Lovely! What date do you need it?', 'order' => null]);

        $sent = $this->http->requests[0];
        $this->assertSame('claude-opus-5-5', $sent['model']);
        $this->assertSame('save_order', $sent['tools'][0]['name']);
        $this->assertStringContainsString(Product::first()->name, $sent['system'][0]['text']);
        $this->assertTrue($sent['tools'][0]['strict']);
        $this->assertSame('object', $sent['tools'][0]['input_schema']['type']);
        $this->assertSame('low', $sent['output_config']['effort']);
        $this->assertSame('default', $sent['fallbacks']);
        $this->assertSame(['type' => 'ephemeral'], $sent['cache_control']);
        $this->assertStringContainsString('server-side-fallback-2026-07-01', $this->http->headers[0]);
    }

    public function test_confirmed_order_is_saved_through_the_tool(): void
    {
        $this->http->queue[] = $this->saveOrderCall();
        $this->http->queue[] = $this->apiReply([['type' => 'text', 'text' => 'All done!']]);

        $response = $this->postJson('/chat', ['message' => 'Yes, that is all correct'])->assertOk();

        $order = Order::where('name', 'Amy Chat')->firstOrFail();
        $this->assertSame('chat', $order->source);
        $this->assertSame('new', $order->status);
        $this->assertSame(Product::first()->name, $order->item);
        $response->assertJson(['reply' => 'All done!', 'order' => ['reference' => $order->reference()]]);

        // Second request echoes the assistant turn verbatim (thinking included) and the tool result.
        $followUp = $this->http->requests[1]['messages'];
        $this->assertSame('sig-abc', $followUp[1]['content'][0]['signature']);
        $this->assertSame('toolu_1', $followUp[2]['content'][0]['tool_use_id']);
        $this->assertFalse($followUp[2]['content'][0]['is_error']);
    }

    public function test_invalid_order_is_sent_back_to_the_model_not_saved(): void
    {
        $this->http->queue[] = $this->saveOrderCall(['needed_on' => today()->toDateString(), 'phone' => '']);
        $this->http->queue[] = $this->apiReply([['type' => 'text', 'text' => 'We need a bit more notice.']]);

        $this->postJson('/chat', ['message' => 'Tomorrow please'])->assertOk()->assertJson(['order' => null]);

        $this->assertDatabaseMissing('orders', ['name' => 'Amy Chat']);
        $result = $this->http->requests[1]['messages'][2]['content'][0];
        $this->assertTrue($result['is_error']);
        $this->assertStringContainsString('notice', $result['content']);
        $this->assertStringContainsString('phone', $result['content']);
    }

    public function test_one_chat_cannot_place_duplicate_orders(): void
    {
        $this->http->queue[] = $this->saveOrderCall();
        $this->http->queue[] = $this->apiReply([['type' => 'text', 'text' => 'Saved.']]);
        $this->postJson('/chat', ['message' => 'Confirm'])->assertOk();

        $this->http->queue[] = $this->saveOrderCall();
        $this->http->queue[] = $this->apiReply([['type' => 'text', 'text' => 'You already have an order.']]);
        $this->postJson('/chat', ['message' => 'Order it again'])->assertOk()->assertJson(['order' => null]);

        $this->assertSame(1, Order::where('name', 'Amy Chat')->count());
        $this->assertTrue(end($this->http->requests)['messages'][array_key_last(end($this->http->requests)['messages'])]['content'][0]['is_error']);
    }

    public function test_new_chat_clears_the_conversation(): void
    {
        $this->http->queue[] = $this->apiReply([['type' => 'text', 'text' => 'Hello!']]);
        $this->postJson('/chat', ['message' => 'Hi'])->assertOk();
        $this->postJson('/chat/reset')->assertOk();

        $this->http->queue[] = $this->apiReply([['type' => 'text', 'text' => 'Hello again!']]);
        $this->postJson('/chat', ['message' => 'Hi'])->assertOk();

        $this->assertCount(1, end($this->http->requests)['messages']);
    }
}
