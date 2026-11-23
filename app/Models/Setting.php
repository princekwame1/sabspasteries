<?php

namespace App\Models;

use App\Support\SiteContent;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

/** Key/value site settings (brand, contact details, hero text, links), cached as one array. */
class Setting extends Model
{
    public $timestamps = false;

    public $incrementing = false;

    protected $primaryKey = 'key';

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    public const DEFAULTS = [
        'business_name' => 'Sabs Pastries',
        'tagline' => 'Cakes, cupcakes, doughnuts and sweet treats, made with love',
        'phone' => '+44 7846 796313',
        'phone_2' => '+233 54 372 0244',
        'phone_2_label' => 'Ghana (preorders only)',
        'whatsapp' => '+44 7846 796313',
        'email' => '',
        'address' => 'Online pastry shop. Order ahead for collection or delivery.',
        'hours' => '',
        'instagram' => 'https://www.instagram.com/sabs_pastries',
        'tiktok' => 'https://www.tiktok.com/@sabs_pastries',
        'threads' => 'https://www.threads.net/@sabs_pastries',
        'facebook' => '',
        'linktree' => 'https://linktr.ee/sabspastries',
        'social_handle' => '@sabs_pastries',
        'logo_path' => '',
        'notify_email' => '', // where new orders and messages are emailed (blank = no email)
        // Ordering
        'currency' => '£',
        'lead_days' => '3', // notice needed before the collection/delivery date, unless a product sets its own
        'flavours' => "Vanilla\nChocolate\nRed velvet\nLemon\nStrawberry\nMarble",
        'delivery_enabled' => '1',
        'delivery_note' => 'Delivery is available in selected areas for a small fee. We will confirm the cost with you.',
        'orders_open' => '1',
        'orders_closed_note' => 'We are fully booked at the moment. Message us on Instagram or WhatsApp and we will let you know when slots open.',
    ];

    /** @return list<string> flavour options, one per line in Settings */
    public static function flavours(): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/\R/', static::get('flavours')))));
    }

    /** Settings for this request (saved values over defaults). Kept in memory: views read it many times. */
    private static ?array $memo = null;

    private static ?int $memoApp = null; // tied to the app instance so tests and queue workers never see stale values

    public static function values(): array
    {
        if (static::$memoApp !== spl_object_id(app())) {
            static::$memo = null;
            static::$memoApp = spl_object_id(app());
        }

        return static::$memo ??= array_merge(
            self::DEFAULTS,
            SiteContent::defaults(),
            Cache::rememberForever('site.settings.saved', fn () => static::query()->pluck('value', 'key')->map(fn ($v) => (string) $v)->all()),
        );
    }

    public static function flushMemo(): void
    {
        static::$memo = null;
    }

    /** Editable site text with {business}, {tagline} and any extra {placeholders} filled in. */
    public static function text(string $key, array $replace = []): string
    {
        $v = static::values();
        $replace += ['business' => $v['business_name'] ?? '', 'tagline' => $v['tagline'] ?? ''];

        return strtr((string) ($v[$key] ?? ''), collect($replace)->mapWithKeys(fn ($r, $k) => ['{'.$k.'}' => (string) $r])->all());
    }

    /** Section switches from Site content (on unless turned off). */
    public static function shows(string $key): bool
    {
        return (static::values()[$key] ?? '1') !== '0';
    }

    /** Uploaded image for a Site content image field, or its built-in default. */
    public static function imageUrl(string $key): string
    {
        $path = static::get($key);

        return $path ? asset('storage/'.$path) : asset((SiteContent::fields()[$key]['default'] ?? '') ?: 'images/placeholder.svg');
    }

    public static function get(string $key, ?string $default = null): string
    {
        return (string) (static::values()[$key] ?? $default ?? '');
    }

    public static function put(array $values): void
    {
        foreach ($values as $key => $value) {
            static::query()->updateOrCreate(['key' => $key], ['value' => $value]);
        }
        Cache::forget('site.settings.saved');
        static::flushMemo();
    }

    /**
     * International digits for a phone number. Local numbers are read as UK (07xxx xxxxxx, 11 digits)
     * or Ghana (0xx xxx xxxx, 10 digits), the two places the business takes orders.
     */
    public static function internationalDigits(?string $number): string
    {
        $digits = preg_replace('/\D/', '', (string) $number);
        if (str_starts_with($digits, '00')) {
            return substr($digits, 2);
        }
        if (preg_match('/^0\d{10}$/', $digits)) {
            return '44'.substr($digits, 1);
        }
        if (preg_match('/^0\d{9}$/', $digits)) {
            return '233'.substr($digits, 1);
        }

        return $digits;
    }

    /** Dialable tel: link value, e.g. +447846796313. */
    public static function tel(?string $number): string
    {
        $digits = static::internationalDigits($number);

        return $digits === '' ? '' : '+'.$digits;
    }

    /** wa.me link for a WhatsApp number (defaults to the business number). */
    public static function whatsappUrl(?string $text = null, ?string $number = null): ?string
    {
        $digits = static::internationalDigits($number ?? static::get('whatsapp'));
        if ($digits === '') {
            return null;
        }

        return 'https://wa.me/'.$digits.($text ? '?text='.rawurlencode($text) : '');
    }

    public static function logoUrl(): ?string
    {
        $path = static::get('logo_path');

        return $path ? asset('storage/'.$path) : null;
    }

    public static function heroImageUrl(): string
    {
        $path = static::get('hero_image_path');

        return $path ? asset('storage/'.$path) : asset('images/hero-gold-pearl-cake.webp');
    }
}
