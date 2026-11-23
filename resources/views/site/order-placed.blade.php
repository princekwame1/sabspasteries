@php
    $first = strtok($order->name, ' ');
    $whatsapp = \App\Models\Setting::whatsappUrl("Hello! I have just placed an order on your website.\n\n".implode("\n", $order->summaryLines()));
@endphp
<x-layouts.site title="Thank you">
    {{-- Same full-width panel as the other page headers --}}
    <section class="px-3 pt-24 sm:px-5">
        <div class="rounded-[2rem] bg-sand py-14 sm:py-16">
            <div class="page max-w-3xl text-center">
                <span class="mx-auto grid size-20 place-items-center rounded-full bg-white text-rose-500 shadow-soft animate-fade-up"><x-icon name="check" class="size-9" /></span>
                <h1 class="mt-6 font-display text-4xl text-cocoa-950 sm:text-5xl">{{ site('order_success_title', ['name' => $first]) }}</h1>
                <p class="mx-auto mt-5 max-w-xl text-lg text-ink/70">{{ site('order_success', ['name' => $first, 'reference' => $order->reference()]) }}</p>
            </div>
        </div>
    </section>

    <section class="page max-w-3xl pt-12">
        <div class="panel p-8 sm:p-10">
            <div class="flex flex-wrap items-baseline justify-between gap-3">
                <p class="font-display text-2xl text-cocoa-950">Your order</p>
                <p class="chip-rose text-sm">{{ $order->reference() }}</p>
            </div>
            <dl class="mt-6 grid gap-5 text-sm sm:grid-cols-2">
                <div><dt class="text-ink/50">Item</dt><dd class="mt-0.5 font-semibold text-cocoa-950">{{ $order->itemLine() }}</dd></div>
                <div><dt class="text-ink/50">Needed</dt><dd class="mt-0.5 font-semibold text-cocoa-950">{{ $order->needed_on->format('l j F Y') }}@if ($order->timeLabel())<span class="block font-normal text-ink/60">{{ $order->timeLabel() }}</span>@endif</dd></div>
                @if ($order->size)<div><dt class="text-ink/50">Size</dt><dd class="mt-0.5 font-semibold text-cocoa-950">{{ $order->size }}</dd></div>@endif
                @if ($order->flavour)<div><dt class="text-ink/50">Flavour</dt><dd class="mt-0.5 font-semibold text-cocoa-950">{{ $order->flavour }}</dd></div>@endif
                <div><dt class="text-ink/50">{{ $order->fulfilmentLabel() }}</dt><dd class="mt-0.5 font-semibold text-cocoa-950">{{ $order->fulfilment === 'delivery' ? $order->delivery_address : 'We will share the collection details' }}</dd></div>
                @if ($order->cake_message)<div><dt class="text-ink/50">Message on cake</dt><dd class="mt-0.5 font-semibold text-cocoa-950">“{{ $order->cake_message }}”</dd></div>@endif
            </dl>
            <div class="mt-8 rounded-[2rem] bg-cream p-5 text-sm text-ink/70">
                Want a faster reply? Send your order to us on WhatsApp too. It is already filled in for you.
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                @if ($whatsapp)<a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn bg-[#25d366] px-7 py-3.5 text-white hover:bg-[#1fb858]"><x-icon name="whatsapp" class="size-4" /> Send on WhatsApp</a>@endif
                <a href="{{ route('menu') }}" class="btn-outline px-7 py-3.5">Back to the menu</a>
            </div>
        </div>
    </section>
</x-layouts.site>
