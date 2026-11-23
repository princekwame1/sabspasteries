@props(['title' => 'Dashboard'])
@php
    $groups = [
        'Overview' => [
            ['Dashboard', 'admin.dashboard', 'chart', 'admin.dashboard'],
            ['Calendar', 'admin.calendar', 'calendar', 'admin.calendar'],
        ],
        'Customers' => [
            ['Orders', 'admin.orders.index', 'inbox', 'admin.orders.*', $badges['orders'] ?? 0],
            ['Messages', 'admin.messages.index', 'mail', 'admin.messages.*', $badges['messages'] ?? 0],
            ['Reviews', 'admin.testimonials.index', 'quote', 'admin.testimonials.*', $badges['reviews'] ?? 0],
        ],
        'Shop' => [
            ['Products', 'admin.products.index', 'store', 'admin.products.*'],
            ['Categories', 'admin.categories.index', 'tag', 'admin.categories.*'],
            ['Gallery', 'admin.gallery.index', 'image', 'admin.gallery.*'],
            ['FAQs', 'admin.faqs.index', 'help', 'admin.faqs.*'],
        ],
        'Website' => [
            ['Site content', 'admin.content', 'pen', 'admin.content*'],
            ['QR codes', 'admin.qr-codes.index', 'qr', 'admin.qr-codes.*'],
            ['Settings', 'admin.settings', 'cog', 'admin.settings'],
        ],
    ];
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>{{ $title }} | {{ \App\Models\Setting::get('business_name') }} admin</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-cream" x-data="{ nav: false }">
    {{-- Sidebar --}}
    <div x-show="nav" x-cloak x-transition.opacity class="fixed inset-0 z-40 bg-cocoa-950/50 lg:hidden" @click="nav = false"></div>
    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 -translate-x-full flex-col bg-cocoa-950 text-white/70 transition-transform duration-300 lg:translate-x-0" :class="nav && '!translate-x-0'">
        <div class="flex h-18 items-center px-6">
            <a href="{{ route('admin.dashboard') }}"><x-logo light class="h-12 w-auto" /></a>
        </div>
        <nav class="flex-1 space-y-6 overflow-y-auto px-3 py-4" aria-label="Dashboard">
            @foreach ($groups as $group => $items)
                <div>
                    <p class="px-3 pb-2 text-[11px] font-semibold uppercase tracking-[0.16em] text-white/35">{{ $group }}</p>
                    @foreach ($items as $item)
                        @php($active = request()->routeIs($item[3]))
                        <a href="{{ route($item[1]) }}" @if ($active) aria-current="page" @endif
                            class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ $active ? 'bg-white/10 text-white' : 'hover:bg-white/5 hover:text-white' }}">
                            <x-icon :name="$item[2]" class="size-[18px] {{ $active ? 'text-rose-300' : '' }}" />
                            <span class="flex-1">{{ $item[0] }}</span>
                            @if (! empty($item[4]))
                                <span class="rounded-full bg-rose-400 px-2 py-0.5 text-[11px] font-bold text-cocoa-950">{{ $item[4] }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endforeach
        </nav>
        <div class="p-3">
            <a href="{{ route('admin.account') }}" class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm hover:bg-white/5 hover:text-white {{ request()->routeIs('admin.account') ? 'bg-white/10 text-white' : '' }}">
                <span class="grid size-8 place-items-center rounded-full bg-rose-400 text-xs font-bold text-cocoa-950">{{ Str::substr(auth()->user()->name, 0, 1) }}</span>
                <span class="min-w-0 flex-1 truncate">{{ auth()->user()->name }}</span>
            </a>
            <form method="post" action="{{ route('admin.logout') }}">
                @csrf
                <button class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm hover:bg-white/5 hover:text-white"><x-icon name="logout" class="size-[18px]" /> Sign out</button>
            </form>
        </div>
    </aside>

    <div class="lg:pl-64">
        <header class="sticky top-0 z-30 flex h-18 items-center gap-4 bg-cream/85 px-5 backdrop-blur-xl sm:px-8">
            <button type="button" class="grid size-10 place-items-center rounded-full hover:bg-white lg:hidden" @click="nav = true" aria-label="Open menu"><x-icon name="menu" /></button>
            <h1 class="flex-1 truncate font-display text-2xl text-cocoa-950">{{ $title }}</h1>
            {{ $actions ?? '' }}
            <a href="{{ route('home') }}" target="_blank" class="btn-ghost hidden bg-white sm:inline-flex">View site <x-icon name="external" class="size-3.5" /></a>
        </header>

        <main class="px-5 pb-16 pt-2 sm:px-8">
            @if (session('status'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 5000)" x-transition
                    class="mb-6 flex items-center justify-between gap-4 rounded-2xl bg-cocoa-900 px-5 py-3.5 text-sm text-white shadow-soft" role="status">
                    <span>{{ session('status') }}</span>
                    <button type="button" @click="show = false" class="text-white/60 hover:text-white" aria-label="Dismiss"><x-icon name="x" class="size-4" /></button>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-6 rounded-2xl bg-red-50 px-5 py-3.5 text-sm text-red-700" role="alert">
                    Please fix the highlighted fields. {{ $errors->count() > 1 ? $errors->count().' problems found.' : $errors->first() }}
                </div>
            @endif
            {{ $slot }}
        </main>
    </div>
</body>
</html>
