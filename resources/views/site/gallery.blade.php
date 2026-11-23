@php
    $viewer = $items->map(fn ($i) => ['src' => $i->url(), 'video' => $i->isVideo(), 'caption' => $i->caption])->values();
@endphp
<x-layouts.site :title="$current ? $current->name.' gallery' : 'Gallery'" :description="site('gallery_text')">
    <x-page-hero :eyebrow="site('gallery_eyebrow')" :title="site('gallery_title')">
        <p class="mt-6 max-w-2xl text-lg text-ink/70">{{ site('gallery_text') }}</p>
    </x-page-hero>

    @if ($categories->count() > 1)
        <nav class="page flex gap-2 overflow-x-auto pt-10 scrollbar-none" aria-label="Filter">
            <a href="{{ route('gallery') }}" class="shrink-0 rounded-full px-5 py-2.5 text-sm font-medium transition {{ $current ? 'bg-white text-cocoa-800 shadow-soft hover:text-rose-600' : 'bg-cocoa-950 text-white' }}">Everything</a>
            @foreach ($categories as $category)
                <a href="{{ route('gallery', ['category' => $category->slug]) }}" class="shrink-0 rounded-full px-5 py-2.5 text-sm font-medium transition {{ $current?->is($category) ? 'bg-cocoa-950 text-white' : 'bg-white text-cocoa-800 shadow-soft hover:text-rose-600' }}">{{ $category->name }}</a>
            @endforeach
        </nav>
    @endif

    <section class="page pt-10" x-data="gallery(@js($viewer))">
        @if ($items->isEmpty())
            <p class="py-20 text-center text-ink/60">{{ site('gallery_empty') }}</p>
        @else
            <div class="columns-2 gap-4 md:columns-3 lg:columns-4">
                @foreach ($items as $i => $item)
                    <button type="button" @click="show({{ $i }})" class="group relative mb-4 block w-full overflow-hidden rounded-3xl bg-rose-50 text-left" aria-label="Open {{ $item->caption ?: ($item->isVideo() ? 'video' : 'photo') }}">
                        @if ($item->isVideo())
                            <video src="{{ $item->url() }}#t=0.5" muted playsinline preload="metadata" class="w-full" onmouseenter="this.play()" onmouseleave="this.pause()"></video>
                            <span class="absolute left-1/2 top-1/2 grid size-14 -translate-x-1/2 -translate-y-1/2 place-items-center rounded-full bg-white/90 text-rose-600"><x-icon name="play" class="size-5" /></span>
                        @else
                            <img src="{{ $item->thumbUrl() }}" alt="{{ $item->caption }}" loading="lazy" decoding="async" class="w-full transition duration-700 group-hover:scale-105">
                        @endif
                        @if ($item->caption)
                            <span class="pointer-events-none absolute inset-x-0 bottom-0 bg-gradient-to-t from-cocoa-950/70 to-transparent p-4 pt-10 text-sm text-white opacity-0 transition group-hover:opacity-100">{{ $item->caption }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        @endif
        @include('site.partials.lightbox')
    </section>
</x-layouts.site>
