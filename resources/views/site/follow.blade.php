{{--
    Hidden "Follow us" page, opened from printed QR codes (Admin → QR codes).
    Not linked from the site or the sitemap, and marked noindex. Links come from Admin → Settings.
--}}
@php
    $s = \App\Models\Setting::values();
    $handle = $s['social_handle'] ?: '';
    $networks = collect([
        'tiktok' => ['TikTok', 'tiktok', site('qr_tiktok')],
        'threads' => ['Threads', 'threads', site('qr_threads')],
        'facebook' => ['Facebook', 'facebook', site('qr_facebook')],
    ])->filter(fn ($n, $key) => $s[$key] ?? '');
    $whatsapp = \App\Models\Setting::whatsappUrl('Hello '.$s['business_name'].', I found you through your QR code.');
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="robots" content="noindex, nofollow">
    <title>Follow {{ $s['business_name'] }}</title>
    <meta name="theme-color" content="#fff0f7">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css'])
</head>
<body class="bg-paper">
<div class="pink-abstract min-h-dvh">
    <main class="mx-auto flex min-h-dvh w-full max-w-md flex-col items-center px-5 pb-10 pt-12 text-center">
        <div class="grid size-32 place-items-center rounded-full bg-white shadow-soft ring-4 ring-white">
            <x-logo class="h-24 w-auto" />
        </div>
        <p class="eyebrow mt-6">{{ site('qr_eyebrow') }}</p>
        <h1 class="mt-1 text-3xl font-semibold leading-tight text-cocoa-950">{{ site('qr_title') }}</h1>
        <p class="mt-3 max-w-xs text-ink/65">{{ site('qr_text') }}</p>

        <div class="mt-8 grid w-full grid-cols-1 gap-3">
            @if ($s['instagram'])
                {{-- Main call to action --}}
                <a href="{{ $s['instagram'] }}" target="_blank" rel="noopener"
                    class="group flex items-center gap-4 rounded-3xl bg-linear-to-br from-rose-500 to-rose-700 p-4 pr-5 text-left text-white shadow-[0_18px_40px_-16px_rgb(250_3_121/0.7)] transition active:scale-[0.98]">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-white/20"><x-icon name="instagram" class="size-6" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-lg font-semibold leading-tight">{{ site('qr_instagram') }}</span>
                        @if ($handle)<span class="block truncate text-sm text-white/80">{{ $handle }}</span>@endif
                    </span>
                    <span class="rounded-full bg-white px-4 py-2 text-sm font-semibold text-rose-700 transition group-hover:bg-rose-50">{{ site('qr_follow_button') }}</span>
                </a>
            @endif

            @foreach ($networks as $key => [$label, $icon, $hint])
                <a href="{{ $s[$key] }}" target="_blank" rel="noopener"
                    class="group flex items-center gap-4 rounded-3xl bg-white p-4 pr-5 text-left shadow-soft ring-1 ring-cocoa-900/5 transition hover:-translate-y-0.5 active:scale-[0.98]">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-cocoa-950 text-white"><x-icon :name="$icon" class="size-6" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold text-cocoa-950">{{ $label }}</span>
                        <span class="block text-sm leading-snug text-ink/55">{{ $hint }}{{ $handle ? ' · '.$handle : '' }}</span>
                    </span>
                    <span class="rounded-full bg-cocoa-950 px-4 py-2 text-sm font-semibold text-white transition group-hover:bg-rose-600">{{ site('qr_follow_button') }}</span>
                </a>
            @endforeach

            @if ($whatsapp)
                <a href="{{ $whatsapp }}" target="_blank" rel="noopener"
                    class="group flex items-center gap-4 rounded-3xl bg-white p-4 pr-5 text-left shadow-soft ring-1 ring-cocoa-900/5 transition hover:-translate-y-0.5 active:scale-[0.98]">
                    <span class="grid size-12 shrink-0 place-items-center rounded-2xl bg-[#25d366] text-white"><x-icon name="whatsapp" class="size-6" /></span>
                    <span class="min-w-0 flex-1">
                        <span class="block font-semibold text-cocoa-950">WhatsApp</span>
                        <span class="block text-sm leading-snug text-ink/55">{{ site('qr_whatsapp') }}</span>
                    </span>
                    <span class="rounded-full bg-[#25d366] px-4 py-2 text-sm font-semibold text-white">{{ site('qr_chat_button') }}</span>
                </a>
            @endif
        </div>

        <div class="mt-8 flex flex-wrap justify-center gap-3">
            <a href="{{ route('order') }}" class="btn-rose px-6 py-3">{{ site('qr_order_button') }}</a>
            <a href="{{ route('home') }}" class="btn-outline px-6 py-3">{{ site('qr_site_button') }}</a>
        </div>

        <p class="mt-auto pt-10 text-xs text-ink/40">&copy; {{ date('Y') }} {{ $s['business_name'] }}</p>
    </main>
</div>
</body>
</html>
