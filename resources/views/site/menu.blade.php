<x-layouts.site :title="$current?->name ?? 'Menu'" :description="$current?->summary ?: site('menu_text')">
    <x-page-hero :eyebrow="site('menu_eyebrow')" :title="$current ? $current->name : site('menu_title')">
        <p class="mt-6 max-w-2xl text-lg text-ink/70">{{ $current?->summary ?: site('menu_text') }}</p>
    </x-page-hero>

    {{-- Category filter --}}
    @if ($categories->count() > 1)
        <nav class="sticky top-20 z-30 bg-paper py-4" aria-label="Categories">
            <div class="page flex gap-2 overflow-x-auto scrollbar-none">
                <a href="{{ route('menu') }}" class="shrink-0 rounded-full px-5 py-2.5 text-sm font-medium transition {{ $current ? 'bg-sand text-cocoa-800 hover:text-rose-600' : 'bg-rose-600 text-white' }}">{{ site('menu_all') }}</a>
                @foreach ($categories as $category)
                    <a href="{{ route('menu', ['category' => $category->slug]) }}" @if ($current?->is($category)) aria-current="page" @endif
                        class="shrink-0 rounded-full px-5 py-2.5 text-sm font-medium transition {{ $current?->is($category) ? 'bg-rose-600 text-white' : 'bg-sand text-cocoa-800 hover:text-rose-600' }}">{{ $category->name }}</a>
                @endforeach
            </div>
        </nav>
    @endif

    <div class="page pt-12">
        @forelse ($shown as $category)
            <section class="{{ $loop->first ? '' : 'pt-20' }}" id="{{ $category->slug }}">
                @unless ($current)
                    <div class="mb-8 flex flex-wrap items-end justify-between gap-4" data-reveal>
                        <div>
                            <h2 class="font-display text-3xl text-cocoa-950">{{ $category->name }}</h2>
                            @if ($category->summary)<p class="mt-2 max-w-2xl text-ink/60">{{ $category->summary }}</p>@endif
                        </div>
                    </div>
                @endunless
                <div class="grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($category->products as $i => $product)
                        <x-product-card :product="$product" data-reveal style="--delay: {{ ($i % 4) * 60 }}ms" />
                    @endforeach
                </div>
            </section>
        @empty
            <p class="py-20 text-center text-ink/60">{{ site('menu_empty') }}</p>
        @endforelse

        @if ($uncategorised->isNotEmpty())
            <section class="pt-20">
                <h2 class="mb-8 font-display text-3xl text-cocoa-950">{{ site('menu_more') }}</h2>
                <div class="grid grid-cols-2 gap-x-5 gap-y-10 md:grid-cols-3 lg:grid-cols-4">
                    @foreach ($uncategorised as $product)<x-product-card :product="$product" />@endforeach
                </div>
            </section>
        @endif

        {{-- Custom order --}}
        <section class="mt-24 grid bg-sand items-center gap-8 rounded-[2rem] p-8 sm:p-12 lg:grid-cols-[1.5fr_1fr]" data-reveal>
            <div>
                <h2 class="font-display text-3xl text-cocoa-950 sm:text-4xl">{{ rich(site('menu_custom_title')) }}</h2>
                <p class="mt-4 max-w-xl text-ink/65">{{ site('menu_custom_text') }}</p>
            </div>
            <div class="flex flex-wrap gap-3 lg:justify-end">
                <a href="{{ route('order') }}" class="btn-rose px-8 py-4">{{ site('menu_custom_button') }}</a>
            </div>
        </section>
    </div>

    @if (site_shows('faq_show') && $faqs->isNotEmpty())
        @include('site.partials.faqs')
    @endif
</x-layouts.site>
