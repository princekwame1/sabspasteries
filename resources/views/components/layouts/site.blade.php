@props(['title' => null, 'description' => null, 'image' => null])
@php
    $s = \App\Models\Setting::values();
    $pageTitle = $title ? $title.' | '.$s['business_name'] : $s['business_name'].' | '.$s['tagline'];
    $metaDescription = $description ?? site('hero_text');
    $nav = [
        // Labels and on/off switches: Admin → Site content → Header & footer
        'menu' => ['href' => route('menu'), 'active' => request()->routeIs('menu*')],
        'gallery' => ['href' => route('gallery'), 'active' => request()->routeIs('gallery')],
        'about' => ['href' => route('about'), 'active' => request()->routeIs('about')],
        'contact' => ['href' => route('contact'), 'active' => request()->routeIs('contact')],
    ];
    $nav = collect($nav)
        ->filter(fn ($item, $key) => site_shows("nav_{$key}_show") && site("nav_{$key}") !== '')
        ->map(fn ($item, $key) => $item + ['label' => site("nav_{$key}")])
        ->values()->all();
    $whatsapp = \App\Models\Setting::whatsappUrl('Hello '.$s['business_name'].', I would like to place an order.');
    $socials = collect(['instagram' => 'Instagram', 'tiktok' => 'TikTok', 'threads' => 'Threads', 'facebook' => 'Facebook'])->filter(fn ($l, $k) => $s[$k] ?? '');
@endphp
<!DOCTYPE html>
<html lang="en" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <script>document.documentElement.classList.add('js');try{if(sessionStorage.getItem('sp-preloaded'))document.documentElement.classList.add('no-preloader')}catch(e){}</script>
    {{-- Preloader styles are inline so the loader paints before the main stylesheet arrives --}}
    <style>
        #preloader{position:fixed;inset:0;z-index:200;display:none;place-items:center;background:#fff0f7;transition:opacity .6s ease,visibility .6s ease}
        .js #preloader{display:grid}
        .no-preloader #preloader{display:none}
        #preloader.is-done{opacity:0;visibility:hidden}
        #preloader .pl-ring{position:relative;display:grid;place-items:center;width:11rem;height:11rem}
        #preloader .pl-ring img{width:6.5rem;height:auto;animation:pl-pulse 1.6s ease-in-out infinite}
        #preloader .pl-dots{display:flex;justify-content:center;gap:.5rem;margin-top:1.75rem}
        #preloader .pl-dots span{width:.55rem;height:.55rem;border-radius:50%;background:#fa0379;animation:pl-bounce 1s ease-in-out infinite}
        #preloader .pl-dots span:nth-child(2){background:#82c20b;animation-delay:.15s}
        #preloader .pl-dots span:nth-child(3){background:#ff8fc2;animation-delay:.3s}
        @keyframes pl-pulse{0%,100%{transform:scale(1)}50%{transform:scale(1.06)}}
        @keyframes pl-bounce{0%,80%,100%{transform:translateY(0);opacity:.5}40%{transform:translateY(-8px);opacity:1}}
        @media (prefers-reduced-motion:reduce){#preloader *{animation:none!important}}
    </style>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $pageTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta property="og:title" content="{{ $pageTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:image" content="{{ $image ?? \App\Models\Setting::imageUrl('hero_image_path') }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="theme-color" content="#fff0f7">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="preload" href="{{ asset('fonts/jost-latin-wght-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="preload" href="{{ asset('fonts/great-vibes-latin-400-normal.woff2') }}" as="font" type="font/woff2" crossorigin>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    {{ $head ?? '' }}
</head>
<body class="min-h-screen bg-paper" x-data="{ menu: false, scrolled: window.scrollY > 24 }" @scroll.window="scrolled = window.scrollY > 24">
    {{-- Preloader: shown on the first page of a visit, hidden once the page has loaded --}}
    <div id="preloader" role="status" aria-live="polite">
        <div>
            <div class="pl-ring"><x-logo /></div>
            <div class="pl-dots" aria-hidden="true"><span></span><span></span><span></span></div>
            <span class="sr-only">Loading…</span>
        </div>
    </div>
    <script>
        (function () {
            var el = document.getElementById('preloader');
            if (!el || document.documentElement.classList.contains('no-preloader')) return;
            var hidden = false;
            function hide() {
                if (hidden) return;
                hidden = true;
                el.classList.add('is-done');
                try { sessionStorage.setItem('sp-preloaded', '1'); } catch (e) {}
                setTimeout(function () { el.remove(); }, 700);
            }
            if (document.readyState === 'complete') hide();
            else window.addEventListener('load', hide);
            setTimeout(hide, 5000); // never block the page on a slow image or script
        })();
    </script>

    <a href="#main" class="sr-only focus:not-sr-only focus:fixed focus:left-4 focus:top-4 focus:z-[100] btn-rose">Skip to content</a>

    {{-- Header --}}
    {{-- Turns brand pink once the page scrolls (.is-stuck); children restyle via group-[.is-stuck]/nav --}}
    <header class="group/nav fixed inset-x-0 top-0 z-50 bg-paper transition-colors duration-300 [&.is-stuck]:bg-rose-600"
        :class="scrolled && 'is-stuck shadow-[0_10px_30px_-18px_rgb(179_0_88/0.8)]'">
        <div class="page flex h-20 items-center justify-between gap-6">
            <a href="{{ route('home') }}" class="shrink-0 rounded-full transition-all duration-300 group-[.is-stuck]/nav:bg-white group-[.is-stuck]/nav:px-3 group-[.is-stuck]/nav:py-1" aria-label="{{ $s['business_name'] }} home"><x-logo class="h-12 w-auto transition-all duration-300 group-[.is-stuck]/nav:h-10" /></a>

            <nav class="hidden items-center gap-8 lg:flex" aria-label="Main">
                @foreach ($nav as $item)
                    <a href="{{ $item['href'] }}" @if ($item['active']) aria-current="page" @endif
                        class="py-2 text-[0.95rem] font-medium underline-offset-[6px] transition {{ $item['active'] ? 'text-rose-600 group-[.is-stuck]/nav:text-white group-[.is-stuck]/nav:underline group-[.is-stuck]/nav:decoration-2' : 'text-cocoa-900 hover:text-rose-600 group-[.is-stuck]/nav:text-white/85 group-[.is-stuck]/nav:hover:text-white' }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>

            <div class="flex items-center gap-2">
                @if ($s['instagram'])<a href="{{ $s['instagram'] }}" target="_blank" rel="noopener" class="hidden size-10 place-items-center rounded-full text-cocoa-800 transition hover:bg-sand hover:text-rose-600 group-[.is-stuck]/nav:text-white group-[.is-stuck]/nav:hover:bg-white/15 group-[.is-stuck]/nav:hover:text-white sm:grid" aria-label="Instagram{{ $s['social_handle'] ? ' '.$s['social_handle'] : '' }}" title="Instagram"><x-icon name="instagram" /></a>@endif
                @if ($s['tiktok'])<a href="{{ $s['tiktok'] }}" target="_blank" rel="noopener" class="hidden size-10 place-items-center rounded-full text-cocoa-800 transition hover:bg-sand hover:text-rose-600 group-[.is-stuck]/nav:text-white group-[.is-stuck]/nav:hover:bg-white/15 group-[.is-stuck]/nav:hover:text-white sm:grid" aria-label="TikTok" title="TikTok"><x-icon name="tiktok" /></a>@endif
                @if (site('nav_cta'))<a href="{{ route('order') }}" class="btn-rose hidden px-5 py-2.5 text-sm group-[.is-stuck]/nav:bg-white group-[.is-stuck]/nav:text-rose-700 group-[.is-stuck]/nav:hover:bg-cocoa-950 group-[.is-stuck]/nav:hover:text-white sm:inline-flex">{{ site('nav_cta') }}</a>@endif
                <button type="button" @click="menu = true" class="grid size-11 place-items-center rounded-full text-cocoa-900 hover:bg-sand group-[.is-stuck]/nav:text-white group-[.is-stuck]/nav:hover:bg-white/15 lg:hidden" aria-label="Open menu">
                    <x-icon name="menu" />
                </button>
            </div>
        </div>
    </header>

    {{-- Mobile menu --}}
    <div x-show="menu" x-cloak class="fixed inset-0 z-[60] lg:hidden" @keydown.escape.window="menu = false">
        <div x-show="menu" x-transition.opacity class="absolute inset-0 bg-cocoa-950/40 backdrop-blur-sm" @click="menu = false"></div>
        <div x-show="menu" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-x-full" x-transition:leave="transition duration-200 ease-in" x-transition:leave-end="translate-x-full"
            class="absolute inset-y-0 right-0 flex w-full max-w-sm flex-col bg-paper p-6 shadow-lift">
            <div class="flex items-center justify-between">
                <x-logo />
                <button type="button" @click="menu = false" class="grid size-11 place-items-center rounded-full hover:bg-white" aria-label="Close menu"><x-icon name="x" /></button>
            </div>
            <nav class="mt-10 flex flex-col divide-y divide-cocoa-900/8">
                <a href="{{ route('home') }}" class="px-2 py-3.5 text-2xl font-semibold {{ request()->routeIs('home') ? 'text-rose-600' : 'text-cocoa-950 hover:text-rose-600' }}">Home</a>
                @foreach ($nav as $item)
                    <a href="{{ $item['href'] }}" class="px-2 py-3.5 text-2xl font-semibold {{ $item['active'] ? 'text-rose-600' : 'text-cocoa-950 hover:text-rose-600' }}">{{ $item['label'] }}</a>
                @endforeach
            </nav>
            <div class="mt-auto grid gap-3">
                @if ($s['instagram'] || $s['tiktok'])
                    <div class="flex gap-3">
                        @if ($s['instagram'])<a href="{{ $s['instagram'] }}" target="_blank" rel="noopener" class="btn flex-1 bg-white py-3.5 text-cocoa-950 ring-1 ring-cocoa-900/10 hover:text-rose-600"><x-icon name="instagram" class="size-5" /> {{ $s['social_handle'] ?: 'Instagram' }}</a>@endif
                        @if ($s['tiktok'])<a href="{{ $s['tiktok'] }}" target="_blank" rel="noopener" class="btn flex-1 bg-white py-3.5 text-cocoa-950 ring-1 ring-cocoa-900/10 hover:text-rose-600"><x-icon name="tiktok" class="size-5" /> TikTok</a>@endif
                    </div>
                @endif
                @if (site('nav_cta'))<a href="{{ route('order') }}" class="btn-rose w-full py-3.5">{{ site('nav_cta') }}</a>@endif
                @if ($s['phone'])<a href="tel:{{ \App\Models\Setting::tel($s['phone']) }}" class="btn-light w-full py-3.5">{{ site('nav_call') }} {{ $s['phone'] }}</a>@endif
            </div>
        </div>
    </div>

    <main id="main">
        {{ $slot }}
    </main>

    {{-- Footer --}}
    <footer class="mt-28">
        {{-- Closing banner (Admin → Site content → Home page → Closing banner) --}}
        @if (site_shows('home_cta_show') && ! request()->routeIs('order*'))
            <div class="page">
                <div class="rounded-[2rem] bg-rose-600 px-8 py-12 text-white sm:px-12 lg:py-14" data-reveal>
                    <div class="flex flex-col items-start justify-between gap-8 lg:flex-row lg:items-center">
                        <div>
                            @if (site('home_cta_eyebrow'))<p class="font-script text-4xl text-white/90">{{ site('home_cta_eyebrow') }}</p>@endif
                            <p class="mt-1 max-w-2xl text-3xl font-semibold leading-tight sm:text-4xl">{{ rich(site('home_cta_title')) }}</p>
                            @if (site('home_cta_text'))<p class="mt-4 max-w-xl text-white/85">{{ site('home_cta_text') }}</p>@endif
                        </div>
                        <div class="flex shrink-0 flex-wrap gap-3">
                            @if (site('home_cta_button'))<a href="{{ route('order') }}" class="btn bg-white px-7 py-3.5 text-rose-700 hover:bg-cocoa-950 hover:text-white">{{ site('home_cta_button') }}</a>@endif
                            @if (site('home_cta_button2') && $whatsapp)<a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn px-7 py-3.5 text-white ring-1 ring-white/60 hover:bg-white/10">{{ site('home_cta_button2') }}</a>@endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="dark-abstract mt-20 text-white/65">
            <div class="page relative grid gap-12 py-16 md:grid-cols-2 lg:grid-cols-[1.4fr_1fr_1fr_1.3fr]">
                <div>
                    <x-logo light class="h-24 w-auto" />
                    @if (site('footer_text'))<p class="mt-6 max-w-sm text-sm leading-relaxed">{{ site('footer_text') }}</p>@endif
                    @if ($socials->isNotEmpty())
                        {{-- Follow buttons (moved here from the old "Follow the sweetness" home section) --}}
                        <div class="mt-6 flex flex-wrap gap-3">
                            @if ($s['instagram'])<a href="{{ $s['instagram'] }}" target="_blank" rel="noopener" class="btn bg-white px-5 py-2.5 text-sm text-cocoa-950 hover:bg-rose-100"><x-icon name="instagram" class="size-4.5" /> {{ $s['social_handle'] ?: 'Instagram' }}</a>@endif
                            @if ($s['tiktok'])<a href="{{ $s['tiktok'] }}" target="_blank" rel="noopener" class="btn bg-white/10 px-5 py-2.5 text-sm text-white hover:bg-white/20"><x-icon name="tiktok" class="size-4.5" /> TikTok</a>@endif
                            @foreach ($socials->except(['instagram', 'tiktok']) as $key => $label)
                                <a href="{{ $s[$key] }}" target="_blank" rel="noopener" class="btn bg-white/10 px-5 py-2.5 text-sm text-white hover:bg-white/20">{{ $label }}</a>
                            @endforeach
                        </div>
                    @endif
                </div>
                <div>
                    <p class="kicker text-rose-300">{{ site('footer_explore') }}</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        @foreach (\App\Models\Category::active()->whereHas('products', fn ($q) => $q->where('is_active', true))->get(['name', 'slug']) as $category)
                            <li><a href="{{ route('menu', ['category' => $category->slug]) }}" class="hover:text-rose-200">{{ $category->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
                <div>
                    <p class="kicker text-rose-300">{{ site('footer_company') }}</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        <li><a href="{{ route('order') }}" class="hover:text-rose-200">{{ site('footer_order_link') }}</a></li>
                        <li><a href="{{ route('gallery') }}" class="hover:text-rose-200">{{ site('nav_gallery') }}</a></li>
                        <li><a href="{{ route('about') }}" class="hover:text-rose-200">{{ site('nav_about') }}</a></li>
                        @if (site('footer_review_link'))<li><a href="{{ route('review') }}" class="hover:text-rose-200">{{ site('footer_review_link') }}</a></li>@endif
                        <li><a href="{{ route('contact') }}" class="hover:text-rose-200">{{ site('nav_contact') }}</a></li>
                        @if ($s['linktree'])<li><a href="{{ $s['linktree'] }}" target="_blank" rel="noopener" class="hover:text-rose-200">{{ site('footer_links_link') }}</a></li>@endif
                    </ul>
                </div>
                <div>
                    <p class="kicker text-rose-300">{{ site('footer_contact') }}</p>
                    <ul class="mt-5 space-y-3 text-sm">
                        @if ($s['phone'])<li><a href="tel:{{ \App\Models\Setting::tel($s['phone']) }}" class="hover:text-rose-200">{{ site('nav_call') }} {{ $s['phone'] }}</a></li>@endif
                        @if ($s['phone_2'])<li><a href="tel:{{ \App\Models\Setting::tel($s['phone_2']) }}" class="hover:text-rose-200">{{ $s['phone_2'] }}</a>@if ($s['phone_2_label'])<span class="block text-xs text-white/40">{{ $s['phone_2_label'] }}</span>@endif</li>@endif
                        @if ($whatsapp)<li><a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="hover:text-rose-200">WhatsApp {{ $s['whatsapp'] }}</a></li>@endif
                        @if ($s['email'])<li><a href="mailto:{{ $s['email'] }}" class="break-all hover:text-rose-200">{{ $s['email'] }}</a></li>@endif
                        @if ($s['address'])<li>{{ $s['address'] }}</li>@endif
                        @if ($s['hours'])<li>{{ $s['hours'] }}</li>@endif
                    </ul>
                </div>
            </div>
            <div class="page relative flex flex-col gap-2 border-t border-white/8 py-7 text-xs text-white/35 sm:flex-row sm:items-center sm:justify-between">
                <p>&copy; {{ date('Y') }} {{ $s['business_name'] }}. {{ site('footer_rights') }}</p>
                @if (site('footer_note'))<p>{{ site('footer_note') }}</p>@endif
            </div>
        </div>
    </footer>

    @php($chatOn = \App\Support\OrderAssistant::enabled() && ($s['orders_open'] ?? '1') !== '0')

    {{-- Follow-us pop-up: once per visitor, after a short wait, never on the order pages (Admin → Site content → Header & footer) --}}
    @if (site_shows('follow_show') && $socials->isNotEmpty() && ! request()->routeIs('order*'))
        <div x-data="followPopup({ delay: {{ max(0, (int) site('follow_delay')) }} })" x-show="open" x-cloak
            class="fixed inset-0 z-[70] grid place-items-center p-4" @keydown.escape.window="close()"
            role="dialog" aria-modal="true" aria-labelledby="follow-title">
            <div x-show="open" x-transition.opacity class="absolute inset-0 bg-cocoa-950/50 backdrop-blur-sm"></div>
            <div x-show="open" x-transition:enter="transition duration-300 ease-out" x-transition:enter-start="translate-y-4 scale-95 opacity-0"
                x-transition:leave="transition duration-150 ease-in" x-transition:leave-end="opacity-0"
                class="relative grid w-full max-w-md overflow-hidden rounded-[2rem] bg-paper text-center shadow-[0_30px_80px_-20px_rgb(46_31_24/0.5)] lg:max-w-4xl lg:grid-cols-[1fr_1.1fr] lg:text-left">
                <button type="button" x-ref="close" @click="close()" class="absolute right-4 top-4 z-10 grid size-10 place-items-center rounded-full bg-white/90 text-cocoa-900 hover:bg-white" aria-label="Close"><x-icon name="x" class="size-5" /></button>

                <div class="grid place-items-center bg-sand px-8 pt-8 lg:p-10">
                    <img src="{{ \App\Models\Setting::imageUrl('follow_image') }}" alt="" loading="lazy" class="mx-auto h-44 w-auto object-contain lg:h-auto lg:max-h-[26rem] lg:w-full drop-shadow-[0_20px_20px_rgb(46_31_24/0.15)]">
                </div>

                <div class="px-7 pb-7 pt-6 sm:px-9 lg:flex lg:flex-col lg:justify-center lg:px-12 lg:py-14">
                    @if (site('follow_eyebrow'))<p class="eyebrow">{{ site('follow_eyebrow') }}</p>@endif
                    <h2 id="follow-title" class="mt-1 text-2xl leading-tight text-cocoa-950 sm:text-[1.7rem] lg:text-4xl">{{ rich(site('follow_title')) }}</h2>
                    @if (site('follow_text'))<p class="mx-auto mt-3 max-w-sm text-ink/65 lg:mx-0 lg:mt-4 lg:text-lg">{{ site('follow_text') }}</p>@endif

                    <div class="mt-6 grid gap-2.5 lg:mt-8">
                        @foreach ($socials as $key => $label)
                            <a href="{{ $s[$key] }}" target="_blank" rel="noopener"
                                class="btn w-full py-3.5 {{ $loop->first ? 'bg-rose-600 text-white hover:bg-rose-700' : 'bg-white text-cocoa-950 ring-1 ring-cocoa-900/10 hover:text-rose-600 hover:ring-rose-300' }}">
                                @if (in_array($key, ['instagram', 'tiktok'], true))<x-icon :name="$key" class="size-5" />@endif
                                Follow on {{ $label }}@if ($key === 'instagram' && $s['social_handle']) <span class="opacity-80">{{ $s['social_handle'] }}</span>@endif
                            </a>
                        @endforeach
                    </div>

                    @if (site('follow_dismiss'))<button type="button" @click="close()" class="mt-4 text-sm text-ink/50 lg:self-start underline-offset-4 hover:text-cocoa-900 hover:underline">{{ site('follow_dismiss') }}</button>@endif
                </div>
            </div>
        </div>
    @endif

    {{-- WhatsApp shortcut (sits above the order chat button when that is on) --}}
    @if ($whatsapp)
        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" aria-label="Chat on WhatsApp"
            class="fixed right-5 z-40 grid place-items-center rounded-full bg-[#25d366] text-white shadow-lift transition hover:scale-105 {{ $chatOn ? 'bottom-24 size-12' : 'bottom-5 size-14' }}">
            <x-icon name="whatsapp" class="{{ $chatOn ? 'size-6' : 'size-7' }}" />
        </a>
    @endif

    {{-- Order chat (Claude): takes order details and saves them to Admin → Orders --}}
    @if ($chatOn)
        <div x-data="orderChat({
                url: @js(route('chat.send')),
                resetUrl: @js(route('chat.reset')),
                token: @js(csrf_token()),
                greeting: @js(site('chat_greeting')),
            })" @keydown.escape.window="open = false">
            <button type="button" @click="toggle()" :aria-expanded="open" aria-controls="order-chat"
                class="fixed bottom-5 right-5 z-50 flex h-14 items-center gap-2 rounded-full bg-rose-600 pl-4 pr-5 font-medium text-white shadow-lift transition hover:bg-rose-700">
                <span x-show="!open"><x-icon name="chat" class="size-6" /></span>
                <span x-show="open" x-cloak><x-icon name="x" class="size-6" /></span>
                <span x-text="open ? 'Close' : @js(site('chat_button'))">{{ site('chat_button') }}</span>
            </button>

            <section id="order-chat" x-show="open" x-cloak x-transition.origin.bottom.right role="dialog" aria-label="Order chat"
                class="fixed inset-x-3 bottom-24 top-24 z-50 flex flex-col overflow-hidden rounded-3xl bg-paper shadow-[0_30px_80px_-20px_rgb(46_31_24/0.45)] ring-1 ring-cocoa-900/10 sm:inset-x-auto sm:right-5 sm:top-auto sm:h-[36rem] sm:max-h-[calc(100vh-8rem)] sm:w-[24rem]">
                <header class="flex items-center gap-3 bg-rose-600 px-5 py-4 text-white">
                    <span class="grid size-10 shrink-0 place-items-center rounded-full bg-white"><x-logo class="h-7 w-auto" /></span>
                    <div class="min-w-0 flex-1">
                        <p class="font-semibold leading-tight">{{ $s['business_name'] }}</p>
                        @if (site('chat_subtitle'))<p class="text-xs text-white/80">{{ site('chat_subtitle') }}</p>@endif
                    </div>
                    <button type="button" @click="reset()" class="grid size-9 place-items-center rounded-full hover:bg-white/15" title="New chat" aria-label="Start a new chat"><x-icon name="refresh" class="size-4.5" /></button>
                </header>

                <div x-ref="log" class="flex-1 space-y-3 overflow-y-auto px-4 py-5" aria-live="polite">
                    <template x-for="(m, i) in messages" :key="i">
                        <div class="flex" :class="m.from === 'me' ? 'justify-end' : 'justify-start'">
                            <p class="max-w-[85%] whitespace-pre-line rounded-2xl px-4 py-2.5 text-[0.95rem] leading-relaxed"
                                :class="m.from === 'me' ? 'rounded-br-md bg-rose-600 text-white' : 'rounded-bl-md bg-white text-cocoa-950 ring-1 ring-cocoa-900/8'" x-text="m.text"></p>
                        </div>
                    </template>

                    <div x-show="sending" class="flex">
                        <span class="flex gap-1 rounded-2xl rounded-bl-md bg-white px-4 py-3.5 ring-1 ring-cocoa-900/8" aria-label="Typing">
                            <span class="size-1.5 animate-bounce rounded-full bg-cocoa-400"></span>
                            <span class="size-1.5 animate-bounce rounded-full bg-cocoa-400 [animation-delay:150ms]"></span>
                            <span class="size-1.5 animate-bounce rounded-full bg-cocoa-400 [animation-delay:300ms]"></span>
                        </span>
                    </div>

                    <div x-show="placed" x-cloak class="rounded-2xl bg-lime-50 px-4 py-3 text-sm text-lime-800 ring-1 ring-lime-200">
                        Order <strong x-text="placed"></strong> received. We’ll be in touch to confirm.
                    </div>

                    {{-- Starter prompts --}}
                    <div x-show="messages.length === 1 && !sending" class="flex flex-wrap gap-2 pt-1">
                        <template x-for="q in @js(array_values(array_filter(array_map('trim', preg_split('/\R/', site('chat_suggestions'))))))">
                            <button type="button" @click="send(q)" class="rounded-full bg-white px-3.5 py-1.5 text-sm text-cocoa-800 ring-1 ring-cocoa-900/12 transition hover:bg-rose-50 hover:text-rose-700 hover:ring-rose-300" x-text="q"></button>
                        </template>
                    </div>
                </div>

                <form @submit.prevent="send()" class="flex items-end gap-2 border-t border-cocoa-900/8 bg-white p-3">
                    <label for="order-chat-input" class="sr-only">Your message</label>
                    <textarea id="order-chat-input" x-ref="input" x-model="input" rows="1" maxlength="1000" placeholder="Type your message…"
                        @keydown.enter.prevent="if (!$event.shiftKey) send(); else input += '\n'"
                        class="max-h-32 min-h-11 flex-1 resize-none rounded-2xl border-0 bg-sand px-4 py-2.5 text-[0.95rem] text-ink placeholder:text-cocoa-900/40 focus:ring-2 focus:ring-rose-400 focus:outline-none"></textarea>
                    <button type="submit" :disabled="sending || !input.trim()" class="grid size-11 shrink-0 place-items-center rounded-full bg-rose-600 text-white transition hover:bg-rose-700 disabled:opacity-40" aria-label="Send"><x-icon name="send" class="size-5" /></button>
                </form>
                @if (site('chat_note'))<p class="bg-white px-4 pb-2.5 text-center text-[0.7rem] text-ink/40">{{ site('chat_note') }}</p>@endif
            </section>
        </div>
    @endif
</body>
</html>
