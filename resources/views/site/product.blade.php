@php
    $photos = collect([$product->imageUrl()])->merge($product->images->map->url())->values();
    $items = $photos->map(fn ($src) => ['src' => $src, 'video' => false, 'caption' => $product->name])->all();
    $whatsapp = \App\Models\Setting::whatsappUrl('Hello! I would like to ask about the '.$product->name.'.');
@endphp
<x-layouts.site :title="$product->name" :description="$product->summary ?: Str::limit(strip_tags($product->html()), 155)" :image="$product->imageUrl()">
    <div class="page pt-28 sm:pt-32">
        <nav class="text-sm text-ink/55" aria-label="Breadcrumb">
            <a href="{{ route('menu') }}" class="hover:text-rose-600">Menu</a>
            @if ($product->category)<span class="mx-2">/</span><a href="{{ route('menu', ['category' => $product->category->slug]) }}" class="hover:text-rose-600">{{ $product->category->name }}</a>@endif
        </nav>

        <div class="mt-6 grid gap-12 lg:grid-cols-[1.1fr_1fr] lg:gap-16" x-data="gallery(@js($items))">
            {{-- Photos --}}
            <div class="lg:sticky lg:top-28 lg:self-start">
                <button type="button" @click="show(index)" class="block w-full overflow-hidden rounded-3xl bg-rose-50 shadow-soft" aria-label="View larger">
                    <img :src="current.src" src="{{ $photos[0] }}" alt="{{ $product->name }}" class="aspect-[4/5] w-full object-cover">
                </button>
                @if ($photos->count() > 1)
                    <div class="mt-4 flex gap-3 overflow-x-auto scrollbar-none">
                        @foreach ($photos as $i => $src)
                            <button type="button" @click="index = {{ $i }}" class="shrink-0 overflow-hidden rounded-2xl ring-2 transition" :class="index === {{ $i }} ? 'ring-rose-500' : 'ring-transparent opacity-70 hover:opacity-100'" aria-label="Photo {{ $i + 1 }}">
                                <img src="{{ $src }}" alt="" loading="lazy" class="size-20 object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
                @include('site.partials.lightbox')
            </div>

            {{-- Details --}}
            <div>
                <h1 class="font-display text-4xl leading-[1.05] text-cocoa-950 sm:text-5xl">{{ $product->name }}</h1>
                <p class="mt-4 text-xl font-semibold text-rose-600">{{ $product->priceLabel() }}</p>
                @if ($product->summary)<p class="mt-5 text-lg leading-relaxed text-ink/70">{{ $product->summary }}</p>@endif

                @if ($product->sizeList())
                    <div class="mt-8">
                        <p class="field-label">Sizes</p>
                        <ul class="flex flex-wrap gap-2">
                            @foreach ($product->sizeList() as $size)<li class="rounded-full bg-white px-4 py-2 text-sm text-cocoa-800 shadow-soft">{{ $size }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('order', ['product' => $product->slug]) }}" class="btn-rose px-8 py-4">{{ site('menu_order_button') }} <x-icon name="arrow-right" class="size-4" /></a>
                    @if ($whatsapp && site('product_ask_button'))<a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="btn-outline px-6 py-4"><x-icon name="whatsapp" class="size-4" /> {{ site('product_ask_button') }}</a>@endif
                </div>
                <p class="mt-4 flex items-center gap-2 text-sm text-ink/55"><x-icon name="clock" class="size-4 text-rose-500" /> {{ site('product_notice', ['days' => $product->noticeDays().' '.Str::plural('day', $product->noticeDays())]) }}</p>

                @if ($product->description)
                    <div class="prose-sweet mt-10">{!! $product->html() !!}</div>
                @endif
            </div>
        </div>
    </div>

    @if ($related->isNotEmpty())
        <section class="page pt-28">
            <x-section-heading :eyebrow="site('product_related_eyebrow')" :title="site('product_related_title')" />
            <div class="mt-10 grid grid-cols-2 gap-x-5 gap-y-10 lg:grid-cols-4">
                @foreach ($related as $item)<x-product-card :product="$item" />@endforeach
            </div>
        </section>
    @endif

    @if (site_shows('faq_show') && $faqs->isNotEmpty())
        @include('site.partials.faqs')
    @endif
</x-layouts.site>
