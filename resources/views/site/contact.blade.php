@php
    $s = \App\Models\Setting::values();
    $socials = collect(['instagram' => 'Instagram', 'tiktok' => 'TikTok', 'threads' => 'Threads', 'facebook' => 'Facebook'])->filter(fn ($l, $k) => $s[$k] ?? '');
@endphp
<x-layouts.site title="Contact us">
    {{-- Wording: Admin → Site content → Contact page --}}
    <x-page-hero :eyebrow="site('contact_eyebrow')" :title="site('contact_title')">
        @if (site('contact_text'))<p class="mt-6 max-w-xl text-lg text-ink/70">{{ site('contact_text') }}</p>@endif
    </x-page-hero>

    <section class="page grid gap-12 pt-14 lg:grid-cols-[1fr_1.2fr]">
        <div>
            <dl class="grid gap-4 sm:grid-cols-2">
                @if ($s['phone'])
                    <div class="rounded-3xl bg-white p-6 shadow-soft"><dt class="kicker">{{ site('contact_call_label') }}</dt><dd class="mt-2"><a href="tel:{{ \App\Models\Setting::tel($s['phone']) }}" class="text-lg font-semibold text-cocoa-950 hover:text-rose-600">{{ $s['phone'] }}</a></dd></div>
                @endif
                @if ($wa = \App\Models\Setting::whatsappUrl())
                    <div class="rounded-3xl bg-white p-6 shadow-soft"><dt class="kicker">{{ site('contact_whatsapp_label') }}</dt><dd class="mt-2"><a href="{{ $wa }}" target="_blank" rel="noopener" class="text-lg font-semibold text-cocoa-950 hover:text-rose-600">{{ $s['whatsapp'] }}</a></dd></div>
                @endif
                @if ($s['phone_2'])
                    <div class="rounded-3xl bg-white p-6 shadow-soft"><dt class="kicker">{{ $s['phone_2_label'] ?: 'Also on' }}</dt><dd class="mt-2"><a href="tel:{{ \App\Models\Setting::tel($s['phone_2']) }}" class="text-lg font-semibold text-cocoa-950 hover:text-rose-600">{{ $s['phone_2'] }}</a></dd></div>
                @endif
                @if ($s['email'])
                    <div class="rounded-3xl bg-white p-6 shadow-soft"><dt class="kicker">{{ site('contact_email_label') }}</dt><dd class="mt-2"><a href="mailto:{{ $s['email'] }}" class="break-all text-lg font-semibold text-cocoa-950 hover:text-rose-600">{{ $s['email'] }}</a></dd></div>
                @endif
                @if ($s['address'])
                    <div class="rounded-3xl bg-white p-6 shadow-soft sm:col-span-2"><dt class="kicker">{{ site('contact_address_label') }}</dt><dd class="mt-2 font-semibold text-cocoa-950">{{ $s['address'] }}</dd>@if ($s['hours'])<dd class="text-sm text-ink/55">{{ $s['hours'] }}</dd>@endif</div>
                @endif
            </dl>
            @if ($socials->isNotEmpty())
                <div class="mt-8">
                    <p class="kicker">{{ site('contact_follow_label') }} @if ($s['social_handle'])<span class="normal-case tracking-normal text-ink/60">{{ $s['social_handle'] }}</span>@endif</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @foreach ($socials as $key => $label)
                            <a href="{{ $s[$key] }}" target="_blank" rel="noopener" class="btn-light shadow-soft">{{ $label }}</a>
                        @endforeach
                    </div>
                </div>
            @endif
            <div class="mt-10 rounded-3xl bg-rose-50 p-9">
                <p class="font-display text-2xl text-cocoa-950">{{ site('contact_cta_title') }}</p>
                @if (site('contact_cta_text'))<p class="mt-2 text-ink/65">{{ site('contact_cta_text') }}</p>@endif
                <a href="{{ route('order') }}" class="btn-rose mt-6">{{ site('contact_cta_button') }}</a>
            </div>
        </div>

        <div class="panel p-6 sm:p-12">
            @if (session('sent'))
                <div class="py-12 text-center">
                    <span class="mx-auto grid size-16 place-items-center rounded-full bg-rose-100 text-rose-600"><x-icon name="check" class="size-7" /></span>
                    <p class="mt-5 font-display text-3xl text-cocoa-950">{{ site('contact_success_title') }}</p>
                    <p class="mt-3 text-ink/65">{{ session('sent') }}</p>
                    <a href="{{ route('menu') }}" class="btn-rose mt-8">{{ site('contact_success_button') }}</a>
                </div>
            @else
                <form method="post" action="{{ route('contact.send') }}" class="space-y-4">
                    @csrf
                    <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">
                    <div>
                        <label class="field-label" for="c-name">Your name</label>
                        <input id="c-name" name="name" value="{{ old('name') }}" required class="field bg-cocoa-50/60" autocomplete="name">
                        @error('name')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div>
                            <label class="field-label" for="c-phone">Phone</label>
                            <input id="c-phone" name="phone" type="tel" value="{{ old('phone') }}" required class="field bg-cocoa-50/60" autocomplete="tel">
                            @error('phone')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label" for="c-email">Email <span class="font-normal normal-case tracking-normal text-ink/40">(optional)</span></label>
                            <input id="c-email" name="email" type="email" value="{{ old('email') }}" class="field bg-cocoa-50/60" autocomplete="email">
                            @error('email')<p class="error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                    <div>
                        <label class="field-label" for="c-message">{{ site('contact_message_label') }}</label>
                        <textarea id="c-message" name="message" rows="6" required class="field bg-cocoa-50/60" placeholder="{{ site('contact_message_hint') }}">{{ old('message') }}</textarea>
                        @error('message')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <button class="btn-rose w-full py-4">{{ site('contact_button') }}</button>
                </form>
            @endif
        </div>
    </section>
</x-layouts.site>
