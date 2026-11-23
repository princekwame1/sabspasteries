@php
    $s = \App\Models\Setting::values();
    $open = $s['orders_open'] !== '0';
    $deliveryOn = $s['delivery_enabled'] !== '0';
    $defaultNotice = (int) $s['lead_days'];
    $productData = $products->mapWithKeys(fn ($p) => [$p->id => ['name' => $p->name, 'sizes' => $p->sizeList(), 'notice' => $p->noticeDays()]]);
    $grouped = $products->groupBy(fn ($p) => $p->category?->name ?? site('menu_more'));
    $initial = ['product' => (string) old('product_id', $selected ?? ''), 'fulfilment' => old('fulfilment', 'collection')];
    $whatsapp = \App\Models\Setting::whatsappUrl('Hello '.$s['business_name'].', I would like to place an order.');
@endphp
<x-layouts.site title="Order online" :description="site('order_text')">
    <x-page-hero :eyebrow="site('order_eyebrow')" :title="site('order_title')">
        <p class="mt-6 max-w-2xl text-lg text-ink/70">{{ site('order_text') }}</p>
    </x-page-hero>

    <div class="page grid items-start gap-10 pt-12 lg:grid-cols-[1fr_22rem]">
        @if (! $open)
            <div class="panel p-10 text-center">
                <p class="font-display text-3xl text-cocoa-950">Orders are paused</p>
                <p class="mx-auto mt-3 max-w-lg text-ink/65">{{ $s['orders_closed_note'] }}</p>
                @if ($whatsapp)<a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-rose mt-8"><x-icon name="whatsapp" class="size-4" /> Message us</a>@endif
            </div>
        @else
            <form method="post" action="{{ route('order.store') }}" enctype="multipart/form-data" class="space-y-6"
                x-data="orderForm(@js($productData), {{ $defaultNotice }}, @js($initial))">
                @csrf
                <input type="text" name="website" class="hidden" tabindex="-1" autocomplete="off" aria-hidden="true">

                @if ($errors->any())
                    <div class="rounded-2xl bg-red-50 px-5 py-4 text-sm text-red-700" role="alert">Please check the highlighted fields below.</div>
                @endif

                {{-- 1. What --}}
                <section class="panel p-6 sm:p-10">
                    <h2 class="flex items-center gap-3 font-display text-2xl text-cocoa-950"><span class="grid size-9 place-items-center rounded-full bg-rose-100 font-sans text-sm font-semibold text-rose-700">1</span> What would you like?</h2>
                    <div class="mt-7 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="field-label" for="product_id">Choose from the menu</label>
                            <select id="product_id" name="product_id" x-model="product" class="field bg-cocoa-50/60">
                                <option value="">Something custom (describe it below)</option>
                                @foreach ($grouped as $group => $items)
                                    <optgroup label="{{ $group }}">
                                        @foreach ($items as $p)
                                            <option value="{{ $p->id }}" @selected($initial['product'] === (string) $p->id)>{{ $p->name }}{{ $p->price_from !== null ? ' · '.$p->priceLabel() : '' }}</option>
                                        @endforeach
                                    </optgroup>
                                @endforeach
                            </select>
                            @error('product_id')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2" x-show="!product" @if ($initial['product'] !== '') x-cloak @endif>
                            <label class="field-label" for="item">What should we make?</label>
                            <input id="item" name="item" value="{{ old('item') }}" maxlength="160" class="field bg-cocoa-50/60" placeholder="e.g. Two-tier birthday cake with 12 matching cupcakes">
                            @error('item')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label" for="size">Size / servings</label>
                            <input id="size" name="size" list="size-options" value="{{ old('size') }}" maxlength="120" class="field bg-cocoa-50/60" :placeholder="sizes.length ? 'Choose or type a size' : 'e.g. 8 inch, about 20 slices'">
                            <datalist id="size-options"><template x-for="option in sizes" :key="option"><option :value="option"></option></template></datalist>
                            @error('size')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label" for="flavour">Flavour</label>
                            <input id="flavour" name="flavour" list="flavour-options" value="{{ old('flavour') }}" maxlength="120" class="field bg-cocoa-50/60" placeholder="Choose or type a flavour">
                            <datalist id="flavour-options">@foreach (\App\Models\Setting::flavours() as $flavour)<option value="{{ $flavour }}"></option>@endforeach</datalist>
                            @error('flavour')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label" for="quantity">Quantity</label>
                            <input id="quantity" name="quantity" type="number" min="1" max="500" value="{{ old('quantity', 1) }}" required class="field bg-cocoa-50/60">
                            @error('quantity')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label" for="occasion">Occasion</label>
                            <select id="occasion" name="occasion" class="field bg-cocoa-50/60">
                                <option value="">Choose (optional)</option>
                                @foreach (\App\Models\Order::OCCASIONS as $occasion)<option @selected(old('occasion') === $occasion)>{{ $occasion }}</option>@endforeach
                            </select>
                            @error('occasion')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="field-label" for="cake_message">Message on the cake <span class="font-normal normal-case tracking-normal text-ink/40">(optional)</span></label>
                            <input id="cake_message" name="cake_message" value="{{ old('cake_message') }}" maxlength="160" class="field bg-cocoa-50/60" placeholder="e.g. Happy 30th Birthday Ama">
                            @error('cake_message')<p class="error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- 2. Design --}}
                <section class="panel p-6 sm:p-10">
                    <h2 class="flex items-center gap-3 font-display text-2xl text-cocoa-950"><span class="grid size-9 place-items-center rounded-full bg-rose-100 font-sans text-sm font-semibold text-rose-700">2</span> Your design</h2>
                    <div class="mt-7 grid gap-5">
                        <div>
                            <label class="field-label" for="design_notes">{{ site('order_design_label') }}</label>
                            <textarea id="design_notes" name="design_notes" rows="5" maxlength="3000" class="field bg-cocoa-50/60" placeholder="{{ site('order_design_hint') }}">{{ old('design_notes') }}</textarea>
                            @error('design_notes')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <span class="field-label">Inspiration photos <span class="font-normal normal-case tracking-normal text-ink/40">(up to 3, optional)</span></span>
                            <label class="flex cursor-pointer flex-col items-center justify-center gap-2 rounded-[2rem] bg-rose-50 px-6 py-8 text-center text-sm text-cocoa-700 ring-2 ring-dashed ring-rose-200 transition hover:bg-rose-100">
                                <x-icon name="upload" class="size-6 text-rose-500" />
                                <span><span class="font-semibold text-rose-700">Choose photos</span> or screenshots from Instagram, Pinterest...</span>
                                <input type="file" name="reference_images[]" accept="image/*" multiple class="sr-only" @change="pick($event)">
                            </label>
                            <div class="mt-3 flex gap-3" x-show="previews.length" x-cloak>
                                <template x-for="src in previews" :key="src"><img :src="src" alt="" class="size-20 rounded-2xl object-cover shadow-soft"></template>
                            </div>
                            @error('reference_images')<p class="error">{{ $message }}</p>@enderror
                            @error('reference_images.*')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label" for="allergies">Allergies or dietary needs <span class="font-normal normal-case tracking-normal text-ink/40">(optional)</span></label>
                            <input id="allergies" name="allergies" value="{{ old('allergies') }}" maxlength="300" class="field bg-cocoa-50/60" placeholder="e.g. nut allergy, eggless">
                            @error('allergies')<p class="error">{{ $message }}</p>@enderror
                        </div>
                    </div>
                </section>

                {{-- 3. When --}}
                <section class="panel p-6 sm:p-10">
                    <h2 class="flex items-center gap-3 font-display text-2xl text-cocoa-950"><span class="grid size-9 place-items-center rounded-full bg-rose-100 font-sans text-sm font-semibold text-rose-700">3</span> When and how?</h2>
                    <div class="mt-7 grid gap-5 sm:grid-cols-2">
                        <div>
                            <label class="field-label" for="needed_on">Date needed</label>
                            <input id="needed_on" name="needed_on" type="date" value="{{ old('needed_on') }}" :min="minDate" required class="field bg-cocoa-50/60">
                            <p class="mt-1.5 text-xs text-ink/50">At least <span x-text="notice">{{ $defaultNotice }}</span> days’ notice, please.</p>
                            @error('needed_on')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label" for="time_slot">Preferred time</label>
                            <select id="time_slot" name="time_slot" class="field bg-cocoa-50/60">
                                <option value="">Any time</option>
                                @foreach (\App\Models\Order::TIME_SLOTS as $key => $label)<option value="{{ $key }}" @selected(old('time_slot') === $key)>{{ $label }}</option>@endforeach
                            </select>
                            @error('time_slot')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <fieldset class="sm:col-span-2">
                            <legend class="field-label">Collection or delivery</legend>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach (\App\Models\Order::FULFILMENT as $key => $label)
                                    @continue($key === 'delivery' && ! $deliveryOn)
                                    <label class="flex cursor-pointer items-center gap-3 rounded-2xl px-5 py-4 ring-2 transition" :class="fulfilment === '{{ $key }}' ? 'bg-rose-50 ring-rose-400' : 'bg-cocoa-50/60 ring-transparent hover:bg-rose-50/60'">
                                        <input type="radio" name="fulfilment" value="{{ $key }}" x-model="fulfilment" class="accent-rose-600" @checked($initial['fulfilment'] === $key)>
                                        <x-icon :name="$key === 'delivery' ? 'truck' : 'store'" class="size-5 text-rose-500" />
                                        <span class="font-medium text-cocoa-900">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                            @error('fulfilment')<p class="error">{{ $message }}</p>@enderror
                        </fieldset>
                        @if ($deliveryOn)
                            <div class="sm:col-span-2" x-show="fulfilment === 'delivery'" @if ($initial['fulfilment'] !== 'delivery') x-cloak @endif>
                                <label class="field-label" for="delivery_address">Delivery address</label>
                                <textarea id="delivery_address" name="delivery_address" rows="2" maxlength="300" class="field bg-cocoa-50/60" :required="fulfilment === 'delivery'">{{ old('delivery_address') }}</textarea>
                                @if ($s['delivery_note'])<p class="mt-1.5 text-xs text-ink/50">{{ $s['delivery_note'] }}</p>@endif
                                @error('delivery_address')<p class="error">{{ $message }}</p>@enderror
                            </div>
                        @endif
                    </div>
                </section>

                {{-- 4. Who --}}
                <section class="panel p-6 sm:p-10">
                    <h2 class="flex items-center gap-3 font-display text-2xl text-cocoa-950"><span class="grid size-9 place-items-center rounded-full bg-rose-100 font-sans text-sm font-semibold text-rose-700">4</span> Your details</h2>
                    <div class="mt-7 grid gap-5 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label class="field-label" for="name">Your name</label>
                            <input id="name" name="name" value="{{ old('name') }}" required maxlength="120" class="field bg-cocoa-50/60" autocomplete="name">
                            @error('name')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label" for="phone">Phone / WhatsApp</label>
                            <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" required maxlength="40" class="field bg-cocoa-50/60" autocomplete="tel">
                            @error('phone')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="field-label" for="email">Email <span class="font-normal normal-case tracking-normal text-ink/40">(optional)</span></label>
                            <input id="email" name="email" type="email" value="{{ old('email') }}" maxlength="160" class="field bg-cocoa-50/60" autocomplete="email">
                            @error('email')<p class="error">{{ $message }}</p>@enderror
                        </div>
                        <div class="sm:col-span-2">
                            <label class="field-label" for="heard_from">How did you find us?</label>
                            <select id="heard_from" name="heard_from" class="field bg-cocoa-50/60 sm:max-w-xs">
                                <option value="">Choose (optional)</option>
                                @foreach (\App\Models\Order::HEARD_FROM as $source)<option @selected(old('heard_from') === $source)>{{ $source }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </section>

                <div class="flex flex-col items-start gap-4 sm:flex-row sm:items-center">
                    <button class="btn-rose px-10 py-4 text-sm">{{ site('order_button') }} <x-icon name="arrow-right" class="size-4" /></button>
                    @if (site('order_note'))<p class="max-w-md text-sm text-ink/55">{{ site('order_note') }}</p>@endif
                </div>
            </form>
        @endif

        {{-- Sidebar --}}
        <aside class="space-y-5 lg:sticky lg:top-28">
            <div class="rounded-3xl bg-rose-50 p-8">
                <p class="font-display text-2xl text-cocoa-950">{{ site('order_steps_title') }}</p>
                <ol class="mt-5 space-y-4 text-sm text-ink/70">
                    @foreach ([1, 2, 3] as $n)
                        <li class="flex gap-3"><span class="grid size-7 shrink-0 place-items-center rounded-full bg-white text-xs font-semibold text-rose-600 shadow-soft">{{ $n }}</span><span><span class="block font-semibold text-cocoa-900">{{ site("home_step_{$n}_title") }}</span>{{ site("home_step_{$n}_text") }}</span></li>
                    @endforeach
                </ol>
            </div>
            <div class="rounded-3xl bg-white p-8 shadow-soft">
                <p class="font-display text-2xl text-cocoa-950">{{ site('order_chat_title') }}</p>
                @if (site('order_chat_text'))<p class="mt-2 text-sm text-ink/60">{{ site('order_chat_text') }}</p>@endif
                <div class="mt-5 grid gap-2">
                    @if ($whatsapp)<a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-ghost"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>@endif
                    @if ($s['instagram'])<a href="{{ $s['instagram'] }}" target="_blank" rel="noopener" class="btn-ghost"><x-icon name="instagram" class="size-4" /> Instagram</a>@endif
                </div>
                @if ($s['phone_2'])<p class="mt-5 text-xs text-ink/50">{{ $s['phone_2_label'] ?: 'Also' }}: <a href="tel:{{ \App\Models\Setting::tel($s['phone_2']) }}" class="font-medium text-cocoa-800">{{ $s['phone_2'] }}</a></p>@endif
            </div>
        </aside>
    </div>
</x-layouts.site>
