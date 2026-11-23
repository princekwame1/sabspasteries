@props(['product'])
<article {{ $attributes->merge(['class' => 'group relative']) }}>
    <a href="{{ route('menu.show', $product) }}" class="block">
        <div class="overflow-hidden rounded-3xl bg-sand">
            <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" loading="lazy" decoding="async"
                class="aspect-square w-full object-cover transition duration-500 group-hover:scale-[1.03]">
        </div>
        <div class="mt-4 px-1 text-center">
            @if ($product->relationLoaded('category') && $product->category)
                <p class="text-xs text-ink/50">{{ $product->category->name }}</p>
            @endif
            <h3 class="mt-1 text-lg leading-snug text-cocoa-950 transition group-hover:text-rose-600">{{ $product->name }}</h3>
            <p class="mt-1 text-[0.95rem] font-medium text-rose-600">{{ $product->priceLabel() }}</p>
        </div>
    </a>
</article>
