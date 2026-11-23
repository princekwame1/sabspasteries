@php
    $field = fn ($key, $label, $type = 'text', $hint = null) => compact('key', 'label', 'type', 'hint');
    $sections = [
        'Brand' => [
            $field('business_name', 'Business name'),
            $field('tagline', 'Tagline'),
        ],
        'Ordering' => [
            $field('currency', 'Currency symbol', 'text', 'Shown before prices, e.g. £ or GH₵.'),
            $field('lead_days', 'Notice needed (days)', 'number', 'Earliest date customers can pick. Products can set their own.'),
            $field('flavours', 'Flavours', 'textarea', 'One per line. Customers pick from these on the order form.'),
            $field('delivery_note', 'Delivery note', 'textarea', 'Shown when a customer chooses delivery.'),
            $field('orders_closed_note', 'Message when orders are paused', 'textarea'),
        ],
        'Contact details' => [
            $field('phone', 'Main phone'),
            $field('whatsapp', 'WhatsApp number', 'text', 'Powers the green chat button. Leave blank to hide it.'),
            $field('phone_2', 'Second phone', 'text', 'Optional. Leave blank to hide.'),
            $field('phone_2_label', 'Second phone label', 'text', 'e.g. Ghana (preorders only)'),
            $field('email', 'Public email', 'email'),
            $field('address', 'Location', 'text', 'e.g. London. Collection only'),
            $field('hours', 'Opening hours', 'text', 'Optional. Leave blank to hide.'),
            $field('notify_email', 'Email new orders to', 'email', 'Every order and message is emailed here. Needs mail settings in .env.'),
        ],
        'Social links' => [
            $field('instagram', 'Instagram URL', 'url'),
            $field('tiktok', 'TikTok URL', 'url'),
            $field('threads', 'Threads URL', 'url'),
            $field('facebook', 'Facebook URL', 'url'),
            $field('linktree', 'Linktree URL', 'url'),
            $field('social_handle', 'Social handle', 'text', 'Shown under the social links, e.g. @sabs_pastries'),
        ],
    ];
@endphp
<x-layouts.admin title="Settings">
    <form method="post" action="{{ route('admin.settings') }}" enctype="multipart/form-data" class="grid max-w-5xl gap-6">
        @csrf @method('put')

        <a href="{{ route('admin.content') }}" class="card flex items-center justify-between gap-4 p-5 transition hover:bg-rose-50">
            <span>
                <span class="block font-semibold text-cocoa-950">Looking for page wording or photos?</span>
                <span class="text-sm text-ink/55">Headlines, section text, buttons and photos are in Site content.</span>
            </span>
            <span class="btn-ghost shrink-0">Open Site content <x-icon name="arrow-right" class="size-4" /></span>
        </a>

        <section class="card grid gap-6 p-6 md:grid-cols-[14rem_1fr]">
            <div>
                <h2 class="font-semibold text-cocoa-950">Online orders</h2>
                <p class="mt-1 text-sm text-ink/50">Pause orders when you are fully booked or on holiday.</p>
            </div>
            <div class="grid gap-3">
                <label class="flex items-center gap-3 rounded-2xl bg-cocoa-50 px-4 py-3 text-sm">
                    <input type="hidden" name="orders_open" value="0"><input type="checkbox" name="orders_open" value="1" @checked(old('orders_open', $s['orders_open']) === '1') class="size-4 accent-rose-600">
                    <span><span class="font-semibold">Accept online orders</span> <span class="text-ink/50">When off, the order form shows the paused message instead.</span></span>
                </label>
                <label class="flex items-center gap-3 rounded-2xl bg-cocoa-50 px-4 py-3 text-sm">
                    <input type="hidden" name="delivery_enabled" value="0"><input type="checkbox" name="delivery_enabled" value="1" @checked(old('delivery_enabled', $s['delivery_enabled']) === '1') class="size-4 accent-rose-600">
                    <span><span class="font-semibold">Offer delivery</span> <span class="text-ink/50">When off, customers can only choose collection.</span></span>
                </label>
            </div>
        </section>

        <section class="card grid gap-6 p-6 md:grid-cols-[14rem_1fr]">
            <div>
                <h2 class="font-semibold text-cocoa-950">Logo</h2>
                <p class="mt-1 text-sm text-ink/50">PNG with a transparent background works best. Leave empty to use the built-in script logo.</p>
            </div>
            <div>
                @php($url = \App\Models\Setting::logoUrl())
                <div class="mb-3 grid h-28 max-w-sm place-items-center overflow-hidden rounded-2xl bg-cocoa-50">
                    @if ($url)<img src="{{ $url }}" alt="" class="max-h-16 w-auto">@else<x-logo />@endif
                </div>
                <input name="logo" type="file" accept="image/png,image/jpeg,image/webp" class="text-sm file:mr-3 file:rounded-full file:border-0 file:bg-cocoa-50 file:px-4 file:py-2 file:font-semibold file:text-cocoa-800" aria-label="Logo">
                @if ($url)<label class="mt-2 flex items-center gap-2 text-xs text-ink/60"><input type="checkbox" name="remove_logo" value="1" class="accent-cocoa-700"> Remove and use the default</label>@endif
                @error('logo')<p class="error">{{ $message }}</p>@enderror
            </div>
        </section>

        @foreach ($sections as $title => $fields)
            <section class="card grid gap-6 p-6 md:grid-cols-[14rem_1fr]">
                <h2 class="font-semibold text-cocoa-950">{{ $title }}</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    @foreach ($fields as $f)
                        <div class="{{ $f['type'] === 'textarea' ? 'sm:col-span-2' : '' }}">
                            <label class="label" for="{{ $f['key'] }}">{{ $f['label'] }}</label>
                            @if ($f['type'] === 'textarea')
                                <textarea id="{{ $f['key'] }}" name="{{ $f['key'] }}" rows="{{ $f['key'] === 'flavours' ? 6 : 2 }}" class="input">{{ old($f['key'], $s[$f['key']]) }}</textarea>
                            @else
                                <input id="{{ $f['key'] }}" name="{{ $f['key'] }}" type="{{ $f['type'] }}" @if ($f['type'] === 'number') min="0" @endif value="{{ old($f['key'], $s[$f['key']]) }}" class="input">
                            @endif
                            @if ($f['hint'])<p class="mt-1.5 text-xs text-ink/45">{{ $f['hint'] }}</p>@endif
                            @error($f['key'])<p class="error">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </div>
            </section>
        @endforeach

        <div class="sticky bottom-4 flex justify-end">
            <button class="btn-primary px-8 py-3 shadow-lift">Save settings</button>
        </div>
    </form>
</x-layouts.admin>
