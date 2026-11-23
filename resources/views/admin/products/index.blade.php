<x-layouts.admin title="Products">
    <x-slot:actions>
        <a href="{{ route('admin.products.create', array_filter(['category' => request('category')])) }}" class="btn-primary"><x-icon name="plus" class="size-4" /> New product</a>
    </x-slot:actions>

    <form method="get" class="flex flex-wrap gap-3">
        <div class="relative min-w-60 flex-1">
            <x-icon name="search" class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-ink/40" />
            <input name="q" value="{{ request('q') }}" class="input bg-white pl-10 shadow-soft" placeholder="Search products" aria-label="Search">
        </div>
        <select name="category" class="input w-auto bg-white shadow-soft" data-autosubmit aria-label="Category">
            <option value="">All categories</option>
            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected(request('category') == $c->id)>{{ $c->name }}</option>@endforeach
        </select>
    </form>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
        @forelse ($products as $product)
            <a href="{{ route('admin.products.edit', $product) }}" class="card group overflow-hidden transition hover:-translate-y-0.5 hover:shadow-lift">
                <div class="relative">
                    <img src="{{ $product->imageUrl() }}" alt="" loading="lazy" class="aspect-[4/3] w-full object-cover {{ $product->is_active ? '' : 'opacity-50 grayscale' }}">
                    <div class="absolute left-3 top-3 flex gap-1.5">
                        @unless ($product->is_active)<x-admin.status status="hidden" label="Hidden" class="bg-white" />@endunless
                        @if ($product->is_featured)<x-admin.status status="featured" label="Favourite" />@endif
                    </div>
                </div>
                <div class="p-5">
                    <p class="font-semibold text-cocoa-950 group-hover:text-rose-600">{{ $product->name }}</p>
                    <p class="mt-0.5 text-xs text-ink/50">{{ $product->category?->name ?? 'No category' }}</p>
                    <div class="mt-3 flex items-center justify-between text-sm">
                        <span class="font-medium text-rose-700">{{ $product->priceLabel() }}</span>
                        <span class="text-xs text-ink/45">{{ $product->orders_count }} {{ Str::plural('order', $product->orders_count) }}</span>
                    </div>
                </div>
            </a>
        @empty
            <p class="col-span-full rounded-3xl bg-white px-6 py-16 text-center text-ink/50 shadow-soft">No products yet.</p>
        @endforelse
    </div>
</x-layouts.admin>
