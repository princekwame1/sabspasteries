@php
    $editing = $order->exists;
    $v = fn ($key) => old($key, $order->{$key});
@endphp
<x-layouts.admin :title="$editing ? 'Edit '.$order->reference() : 'Add an order'">
    @unless ($editing)
        <p class="mb-6 max-w-3xl text-sm text-ink/55">For orders that come in by Instagram or TikTok DM, WhatsApp, phone or in person. They appear in the calendar and dashboard just like website orders.</p>
    @endunless
    <form method="post" action="{{ $editing ? route('admin.orders.update', $order) : route('admin.orders.store') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        @csrf
        @if ($editing) @method('put') @endif

        <div class="space-y-6">
            <section class="card grid gap-4 p-6 sm:grid-cols-2">
                <h2 class="font-semibold text-cocoa-950 sm:col-span-2">Customer</h2>
                <div class="sm:col-span-2">
                    <label class="label" for="name">Name</label>
                    <input id="name" name="name" value="{{ $v('name') }}" required class="input">
                    @error('name')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="phone">Phone / WhatsApp</label>
                    <input id="phone" name="phone" value="{{ $v('phone') }}" required class="input">
                    @error('phone')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="email">Email</label>
                    <input id="email" name="email" type="email" value="{{ $v('email') }}" class="input">
                    @error('email')<p class="error">{{ $message }}</p>@enderror
                </div>
            </section>

            <section class="card grid gap-4 p-6 sm:grid-cols-2">
                <h2 class="font-semibold text-cocoa-950 sm:col-span-2">The order</h2>
                <div>
                    <label class="label" for="product_id">Menu item</label>
                    <select id="product_id" name="product_id" class="input">
                        <option value="">Custom / not on the menu</option>
                        @foreach ($products as $p)<option value="{{ $p->id }}" @selected((string) $v('product_id') === (string) $p->id)>{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="item">Description</label>
                    <input id="item" name="item" value="{{ $v('item') }}" class="input" placeholder="Leave blank to use the menu item name">
                    @error('item')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="size">Size</label>
                    <input id="size" name="size" value="{{ $v('size') }}" class="input">
                </div>
                <div>
                    <label class="label" for="flavour">Flavour</label>
                    <input id="flavour" name="flavour" list="flavours" value="{{ $v('flavour') }}" class="input">
                    <datalist id="flavours">@foreach (\App\Models\Setting::flavours() as $f)<option value="{{ $f }}"></option>@endforeach</datalist>
                </div>
                <div>
                    <label class="label" for="quantity">Quantity</label>
                    <input id="quantity" name="quantity" type="number" min="1" value="{{ $v('quantity') }}" required class="input">
                    @error('quantity')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="occasion">Occasion</label>
                    <input id="occasion" name="occasion" list="occasions" value="{{ $v('occasion') }}" class="input">
                    <datalist id="occasions">@foreach (\App\Models\Order::OCCASIONS as $o)<option value="{{ $o }}"></option>@endforeach</datalist>
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="cake_message">Message on the cake</label>
                    <input id="cake_message" name="cake_message" value="{{ $v('cake_message') }}" maxlength="160" class="input">
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="design_notes">Design notes</label>
                    <textarea id="design_notes" name="design_notes" rows="4" class="input">{{ $v('design_notes') }}</textarea>
                </div>
                <div class="sm:col-span-2">
                    <label class="label" for="allergies">Allergies / dietary</label>
                    <input id="allergies" name="allergies" value="{{ $v('allergies') }}" class="input">
                </div>
                <div class="sm:col-span-2">
                    <span class="label">Inspiration photos</span>
                    @if ($order->reference_images)
                        <div class="mb-3 flex flex-wrap gap-3">
                            @foreach ($order->reference_images as $path)
                                <label class="relative block">
                                    <img src="{{ asset('storage/'.$path) }}" alt="" class="size-24 rounded-xl object-cover">
                                    <span class="mt-1 flex items-center gap-1.5 text-xs text-ink/60"><input type="checkbox" name="remove_images[]" value="{{ $path }}" class="accent-red-600"> Remove</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <input name="reference_images[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="input file:mr-3 file:rounded-full file:border-0 file:bg-cocoa-950 file:px-3 file:py-1 file:text-xs file:text-white">
                    <p class="mt-1.5 text-xs text-ink/45">Screenshots from the customer’s DMs work well.</p>
                    @error('reference_images.*')<p class="error">{{ $message }}</p>@enderror
                </div>
            </section>
        </div>

        <div class="space-y-6 self-start">
            <section class="card space-y-4 p-6">
                <h2 class="font-semibold text-cocoa-950">When and how</h2>
                <div>
                    <label class="label" for="needed_on">Date needed</label>
                    <input id="needed_on" name="needed_on" type="date" value="{{ old('needed_on', $order->needed_on?->toDateString()) }}" required class="input">
                    @error('needed_on')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="time_slot">Time</label>
                    <select id="time_slot" name="time_slot" class="input">
                        <option value="">Any time</option>
                        @foreach (\App\Models\Order::TIME_SLOTS as $key => $label)<option value="{{ $key }}" @selected($v('time_slot') === $key)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="fulfilment">Collection or delivery</label>
                    <select id="fulfilment" name="fulfilment" class="input">
                        @foreach (\App\Models\Order::FULFILMENT as $key => $label)<option value="{{ $key }}" @selected($v('fulfilment') === $key)>{{ $label }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="label" for="delivery_address">Delivery address</label>
                    <textarea id="delivery_address" name="delivery_address" rows="2" class="input">{{ $v('delivery_address') }}</textarea>
                </div>
            </section>

            <section class="card space-y-4 p-6">
                <h2 class="font-semibold text-cocoa-950">Status and payment</h2>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="source">Came in via</label>
                        <select id="source" name="source" class="input">
                            @foreach (\App\Models\Order::SOURCES as $key => $label)<option value="{{ $key }}" @selected($v('source') === $key)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="status">Stage</label>
                        <select id="status" name="status" class="input">
                            @foreach (\App\Models\Order::STATUSES as $key => $label)<option value="{{ $key }}" @selected($v('status') === $key)>{{ $label }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label class="label" for="total">Price ({{ \App\Models\Setting::get('currency') }})</label>
                        <input id="total" name="total" type="number" min="0" step="0.01" value="{{ $v('total') }}" class="input">
                        @error('total')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="paid">Paid</label>
                        <input id="paid" name="paid" type="number" min="0" step="0.01" value="{{ old('paid', (float) $order->paid ?: '') }}" class="input">
                    </div>
                </div>
                <div>
                    <label class="label" for="notes">Private notes</label>
                    <textarea id="notes" name="notes" rows="3" class="input">{{ $v('notes') }}</textarea>
                </div>
                <div class="flex gap-2 pt-2">
                    <button class="btn-primary flex-1">{{ $editing ? 'Save changes' : 'Add order' }}</button>
                    <a href="{{ $editing ? route('admin.orders.show', $order) : route('admin.orders.index') }}" class="btn-ghost">Cancel</a>
                </div>
            </section>
        </div>
    </form>
</x-layouts.admin>
