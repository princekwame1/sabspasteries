@php
    $first = Str::before($order->name, ' ');
    $business = \App\Models\Setting::get('business_name');
    $statusKeys = array_keys(\App\Models\Order::STATUSES);
    $nextStatus = [
        'new' => ['confirmed', 'Confirm order'],
        'confirmed' => ['baking', 'Start baking'],
        'baking' => ['ready', 'Mark ready'],
        'ready' => ['completed', $order->fulfilment === 'delivery' ? 'Mark delivered' : 'Mark collected'],
    ][$order->status] ?? null;
    $waText = match ($order->status) {
        'ready' => "Hello {$first}, your order {$order->reference()} from {$business} is ready".($order->fulfilment === 'delivery' ? ' and on its way.' : ' to collect.'),
        default => "Hello {$first}, thank you for your order {$order->reference()} with {$business} (".$order->itemLine().' for '.$order->needed_on->format('l j F').').',
    };
@endphp
<x-layouts.admin :title="$order->reference().' · '.$order->name">
    <x-slot:actions>
        <a href="{{ route('admin.orders.edit', $order) }}" class="btn-ghost bg-white"><x-icon name="pen" class="size-4" /> Edit</a>
    </x-slot:actions>

    <div class="grid gap-6 xl:grid-cols-[1fr_24rem]">
        <div class="space-y-6">
            <section class="card p-6 sm:p-8">
                <div class="flex flex-wrap items-center gap-3">
                    <x-admin.status :status="$order->status" :label="$order->statusLabel()" />
                    @if ($order->paymentState())<x-admin.status :status="$order->paymentState()" :label="$order->paymentLabel()" />@endif
                    <span class="text-sm text-ink/55">{{ $order->sourceLabel() }} · received {{ $order->created_at->format('D j M Y, H:i') }}</span>
                </div>

                <div class="mt-6 rounded-2xl bg-cocoa-950 p-6 text-white">
                    <p class="text-sm text-white/55">{{ $order->fulfilmentLabel() }}{{ $order->timeLabel() ? ' · '.$order->timeLabel() : '' }}</p>
                    <p class="mt-1 font-display text-3xl text-rose-300">{{ $order->needed_on->format('l j F Y') }}</p>
                    <p class="mt-1 text-sm text-white/55">{{ $order->needed_on->isToday() ? 'Today' : $order->needed_on->diffForHumans(['parts' => 1]) }}</p>
                    @if ($order->fulfilment === 'delivery')<p class="mt-3 text-sm text-white/80">{{ $order->delivery_address ?: 'No address given yet' }}</p>@endif
                </div>
                @if ($sameDay)
                    <p class="mt-3 text-sm text-ink/55">{{ $sameDay }} other {{ Str::plural('order', $sameDay) }} due the same day. <a href="{{ route('admin.calendar', ['month' => $order->needed_on->format('Y-m')]) }}" class="font-medium text-rose-600 underline">See calendar</a></p>
                @endif

                <h2 class="mt-8 font-display text-3xl text-cocoa-950">{{ $order->itemLine() }}</h2>
                @if ($order->product)<a href="{{ route('admin.products.edit', $order->product) }}" class="text-xs text-ink/50 hover:text-rose-600">From the menu: {{ $order->product->name }}</a>@endif
                <dl class="mt-6 grid gap-5 text-sm sm:grid-cols-3">
                    <div><dt class="text-ink/45">Size</dt><dd class="mt-0.5 font-medium">{{ $order->size ?: '-' }}</dd></div>
                    <div><dt class="text-ink/45">Flavour</dt><dd class="mt-0.5 font-medium">{{ $order->flavour ?: '-' }}</dd></div>
                    <div><dt class="text-ink/45">Occasion</dt><dd class="mt-0.5 font-medium">{{ $order->occasion ?: '-' }}</dd></div>
                    <div class="sm:col-span-2"><dt class="text-ink/45">Message on the cake</dt><dd class="mt-0.5 font-medium">{{ $order->cake_message ? '“'.$order->cake_message.'”' : '-' }}</dd></div>
                    <div><dt class="text-ink/45">Found us via</dt><dd class="mt-0.5 font-medium">{{ $order->heard_from ?: '-' }}</dd></div>
                </dl>
                @if ($order->allergies)
                    <p class="mt-6 rounded-2xl bg-red-50 px-5 py-3.5 text-sm text-red-800"><span class="font-semibold">Allergies / dietary:</span> {{ $order->allergies }}</p>
                @endif
                <div class="mt-6">
                    <p class="text-xs font-semibold uppercase tracking-wider text-ink/45">Design notes</p>
                    <p class="mt-2 whitespace-pre-line text-ink/80">{{ $order->design_notes ?: 'None given.' }}</p>
                </div>
                @if ($order->referenceUrls())
                    <div class="mt-6">
                        <p class="text-xs font-semibold uppercase tracking-wider text-ink/45">Inspiration photos</p>
                        <div class="mt-3 flex flex-wrap gap-3">
                            @foreach ($order->referenceUrls() as $url)
                                <a href="{{ $url }}" target="_blank" class="block overflow-hidden rounded-2xl shadow-soft"><img src="{{ $url }}" alt="Inspiration photo" class="size-32 object-cover transition hover:scale-105"></a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </section>

            @if ($history->isNotEmpty())
                <section class="card p-6">
                    <h2 class="font-semibold text-cocoa-950">Other orders from {{ $first }}</h2>
                    <ul class="mt-3 space-y-1">
                        @foreach ($history as $h)
                            <li><a href="{{ route('admin.orders.show', $h) }}" class="flex items-center justify-between gap-4 rounded-xl px-3 py-2.5 text-sm hover:bg-cocoa-50">
                                <span class="truncate">{{ $h->reference() }} · {{ $h->itemLine() }}</span>
                                <span class="flex shrink-0 items-center gap-3"><span class="text-xs text-ink/45">{{ $h->needed_on->format('j M Y') }}</span><x-admin.status :status="$h->status" :label="$h->statusLabel()" /></span>
                            </a></li>
                        @endforeach
                    </ul>
                </section>
            @endif
        </div>

        <div class="space-y-6">
            {{-- Next step --}}
            @if ($nextStatus)
                <form method="post" action="{{ route('admin.orders.status', $order) }}">
                    @csrf
                    <input type="hidden" name="status" value="{{ $nextStatus[0] }}">
                    <button class="btn-rose w-full py-3.5"><x-icon name="check" class="size-4" /> {{ $nextStatus[1] }}</button>
                </form>
            @endif

            <section class="card p-6">
                <h2 class="font-semibold text-cocoa-950">Customer</h2>
                <dl class="mt-4 space-y-3 text-sm">
                    <div><dt class="text-ink/45">Name</dt><dd class="font-medium">{{ $order->name }}</dd></div>
                    <div><dt class="text-ink/45">Phone</dt><dd class="font-medium">{{ $order->phone }}</dd></div>
                    @if ($order->email)<div><dt class="text-ink/45">Email</dt><dd class="break-all font-medium">{{ $order->email }}</dd></div>@endif
                </dl>
                <div class="mt-5 grid grid-cols-2 gap-2">
                    <a href="tel:{{ \App\Models\Setting::tel($order->phone) }}" class="btn-ghost"><x-icon name="phone" class="size-4" /> Call</a>
                    <a href="{{ \App\Models\Setting::whatsappUrl($waText, $order->phone) }}" target="_blank" rel="noopener" class="btn-ghost"><x-icon name="whatsapp" class="size-4" /> WhatsApp</a>
                    @if ($order->email)
                        <a href="mailto:{{ $order->email }}?subject={{ rawurlencode('Your order '.$order->reference().' with '.$business) }}" class="btn-ghost col-span-2"><x-icon name="mail" class="size-4" /> Email</a>
                    @endif
                </div>
            </section>

            <form method="post" action="{{ route('admin.orders.update', $order) }}" class="card space-y-4 p-6">
                @csrf @method('put')
                <input type="hidden" name="quick" value="1">
                <h2 class="font-semibold text-cocoa-950">Stage and payment</h2>
                <div>
                    <label class="label" for="status">Stage</label>
                    <select id="status" name="status" class="input">
                        @foreach (\App\Models\Order::STATUSES as $key => $label)
                            <option value="{{ $key }}" @selected(old('status', $order->status) === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="label" for="total">Price ({{ \App\Models\Setting::get('currency') }})</label>
                        <input id="total" name="total" type="number" min="0" step="0.01" value="{{ old('total', $order->total) }}" class="input" placeholder="Agreed price">
                        @error('total')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="paid">Paid so far</label>
                        <input id="paid" name="paid" type="number" min="0" step="0.01" value="{{ old('paid', (float) $order->paid ?: '') }}" class="input" placeholder="0">
                        @error('paid')<p class="error">{{ $message }}</p>@enderror
                    </div>
                </div>
                @if ($order->balance() !== null)
                    <p class="rounded-xl bg-cocoa-50 px-4 py-2.5 text-sm">Balance: <span class="font-semibold {{ $order->balance() > 0 ? 'text-rose-700' : 'text-emerald-700' }}">{{ money($order->balance()) }}</span></p>
                @endif
                <div>
                    <label class="label" for="notes">Private notes</label>
                    <textarea id="notes" name="notes" rows="5" class="input" placeholder="Deposit received by bank transfer, prefers pastel colours...">{{ old('notes', $order->notes) }}</textarea>
                </div>
                <button class="btn-primary w-full">Save</button>
            </form>

            <form method="post" action="{{ route('admin.orders.destroy', $order) }}" data-confirm="Delete order {{ $order->reference() }}? This cannot be undone.">
                @csrf @method('delete')
                <button class="btn-danger w-full"><x-icon name="trash" class="size-4" /> Delete order</button>
            </form>
        </div>
    </div>
</x-layouts.admin>
