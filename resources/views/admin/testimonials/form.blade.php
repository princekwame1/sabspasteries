<x-layouts.admin :title="$testimonial->exists ? 'Edit review' : 'New review'">
    <form method="post" action="{{ $testimonial->exists ? route('admin.testimonials.update', $testimonial) : route('admin.testimonials.store') }}" class="card max-w-2xl space-y-4 p-6">
        @csrf
        @if ($testimonial->exists) @method('put') @endif
        <div class="grid gap-4 sm:grid-cols-2">
            <div>
                <label class="label" for="name">Name</label>
                <input id="name" name="name" value="{{ old('name', $testimonial->name) }}" required class="input">
                @error('name')<p class="error">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="label" for="role">Who they are <span class="font-normal text-ink/40">(optional)</span></label>
                <input id="role" name="role" value="{{ old('role', $testimonial->role) }}" class="input" placeholder="e.g. Bride, December wedding">
            </div>
        </div>
        <div>
            <label class="label" for="quote">Review</label>
            <textarea id="quote" name="quote" rows="5" required class="input">{{ old('quote', $testimonial->quote) }}</textarea>
            @error('quote')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div class="grid grid-cols-2 gap-4 sm:max-w-sm">
            <div>
                <label class="label" for="rating">Stars</label>
                <select id="rating" name="rating" class="input">
                    @foreach ([5, 4, 3, 2, 1] as $n)<option value="{{ $n }}" @selected(old('rating', $testimonial->rating) == $n)>{{ $n }}</option>@endforeach
                </select>
            </div>
            <div>
                <label class="label" for="sort">Order</label>
                <input id="sort" name="sort" type="number" min="0" value="{{ old('sort', $testimonial->sort) }}" class="input">
            </div>
        </div>
        <label class="flex items-center gap-3 text-sm"><input type="checkbox" name="is_published" value="1" @checked(old('is_published', $testimonial->is_published)) class="size-4 accent-cocoa-700"> Show on the website</label>
        <div class="flex gap-2 pt-2">
            <button class="btn-primary">Save</button>
            <a href="{{ route('admin.testimonials.index') }}" class="btn-ghost">Cancel</a>
        </div>
    </form>
</x-layouts.admin>
