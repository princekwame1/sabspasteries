<?php

namespace App\Support;

use App\Models\Order;
use App\Models\Product;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

/**
 * Validation rules and order creation shared by the order form and the order chat,
 * so both channels accept exactly the same orders.
 */
class OrderIntake
{
    public static function product(mixed $id): ?Product
    {
        return $id ? Product::active()->find($id) : null;
    }

    public static function noticeDays(?Product $product): int
    {
        return $product?->noticeDays() ?? (int) Setting::get('lead_days', '3');
    }

    public static function earliestDate(?Product $product): Carbon
    {
        return today()->addDays(self::noticeDays($product));
    }

    public static function deliveryEnabled(): bool
    {
        return Setting::get('delivery_enabled') !== '0';
    }

    /** @return array<string, mixed> */
    public static function rules(?Product $product): array
    {
        return [
            'product_id' => ['nullable', Rule::exists('products', 'id')->where('is_active', true)],
            'item' => [Rule::requiredIf(! $product), 'nullable', 'string', 'max:160'],
            'size' => ['nullable', 'string', 'max:120'],
            'flavour' => ['nullable', 'string', 'max:120'],
            'quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'occasion' => ['nullable', Rule::in(Order::OCCASIONS)],
            'cake_message' => ['nullable', 'string', 'max:160'],
            'design_notes' => ['nullable', 'string', 'max:3000'],
            'allergies' => ['nullable', 'string', 'max:300'],
            'needed_on' => ['required', 'date', 'after_or_equal:'.self::earliestDate($product)->toDateString()],
            'time_slot' => ['nullable', Rule::in(array_keys(Order::TIME_SLOTS))],
            'fulfilment' => ['required', Rule::in(self::deliveryEnabled() ? array_keys(Order::FULFILMENT) : ['collection'])],
            'delivery_address' => ['required_if:fulfilment,delivery', 'nullable', 'string', 'max:300'],
            'name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'heard_from' => ['nullable', Rule::in(Order::HEARD_FROM)],
        ];
    }

    /** @return array<string, string> */
    public static function messages(?Product $product): array
    {
        $notice = self::noticeDays($product);

        return [
            'item.required' => 'Please choose something from the menu or describe what you would like.',
            'needed_on.after_or_equal' => 'We need at least '.$notice.' days’ notice'.($product ? ' for this item' : '').'. The earliest date is '.self::earliestDate($product)->format('l j F').'.',
            'delivery_address.required_if' => 'Please add the delivery address.',
        ];
    }

    /**
     * Save a validated order and notify the owner.
     *
     * @param  array<string, mixed>  $data  validated fields
     * @param  list<string>|null  $images  stored reference image paths
     */
    public static function create(array $data, ?Product $product, string $source, ?array $images = null): Order
    {
        $data['item'] = $product?->name ?? $data['item'];
        $data['reference_images'] = $images ?: null;

        $order = Order::create($data + ['source' => $source, 'status' => 'new']);
        LeadNotifier::order($order);

        return $order;
    }
}
