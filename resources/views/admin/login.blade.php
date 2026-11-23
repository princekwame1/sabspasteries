@php($business = \App\Models\Setting::get('business_name'))
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex">
    <title>Sign in | {{ $business }}</title>
    <link rel="icon" href="{{ asset('favicon.png') }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-paper">
    {{-- Phones: one pink panel behind the form. Large screens: brand panel on the left, form on the right. --}}
    <div class="pink-abstract grid min-h-dvh lg:grid-cols-[1.1fr_1fr] lg:bg-none lg:bg-paper lg:p-4 lg:before:hidden lg:after:hidden">
        {{-- Brand panel --}}
        <aside class="pink-abstract relative hidden flex-col rounded-[2rem] p-12 lg:flex">
            <a href="{{ route('home') }}" class="w-fit" aria-label="{{ $business }} website"><x-logo class="h-16 w-auto" /></a>

            <div class="my-auto max-w-lg">
                <p class="text-5xl font-semibold leading-[1.05] tracking-[-0.02em] text-cocoa-950">Your bakery, all in one place.</p>
                <p class="mt-5 text-lg text-ink/60">Everything you need to run {{ $business }}, from new orders to the words on your website.</p>

                {{-- What's inside the dashboard --}}
                <ul class="mt-10 flex flex-wrap gap-2.5">
                    @foreach ([['inbox', 'Orders', 'bg-rose-600'], ['calendar', 'Calendar', 'bg-lime-600'], ['store', 'Menu', 'bg-cocoa-900'], ['image', 'Gallery', 'bg-rose-400'], ['pen', 'Website', 'bg-lime-700']] as [$icon, $label, $tone])
                        <li class="glass flex items-center gap-2 rounded-2xl py-2 pl-2 pr-4 text-sm font-medium text-cocoa-900 shadow-soft">
                            <span class="grid size-8 place-items-center rounded-xl {{ $tone }} text-white"><x-icon :name="$icon" class="size-4" /></span> {{ $label }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </aside>

        {{-- Sign-in form --}}
        <main class="flex items-center justify-center px-5 py-10 lg:px-10">
            <div class="w-full max-w-sm">
                <form method="post" action="{{ route('admin.login') }}" x-data="{ show: false, sending: false }" @submit="sending = true"
                    class="rounded-[2rem] bg-white p-7 shadow-lift ring-1 ring-cocoa-900/5 sm:p-8 lg:bg-transparent lg:p-0 lg:shadow-none lg:ring-0">
                    @csrf
                    <div class="flex justify-center lg:hidden"><x-logo class="h-16 w-auto" /></div>

                    <p class="eyebrow mt-6 text-center lg:mt-0 lg:text-left">Welcome back</p>
                    <h1 class="mt-1 text-center text-3xl font-semibold text-cocoa-950 lg:text-left">Sign in to your dashboard</h1>

                    @error('email')
                        <div class="mt-6 flex items-start gap-2.5 rounded-2xl bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">
                            <x-icon name="help" class="mt-0.5 size-4 shrink-0" /> {{ $message }}
                        </div>
                    @enderror

                    <div class="mt-7 space-y-4">
                        <div>
                            <label class="label" for="email">Email</label>
                            <div class="relative">
                                <x-icon name="mail" class="pointer-events-none absolute left-4 top-1/2 size-[18px] -translate-y-1/2 text-cocoa-900/35" />
                                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                    class="input py-3.5 pl-11 @error('email') ring-red-300! @enderror" placeholder="you@example.com">
                            </div>
                        </div>
                        <div>
                            <label class="label" for="password">Password</label>
                            <div class="relative">
                                <x-icon name="key" class="pointer-events-none absolute left-4 top-1/2 size-[18px] -translate-y-1/2 text-cocoa-900/35" />
                                <input id="password" name="password" :type="show ? 'text' : 'password'" type="password" required autocomplete="current-password"
                                    class="input py-3.5 pl-11 pr-20" placeholder="Your password">
                                <button type="button" @click="show = !show" class="absolute right-2 top-1/2 -translate-y-1/2 rounded-lg px-3 py-1.5 text-xs font-medium text-cocoa-700 hover:bg-cocoa-100"
                                    :aria-pressed="show.toString()" x-text="show ? 'Hide' : 'Show'">Show</button>
                            </div>
                        </div>
                        <label class="flex w-fit cursor-pointer items-center gap-2.5 text-sm text-ink/70">
                            <input type="checkbox" name="remember" value="1" @checked(old('remember')) class="size-4 accent-rose-600"> Keep me signed in
                        </label>
                        <button class="btn-rose w-full py-3.5" :disabled="sending">
                            <svg x-show="sending" x-cloak class="size-4 animate-spin" viewBox="0 0 24 24" fill="none" aria-hidden="true"><circle cx="12" cy="12" r="9" stroke="currentColor" stroke-opacity=".3" stroke-width="3"/><path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round"/></svg>
                            <span x-text="sending ? 'Signing in…' : 'Sign in'">Sign in</span>
                            <x-icon name="arrow-right" class="size-4" x-show="!sending" />
                        </button>
                    </div>
                </form>

                <a href="{{ route('home') }}" class="mt-6 flex items-center justify-center gap-1.5 text-sm text-ink/55 hover:text-rose-600 lg:justify-start">
                    <x-icon name="chevron-left" class="size-4" /> Back to website
                </a>
            </div>
        </main>
    </div>
</body>
</html>
