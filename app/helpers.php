<?php

use App\Models\Setting;

if (! function_exists('site')) {
    /**
     * Editable site text from Admin → Site content (see App\Support\SiteContent).
     * {business} and {tagline} are filled in; pass extra placeholders like ['name' => 'Ama'].
     */
    function site(string $key, array $replace = []): string
    {
        return Setting::text($key, $replace);
    }
}

if (! function_exists('site_shows')) {
    /** Whether a section switched on/off in Admin → Site content is visible. */
    function site_shows(string $key): bool
    {
        return Setting::shows($key);
    }
}

if (! function_exists('rich')) {
    /**
     * Heading text with *accent words* shown in rose italics (escaped first, so it is safe to print).
     * e.g. "Where vision meets *excellence*" becomes "Where vision meets <em class="accent">excellence</em>".
     */
    function rich(?string $text): \Illuminate\Support\HtmlString
    {
        $safe = e((string) $text);

        return new \Illuminate\Support\HtmlString(preg_replace('/\*(.+?)\*/u', '<em class="accent">$1</em>', $safe));
    }
}

if (! function_exists('money')) {
    /** Amount with the currency symbol from Settings: "£45" or "£45.50". */
    function money(float|string|null $amount): string
    {
        $amount = (float) $amount;
        $symbol = Setting::get('currency', '£');

        return $symbol.number_format($amount, fmod($amount, 1.0) == 0.0 ? 0 : 2);
    }
}
