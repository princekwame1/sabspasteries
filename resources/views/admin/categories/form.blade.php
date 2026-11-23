<x-layouts.admin :title="$category->exists ? 'Edit category' : 'New category'">
    <form method="post" action="{{ $category->exists ? route('admin.categories.update', $category) : route('admin.categories.store') }}" enctype="multipart/form-data" class="grid max-w-4xl gap-6 lg:grid-cols-[1fr_18rem]">
        @csrf
        @if ($category->exists) @method('put') @endif
        <section class="card space-y-4 p-6">
            <div>
                <label class="label" for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $category->name) }}" required class="input text-base" placeholder="e.g. Celebration cakes">
                @error('name')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="summary">Short description</label>
                <textarea id="summary" name="summary" rows="3" maxlength="300" class="input">{{ old('summary', $category->summary) }}</textarea>
                @error('summary')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="slug">Web address</label>
                <input id="slug" name="slug" value="{{ old('slug', $category->slug) }}" class="input" placeholder="Made from the name">
                @error('slug')<p class="error">{{ $message }}</p>@enderror
            </div>
        </section>
        <section class="card space-y-4 self-start p-6">
            <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active)) class="size-4 accent-rose-600"> Show on the website</label>
            <div>
                <label class="label" for="image">Photo</label>
                @if ($category->image_path)
                    <img src="{{ $category->imageUrl() }}" alt="" class="mb-2 aspect-square w-32 rounded-full object-cover">
                    <label class="mb-3 flex items-center gap-2 text-xs text-ink/60"><input type="checkbox" name="remove_image" value="1"> Remove photo</label>
                @else
                    <p class="mb-2 text-xs text-ink/45">Optional. Without one, the first product photo is used.</p>
                @endif
                <input id="image" name="image" type="file" accept="image/jpeg,image/png,image/webp" class="input file:mr-3 file:rounded-full file:border-0 file:bg-cocoa-950 file:px-3 file:py-1 file:text-xs file:text-white">
                @error('image')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="sort">Order</label>
                <input id="sort" name="sort" type="number" min="0" value="{{ old('sort', $category->sort) }}" class="input">
            </div>
            <div class="flex gap-2 pt-2">
                <button class="btn-primary flex-1">Save</button>
                <a href="{{ route('admin.categories.index') }}" class="btn-ghost">Cancel</a>
            </div>
        </section>
    </form>
</x-layouts.admin>
