<x-layouts.admin title="Categories">
    <x-slot:actions>
        <a href="{{ route('admin.categories.create') }}" class="btn-primary"><x-icon name="plus" class="size-4" /> New category</a>
    </x-slot:actions>
    <p class="mb-6 max-w-2xl text-sm text-ink/55">Categories group the menu (Celebration cakes, Cupcakes...). A category shows on the site once it has a product.</p>
    <div class="card overflow-x-auto">
        <table class="w-full min-w-[36rem] text-left text-sm">
            <tbody>
                @forelse ($categories as $category)
                    <tr class="odd:bg-cocoa-50/40">
                        <td class="w-20 py-3 pl-5"><img src="{{ $category->imageUrl() }}" alt="" class="size-12 rounded-full object-cover"></td>
                        <td class="py-3">
                            <a href="{{ route('admin.categories.edit', $category) }}" class="font-semibold text-cocoa-950 hover:text-rose-600">{{ $category->name }}</a>
                            <p class="text-xs text-ink/50">{{ $category->products_count }} {{ Str::plural('product', $category->products_count) }}</p>
                        </td>
                        <td class="py-3">@if ($category->is_active)<x-admin.status status="active" label="On the site" />@else<x-admin.status status="hidden" label="Hidden" />@endif</td>
                        <td class="py-3 pr-5 text-right">
                            <div class="flex justify-end gap-1">
                                <a href="{{ route('admin.products.index', ['category' => $category->id]) }}" class="btn-ghost btn-sm">Products</a>
                                <a href="{{ route('admin.categories.edit', $category) }}" class="btn-ghost btn-sm">Edit</a>
                                <form method="post" action="{{ route('admin.categories.destroy', $category) }}" data-confirm="Delete “{{ $category->name }}”? Its products stay on the menu without a category.">@csrf @method('delete')<button class="btn-danger btn-sm">Delete</button></form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td class="px-5 py-10 text-center text-ink/50">No categories yet.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin>
