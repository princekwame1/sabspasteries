{{-- Heading panel at the top of inner pages, with an optional photo on the right.
     Same width as the home hero: runs nearly edge to edge, content aligned with the header. --}}
@props(['title', 'eyebrow' => null, 'image' => null])
<section {{ $attributes->merge(['class' => 'px-3 pt-24 sm:px-5']) }}>
    <div class="overflow-hidden rounded-[2rem] bg-sand">
        <div class="page grid items-center gap-10 py-12 sm:py-16 {{ $image ? 'lg:grid-cols-[1.4fr_1fr] lg:py-12' : '' }}">
            <div class="animate-fade-up">
                @if ($eyebrow)<p class="eyebrow">{{ $eyebrow }}</p>@endif
                <h1 class="mt-2 max-w-3xl text-4xl leading-[1.1] text-cocoa-950 sm:text-5xl">{{ rich($title) }}</h1>
                {{ $slot }}
            </div>
            @if ($image)
                <div class="hidden justify-end lg:flex">
                    <img src="{{ $image }}" alt="" fetchpriority="high" class="aspect-[4/5] w-64 rounded-3xl object-cover">
                </div>
            @endif
        </div>
    </div>
</section>
