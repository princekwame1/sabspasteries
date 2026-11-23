<x-layouts.admin :title="$product->exists ? $product->name : 'New product'">
    @if ($product->exists)
        <x-slot:actions>
            @if ($product->is_active)<a href="{{ route('menu.show', $product) }}" target="_blank" class="btn-ghost hidden bg-white sm:inline-flex">View on site <x-icon name="external" class="size-3.5" /></a>@endif
        </x-slot:actions>
    @endif

    <form method="post" action="{{ $product->exists ? route('admin.products.update', $product) : route('admin.products.store') }}" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[1fr_22rem]">
        @csrf
        @if ($product->exists) @method('put') @endif
        <div class="space-y-6">
            <section class="card space-y-4 p-6">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="label" for="name">Name</label>
                        <input id="name" name="name" value="{{ old('name', $product->name) }}" required class="input text-base" placeholder="e.g. Vintage heart cake">
                        @error('name')<p class="error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="label" for="category_id">Category</label>
                        <select id="category_id" name="category_id" class="input">
                            <option value="">None</option>
                            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected((string) old('category_id', $product->category_id) === (string) $c->id)>{{ $c->name }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <div>
                    <label class="label" for="summary">Short description <span class="font-normal normal-case tracking-normal text-ink/40">(menu cards)</span></label>
                    <textarea id="summary" name="summary" rows="2" maxlength="300" class="input">{{ old('summary', $product->summary) }}</textarea>
                    @error('summary')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="description">Full description <span class="font-normal normal-case tracking-normal text-ink/40">(product page)</span></label>
                    <textarea id="description" name="description" rows="7" class="input">{{ old('description', $product->description) }}</textarea>
                    <p class="mt-1.5 text-xs text-ink/45">Blank line between paragraphs; **bold** and "- " bullet points work.</p>
                </div>
                <div>
                    <label class="label" for="sizes">Sizes <span class="font-normal normal-case tracking-normal text-ink/40">(one per line, optional)</span></label>
                    <textarea id="sizes" name="sizes" rows="4" class="input" placeholder="6 inch (10-12 slices)&#10;8 inch (18-20 slices)&#10;Box of 6&#10;Box of 12">{{ old('sizes', $product->sizes) }}</textarea>
                    <p class="mt-1.5 text-xs text-ink/45">Shown on the product page and offered on the order form.</p>
                </div>
            </section>

            <section class="card p-6">
                <h2 class="font-semibold text-cocoa-950">More photos</h2>
                <p class="mt-1 text-sm text-ink/50">Shown in the gallery on the product page.</p>
                @if ($product->exists && $product->images->isNotEmpty())
                    <div class="mt-4 grid grid-cols-3 gap-3 sm:grid-cols-5">
                        @foreach ($product->images as $image)
                            <div class="group relative">
                                <img src="{{ $image->url() }}" alt="" class="aspect-square w-full rounded-xl object-cover">
                                <button form="delete-image-{{ $image->id }}" class="absolute right-1.5 top-1.5 grid size-7 place-items-center rounded-full bg-white/90 text-red-600 opacity-0 shadow transition group-hover:opacity-100" aria-label="Remove photo"><x-icon name="trash" class="size-3.5" /></button>
                            </div>
                        @endforeach
                    </div>
                @endif
                <input name="photos[]" type="file" multiple accept="image/jpeg,image/png,image/webp" class="input mt-4 file:mr-3 file:rounded-full file:border-0 file:bg-cocoa-950 file:px-3 file:py-1 file:text-xs file:text-white">
                @error('photos.*')<p class="error">{{ $message }}</p>@enderror
            </section>
        </div>

        <section class="card space-y-4 self-start p-6">
            <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active)) class="size-4 accent-rose-600"> Show on the menu</label>
            <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured)) class="size-4 accent-rose-600"> Customer favourite (home page)</label>
            <div>
                <label class="label" for="image">Main photo</label>
                @if ($product->image_path)<img src="{{ $product->imageUrl() }}" alt="" class="mb-2 aspect-[4/5] w-full rounded-xl object-cover">@endif
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="input file:mr-3 file:rounded-full file:border-0 file:bg-cocoa-950 file:px-3 file:py-1 file:text-xs file:text-white">
                @error('image')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label" for="price_from">Price from ({{ \App\Models\Setting::get('currency') }})</label>
                    <input id="price_from" name="price_from" type="number" min="0" step="0.01" value="{{ old('price_from', $product->price_from) }}" class="input" placeholder="On request">
                    @error('price_from')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="price_note">Price note</label>
                    <input id="price_note" name="price_note" value="{{ old('price_note', $product->price_note) }}" class="input" placeholder="per box of 6">
                </div>
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="label" for="lead_days">Notice (days)</label>
                    <input id="lead_days" name="lead_days" type="number" min="0" value="{{ old('lead_days', $product->lead_days) }}" class="input" placeholder="{{ \App\Models\Setting::get('lead_days') }}">
                    @error('lead_days')<p class="error">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="label" for="sort">Order</label>
                    <input id="sort" name="sort" type="number" min="0" value="{{ old('sort', $product->sort) }}" class="input">
                </div>
            </div>
            <div>
                <label class="label" for="slug">Web address</label>
                <input id="slug" name="slug" value="{{ old('slug', $product->slug) }}" class="input" placeholder="Made from the name">
                @error('slug')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div class="flex gap-2 pt-2">
                <button class="btn-primary flex-1">Save</button>
                <a href="{{ route('admin.products.index') }}" class="btn-ghost">Back</a>
            </div>
        </section>
    </form>

    @if ($product->exists)
        @foreach ($product->images as $image)
            <form id="delete-image-{{ $image->id }}" method="post" action="{{ route('admin.products.images.destroy', [$product, $image]) }}" data-confirm="Remove this photo?" class="hidden">@csrf @method('delete')</form>
        @endforeach
        <form method="post" action="{{ route('admin.products.destroy', $product) }}" data-confirm="Delete “{{ $product->name }}”? Past orders keep their details." class="mt-6">
            @csrf @method('delete')
            <button class="btn-danger"><x-icon name="trash" class="size-4" /> Delete product</button>
        </form>
    @endif
</x-layouts.admin>
