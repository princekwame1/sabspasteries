@php
    $s = \App\Models\Setting::values();
    $heroImage = \App\Models\Setting::imageUrl('hero_image_path');
    // Hero cakes that rotate every 5 seconds: the Main photo from Site content first, then these.
    $heroSlides = [$heroImage, asset('images/hero-red-rose-cake.webp'), asset('images/hero-mothers-day-cake.png')];
    // Category grid: the first tile widens so every row is full (4 columns on desktop, 2 on phones).
    $catCount = $categories->count();
    $firstSpan = trim([0 => '', 1 => 'lg:col-span-4', 2 => 'lg:col-span-3', 3 => 'lg:col-span-2'][$catCount % 4].' '.($catCount % 2 ? 'col-span-2' : ''));
@endphp
<x-layouts.site>
    {{-- Hero --}}
    {{-- Panel runs nearly edge to edge; content inside stays aligned with the header --}}
    <section class="px-3 pt-24 sm:px-5">
        <div class="overflow-hidden rounded-[2rem] bg-sand">
        <div class="page relative grid items-center gap-12 pb-10 pt-12 lg:grid-cols-[1.05fr_1fr] lg:gap-6 lg:py-20">
            <div class="animate-fade-up">
                @if (site('hero_eyebrow'))<p class="eyebrow">{{ site('hero_eyebrow') }}</p>@endif
                <h1 class="mt-3 text-[2.75rem] leading-[1.02] tracking-[-0.035em] text-cocoa-950 sm:text-6xl xl:text-[4.5rem]">{{ rich(site('hero_title')) }}</h1>
                <p class="mt-6 max-w-md text-lg leading-relaxed text-ink/65">{{ site('hero_text') }}</p>

                <div class="mt-9 flex flex-wrap items-center gap-3">
                    @if (site('hero_button'))
                        <a href="{{ route('order') }}" class="btn-rose group py-2 pl-7 pr-2">
                            {{ site('hero_button') }}
                            <span class="grid size-10 place-items-center rounded-full bg-white text-rose-600 transition group-hover:translate-x-0.5"><x-icon name="arrow-right" class="size-4" /></span>
                        </a>
                    @endif
                    @if (site('hero_button2'))<a href="{{ route('menu') }}" class="btn px-6 py-4 font-medium text-cocoa-950 underline decoration-cocoa-950/25 underline-offset-[6px] hover:text-rose-600 hover:decoration-rose-600">{{ site('hero_button2') }}</a>@endif
                </div>

                {{-- Social proof from real published reviews --}}
                @if ($testimonials->isNotEmpty())
                    <div class="mt-10 flex items-center gap-4 border-t border-cocoa-900/10 pt-8">
                        <div class="flex -space-x-2.5">
                            @foreach ($testimonials->take(3) as $t)
                                <span class="grid size-10 place-items-center rounded-full bg-white text-sm font-semibold text-rose-600 ring-[3px] ring-sand">{{ Str::substr($t->name, 0, 1) }}</span>
                            @endforeach
                        </div>
                        <div>
                            <div class="flex items-center gap-1.5 text-sm font-semibold text-cocoa-950">
                                <span class="flex gap-0.5 text-rose-500" aria-hidden="true">@for ($n = 1; $n <= 5; $n++)<x-icon name="star" class="size-3.5 {{ $n <= round($testimonials->avg('rating')) ? 'fill-current' : 'opacity-30' }}" />@endfor</span>
                                {{ number_format($testimonials->avg('rating'), 1) }}
                            </div>
                            @if (site('hero_reviews_label'))<p class="text-sm text-ink/55">{{ site('hero_reviews_label') }}</p>@endif
                        </div>
                    </div>
                @endif
            </div>

            <div class="relative animate-fade-up [animation-delay:120ms]">
                {{-- Soft disc behind the cake for depth --}}
                <div class="absolute left-1/2 top-1/2 aspect-square w-[88%] max-w-[30rem] -translate-x-1/2 -translate-y-1/2 rounded-full bg-white/70" aria-hidden="true"></div>
                {{-- Rotating cakes: cross-fade to the next one every 5 seconds --}}
                <div class="relative mx-auto max-w-lg" x-data="heroRotator({{ count($heroSlides) }}, 5000)">
                    @foreach ($heroSlides as $i => $src)
                        <img src="{{ $src }}" alt="" @if ($i === 0) fetchpriority="high" @else fetchpriority="low" decoding="async" @endif width="1219" height="1290"
                            class="{{ $i === 0 ? 'relative' : 'absolute inset-0 opacity-0 scale-95' }} mx-auto h-full max-h-[32rem] w-full object-contain drop-shadow-[0_30px_30px_rgb(46_31_24/0.18)] transition duration-1000 ease-out"
                            @if ($i === 0) :class="current === 0 ? '' : 'opacity-0 scale-95'" @else :class="current === {{ $i }} ? 'opacity-100! scale-100!' : ''" @endif>
                    @endforeach
                    @if (count($heroSlides) > 1)
                        <div class="absolute -bottom-2 right-2 flex gap-1.5 lg:right-6">
                            @foreach ($heroSlides as $i => $src)
                                <button type="button" @click="go({{ $i }})" class="h-2 rounded-full transition-all duration-300" :class="current === {{ $i }} ? 'w-6 bg-rose-600' : 'w-2 bg-cocoa-900/20 hover:bg-cocoa-900/40'" aria-label="Show cake {{ $i + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        </div>
    </section>

    {{-- About teaser --}}
    @if (site_shows('home_intro_show'))
        <section class="page grid items-center gap-12 pt-24 lg:grid-cols-2 lg:gap-20">
            <img src="{{ \App\Models\Setting::imageUrl('home_intro_image') }}" alt="" loading="lazy" class="aspect-[4/3] w-full rounded-3xl object-cover" data-reveal>
            <div>
                <x-section-heading :eyebrow="site('home_intro_eyebrow')" :title="site('home_intro_title')" />
                <p class="mt-6 max-w-xl text-lg leading-relaxed text-ink/70" data-reveal>{{ site('home_intro_text') }}</p>
                @if (site('home_intro_button'))<a href="{{ route('about') }}" class="btn-primary mt-8 px-7 py-3.5" data-reveal>{{ site('home_intro_button') }}</a>@endif
            </div>
        </section>
    @endif

    {{-- Categories --}}
    @if (site_shows('home_categories_show') && $categories->isNotEmpty())
        <section class="pink-abstract mt-24 py-16 sm:py-20">
            <div class="page">
                <x-section-heading :eyebrow="site('home_categories_eyebrow')" :title="site('home_categories_title')" center />
                <div class="mt-10 grid grid-cols-2 gap-4 lg:grid-cols-4">
                    @foreach ($categories->values() as $i => $category)
                        <a href="{{ route('menu', ['category' => $category->slug]) }}" class="group relative block h-56 overflow-hidden rounded-3xl bg-sand sm:h-72 {{ $i === 0 ? $firstSpan : '' }}" data-reveal style="--delay: {{ ($i % 4) * 60 }}ms">
                            <img src="{{ $category->imageUrl() }}" alt="" loading="lazy" class="absolute inset-0 size-full object-cover transition duration-700 group-hover:scale-[1.03]">
                            <span class="absolute inset-0 bg-linear-to-t from-cocoa-950/70 via-cocoa-950/10 to-transparent"></span>
                            <span class="absolute inset-x-0 bottom-0 p-5">
                                <span class="block text-xl font-semibold leading-tight text-white">{{ $category->name }}</span>
                                <span class="mt-3 inline-flex rounded-full bg-rose-600 px-4 py-1.5 text-sm font-medium text-white transition group-hover:bg-rose-700">{{ site('home_categories_button') }}</span>
                            </span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Favourites --}}
    @if (site_shows('home_featured_show') && $featured->isNotEmpty())
        <section class="page pt-24" x-data="slider" aria-roledescription="carousel">
            <x-section-heading :eyebrow="site('home_featured_eyebrow')" :title="site('home_featured_title')" center />
            <div class="relative mt-10">
                <div x-ref="track" @scroll.debounce.50ms="update()" class="scrollbar-none flex snap-x snap-mandatory gap-5 overflow-x-auto pb-2">
                    @foreach ($featured as $product)
                        <x-product-card :product="$product" class="w-[70%] shrink-0 snap-start sm:w-[calc((100%-2.5rem)/3)] lg:w-[calc((100%-3.75rem)/4)]" />
                    @endforeach
                </div>
                <button type="button" @click="prev()" x-show="!atStart" x-transition.opacity class="absolute left-2 top-[38%] hidden size-11 -translate-y-1/2 place-items-center rounded-full bg-white text-cocoa-950 shadow-lift transition hover:bg-rose-600 hover:text-white sm:grid" aria-label="Previous"><x-icon name="chevron-left" class="size-5" /></button>
                <button type="button" @click="next()" x-show="!atEnd" x-transition.opacity class="absolute right-2 top-[38%] hidden size-11 -translate-y-1/2 place-items-center rounded-full bg-white text-cocoa-950 shadow-lift transition hover:bg-rose-600 hover:text-white sm:grid" aria-label="Next"><x-icon name="chevron-right" class="size-5" /></button>
            </div>
            @if (site('home_featured_button'))<div class="mt-10 text-center"><a href="{{ route('menu') }}" class="btn-outline">{{ site('home_featured_button') }}</a></div>@endif
        </section>
    @endif

    {{-- How ordering works --}}
    @if (site_shows('home_steps_show'))
        <section class="pink-abstract mt-24 py-16 sm:py-20">
            <div class="page">
                <x-section-heading :eyebrow="site('home_steps_eyebrow')" :title="site('home_steps_title')" center />
                <ol class="mx-auto mt-12 grid max-w-5xl gap-10 text-center md:grid-cols-3">
                    @foreach ([1, 2, 3] as $n)
                        <li data-reveal style="--delay: {{ $n * 80 }}ms">
                            <span class="mx-auto grid size-12 place-items-center rounded-full bg-rose-600 text-lg font-semibold text-white">{{ $n }}</span>
                            <h3 class="mt-5 text-xl text-cocoa-950">{{ site("home_step_{$n}_title") }}</h3>
                            <p class="mx-auto mt-2 max-w-xs text-ink/65">{{ site("home_step_{$n}_text") }}</p>
                        </li>
                    @endforeach
                </ol>
                <div class="mt-12 text-center"><a href="{{ route('order') }}" class="btn-rose px-7 py-3.5">{{ site('hero_button') ?: 'Order online' }}</a></div>
            </div>
        </section>
    @endif

    {{-- Gallery preview: an endless strip that glides on its own and pauses on hover --}}
    @if (site_shows('home_gallery_show') && $gallery->isNotEmpty())
        <section class="pt-24">
            <div class="page">
                <x-section-heading :eyebrow="site('home_gallery_eyebrow')" :title="site('home_gallery_title')" center />
            </div>
            <div class="marquee mt-10 overflow-hidden [mask-image:linear-gradient(to_right,transparent,black_6%,black_94%,transparent)]" style="--marquee-duration: {{ max(30, $gallery->count() * 6) }}s">
                {{-- The photos are listed twice; the track slides by half its width, then loops seamlessly --}}
                <div class="marquee-track flex w-max">
                    @foreach ([false, true] as $clone)
                        @foreach ($gallery as $item)
                            <a href="{{ route('gallery') }}" @if ($clone) aria-hidden="true" tabindex="-1" data-marquee-clone @endif class="group block w-60 shrink-0 pr-4 sm:w-72 lg:w-80">
                                <span class="block overflow-hidden rounded-3xl bg-sand">
                                    <img src="{{ $item->url() }}" alt="{{ $clone ? '' : $item->caption }}" loading="lazy" class="aspect-square w-full object-cover transition duration-700 group-hover:scale-[1.04]">
                                </span>
                            </a>
                        @endforeach
                    @endforeach
                </div>
            </div>
            @if (site('home_gallery_button'))<div class="page mt-10 text-center"><a href="{{ route('gallery') }}" class="btn-outline">{{ site('home_gallery_button') }}</a></div>@endif
        </section>
    @endif

    {{-- Reviews --}}
    @if (site_shows('home_reviews_show') && $testimonials->isNotEmpty())
        <section class="page pt-24">
            <x-section-heading :eyebrow="site('home_reviews_eyebrow')" :title="site('home_reviews_title')" center />
            @include('site.partials.reviews', ['testimonials' => $testimonials])
            @if (site('home_reviews_button'))<p class="mt-12 text-center"><a href="{{ route('review') }}" class="link-rose">{{ site('home_reviews_button') }}</a></p>@endif
        </section>
    @endif
</x-layouts.site>
