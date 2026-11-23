<x-layouts.admin title="Gallery">
    <form method="post" action="{{ route('admin.gallery.store') }}" enctype="multipart/form-data" class="card flex flex-wrap items-end gap-4 p-6">
        @csrf
        <div class="min-w-64 flex-1">
            <label class="label" for="files">Add photos or videos</label>
            <input id="files" name="files[]" type="file" multiple required accept="image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime" class="input file:mr-3 file:rounded-full file:border-0 file:bg-cocoa-950 file:px-3 file:py-1 file:text-xs file:text-white">
            <p class="mt-1.5 text-xs text-ink/45">Up to 20 at once. Videos up to 60 MB (short clips work best).</p>
            @error('files')<p class="error">{{ $message }}</p>@enderror
            @error('files.*')<p class="error">{{ $message }}</p>@enderror
        </div>
        <div>
            <label class="label" for="category_id">Category</label>
            <select id="category_id" name="category_id" class="input">
                <option value="">None</option>
                @foreach ($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach
            </select>
        </div>
        <button class="btn-primary"><x-icon name="upload" class="size-4" /> Upload</button>
    </form>

    <div class="mt-6 flex gap-2 overflow-x-auto scrollbar-none">
        <a href="{{ route('admin.gallery.index') }}" class="shrink-0 rounded-full px-4 py-2 text-sm font-medium {{ request('category') ? 'bg-white text-ink/65 shadow-soft' : 'bg-cocoa-950 text-white' }}">All</a>
        @foreach ($categories as $c)
            <a href="{{ route('admin.gallery.index', ['category' => $c->id]) }}" class="shrink-0 rounded-full px-4 py-2 text-sm font-medium {{ request('category') == $c->id ? 'bg-cocoa-950 text-white' : 'bg-white text-ink/65 shadow-soft' }}">{{ $c->name }}</a>
        @endforeach
    </div>

    <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4">
        @forelse ($items as $item)
            <div class="card overflow-hidden" x-data="{ edit: false }">
                <div class="relative">
                    @if ($item->isVideo())
                        <video src="{{ $item->url() }}#t=0.5" muted playsinline preload="metadata" controls class="aspect-square w-full bg-cocoa-950 object-cover"></video>
                    @else
                        <img src="{{ $item->url() }}" alt="" loading="lazy" class="aspect-square w-full object-cover {{ $item->is_published ? '' : 'opacity-50 grayscale' }}">
                    @endif
                    <div class="pointer-events-none absolute left-3 top-3 flex gap-1.5">
                        @if ($item->is_featured)<x-admin.status status="featured" label="Featured" />@endif
                        @unless ($item->is_published)<x-admin.status status="hidden" label="Hidden" class="bg-white" />@endunless
                    </div>
                </div>
                <div class="p-4">
                    <div class="flex items-center justify-between gap-2">
                        <p class="min-w-0 truncate text-sm {{ $item->caption ? 'text-ink/80' : 'text-ink/40' }}">{{ $item->caption ?: 'No caption' }}</p>
                        <button type="button" @click="edit = !edit" class="btn-ghost btn-sm shrink-0">Edit</button>
                    </div>
                    <p class="text-xs text-ink/45">{{ $item->category?->name ?? 'No category' }}</p>
                    <form x-show="edit" x-cloak method="post" action="{{ route('admin.gallery.update', $item) }}" class="mt-4 space-y-3">
                        @csrf @method('put')
                        <input name="caption" value="{{ $item->caption }}" class="input" placeholder="Caption, e.g. Sweet 16 heart cake" aria-label="Caption">
                        <select name="category_id" class="input" aria-label="Category">
                            <option value="">No category</option>
                            @foreach ($categories as $c)<option value="{{ $c->id }}" @selected($item->category_id === $c->id)>{{ $c->name }}</option>@endforeach
                        </select>
                        <div class="flex flex-wrap gap-4 text-sm">
                            <label class="flex items-center gap-2"><input type="checkbox" name="is_published" value="1" @checked($item->is_published) class="accent-rose-600"> On the site</label>
                            <label class="flex items-center gap-2"><input type="checkbox" name="is_featured" value="1" @checked($item->is_featured) class="accent-rose-600"> Featured</label>
                        </div>
                        <input type="hidden" name="sort" value="{{ $item->sort }}">
                        <button class="btn-primary btn-sm w-full">Save</button>
                    </form>
                    <form x-show="edit" x-cloak method="post" action="{{ route('admin.gallery.destroy', $item) }}" data-confirm="Remove this from the gallery?" class="mt-2">
                        @csrf @method('delete')
                        <button class="btn-danger btn-sm w-full">Delete</button>
                    </form>
                </div>
            </div>
        @empty
            <p class="col-span-full rounded-3xl bg-white px-6 py-16 text-center text-ink/50 shadow-soft">The gallery is empty. Upload your favourite bakes above.</p>
        @endforelse
    </div>
    <div class="mt-6">{{ $items->links() }}</div>
</x-layouts.admin>
