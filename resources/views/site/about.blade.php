@php
    $values = collect(range(1, 4))->map(fn ($n) => [site("about_value_{$n}_title"), site("about_value_{$n}_text")])->filter(fn ($v) => $v[0] !== '')->values();
    $paragraphs = preg_split('/\n\s*\n/', site('about'));
@endphp
<x-layouts.site title="About us" :description="Str::limit(site('about'), 155)">
    <x-page-hero :eyebrow="site('about_eyebrow')" :title="site('about_title')" />

    <section class="page grid items-start gap-16 pt-24 lg:grid-cols-[1fr_1.15fr] lg:gap-24">
        <div class="relative mx-auto w-full max-w-md lg:sticky lg:top-28 lg:max-w-none" data-reveal>
            <img src="{{ \App\Models\Setting::imageUrl('about_image_path') }}" alt="" loading="lazy" class="aspect-[4/5] w-full rounded-3xl object-cover">
        </div>
        <div data-reveal style="--delay: 100ms">
            <h2 class="font-display text-3xl leading-[1.1] text-cocoa-950 sm:text-4xl">{{ rich(site('about_story_title')) }}</h2>
            <div class="mt-10 space-y-6 text-[1.05rem] leading-8 text-ink/70">
                @foreach ($paragraphs as $i => $paragraph)
                    <p class="{{ $i === 0 ? 'text-xl leading-relaxed text-cocoa-900' : '' }}">{{ $paragraph }}</p>
                @endforeach
            </div>
            <div class="mt-12 flex flex-wrap gap-3">
                @if (site('about_button'))<a href="{{ route('order') }}" class="btn-rose px-8 py-4">{{ site('about_button') }}</a>@endif
                @if (site('about_button2'))<a href="{{ route('menu') }}" class="btn-outline px-8 py-4">{{ site('about_button2') }}</a>@endif
            </div>
        </div>
    </section>

    @if (site_shows('about_values_show') && $values->isNotEmpty())
        <section class="page pt-28">
            <div class="rounded-[2rem] bg-sand px-6 py-16 sm:px-12">
                <x-section-heading :eyebrow="site('about_values_eyebrow')" :title="site('about_values_title')" center />
                <div class="mt-14 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($values as $i => [$heading, $text])
                        <div class="text-center" data-reveal style="--delay: {{ $i * 80 }}ms">
                            <span class="mx-auto grid size-12 place-items-center rounded-full bg-rose-600 text-lg font-semibold text-white">{{ $i + 1 }}</span>
                            <p class="mt-5 text-xl font-semibold text-cocoa-950">{{ $heading }}</p>
                            @if ($text)<p class="mt-2 text-[0.95rem] leading-relaxed text-ink/65">{{ $text }}</p>@endif
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if (site_shows('about_reviews_show') && $testimonials->isNotEmpty())
        <section class="page pt-28">
            <x-section-heading :eyebrow="site('home_reviews_eyebrow')" :title="site('home_reviews_title')" center />
            @include('site.partials.reviews', ['testimonials' => $testimonials])
        </section>
    @endif
</x-layouts.site>
