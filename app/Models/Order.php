<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class Order extends Model
{
    /** The order pipeline, in order. */
    public const STATUSES = [
        'new' => 'New',
        'confirmed' => 'Confirmed',
        'baking' => 'In the kitchen',
        'ready' => 'Ready',
        'completed' => 'Completed',
        'cancelled' => 'Cancelled',
    ];

    /** Orders still to be made and handed over. */
    public const OPEN = ['new', 'confirmed', 'baking', 'ready'];

    public const FULFILMENT = ['collection' => 'Collection', 'delivery' => 'Delivery'];

    public const TIME_SLOTS = ['morning' => 'Morning (9am - 12pm)', 'afternoon' => 'Afternoon (12pm - 4pm)', 'evening' => 'Evening (4pm - 7pm)'];

    /** Where the order came in. Website orders are set automatically; the rest are entered from the dashboard. */
    public const SOURCES = ['website' => 'Website', 'chat' => 'Website chat', 'instagram' => 'Instagram', 'tiktok' => 'TikTok', 'whatsapp' => 'WhatsApp', 'phone' => 'Phone call', 'in-person' => 'In person'];

    public const OCCASIONS = ['Birthday', 'Wedding', 'Anniversary', 'Baby shower / gender reveal', 'Christening / naming', 'Graduation', 'Valentine’s', 'Mother’s / Father’s Day', 'Christmas', 'Corporate', 'Just because', 'Other'];

    public const HEARD_FROM = ['Instagram', 'TikTok', 'Threads', 'A friend or family', 'Tasted at an event', 'Google', 'Other'];

    protected $fillable = [
        'name', 'phone', 'email', 'product_id', 'item', 'occasion', 'size', 'flavour', 'quantity', 'cake_message',
        'design_notes', 'allergies', 'reference_images', 'needed_on', 'time_slot', 'fulfilment', 'delivery_address',
        'source', 'heard_from', 'status', 'total', 'paid', 'notes',
    ];

    protected function casts(): array
    {
        return ['needed_on' => 'date', 'reference_images' => 'array', 'total' => 'decimal:2', 'paid' => 'decimal:2', 'quantity' => 'integer'];
    }

    protected static function booted(): void
    {
        static::deleted(fn (Order $o) => collect($o->reference_images)->each(fn ($p) => Storage::disk('public')->delete($p)));
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** Short reference customers can quote: SP-0042. */
    public function reference(): string
    {
        return 'SP-'.str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? ucfirst($this->status);
    }

    public function sourceLabel(): string
    {
        return self::SOURCES[$this->source] ?? ucfirst((string) $this->source);
    }

    public function fulfilmentLabel(): string
    {
        return self::FULFILMENT[$this->fulfilment] ?? ucfirst($this->fulfilment);
    }

    public function timeLabel(): ?string
    {
        return $this->time_slot ? (self::TIME_SLOTS[$this->time_slot] ?? $this->time_slot) : null;
    }

    /** What was ordered: "2 × Vintage Heart Cake". */
    public function itemLine(): string
    {
        $item = $this->item ?: ($this->product?->name ?? 'Custom order');

        return ($this->quantity > 1 ? $this->quantity.' × ' : '').$item;
    }

    public function balance(): ?float
    {
        return $this->total === null ? null : max(0, (float) $this->total - (float) $this->paid);
    }

    /** unpaid | deposit | paid | (null when no price agreed yet) */
    public function paymentState(): ?string
    {
        if ($this->total === null || (float) $this->total <= 0) {
            return (float) $this->paid > 0 ? 'deposit' : null;
        }
        if ((float) $this->paid <= 0) {
            return 'unpaid';
        }

        return (float) $this->paid >= (float) $this->total ? 'paid' : 'deposit';
    }

    public function paymentLabel(): ?string
    {
        return ['unpaid' => 'Unpaid', 'deposit' => 'Part paid', 'paid' => 'Paid in full'][$this->paymentState()] ?? null;
    }

    /** @return list<string> public URLs of the customer's inspiration photos */
    public function referenceUrls(): array
    {
        return array_map(fn ($p) => asset('storage/'.$p), $this->reference_images ?? []);
    }

    /** Summary for WhatsApp and emails. */
    public function summaryLines(): array
    {
        return array_values(array_filter([
            'Order '.$this->reference(),
            'Item: '.$this->itemLine(),
            $this->size ? 'Size: '.$this->size : null,
            $this->flavour ? 'Flavour: '.$this->flavour : null,
            $this->occasion ? 'Occasion: '.$this->occasion : null,
            $this->cake_message ? 'Message on cake: '.$this->cake_message : null,
            'Needed: '.$this->needed_on->format('D j M Y').($this->timeLabel() ? ', '.$this->timeLabel() : ''),
            $this->fulfilmentLabel().($this->fulfilment === 'delivery' && $this->delivery_address ? ' to '.$this->delivery_address : ''),
            'Name: '.$this->name,
        ]));
    }
}
