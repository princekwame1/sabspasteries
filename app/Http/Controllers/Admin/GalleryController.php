<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\GalleryItem;
use Illuminate\Http\Request;

/** Photo and video gallery: upload several at once, then caption, tag and feature them. */
class GalleryController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.gallery.index', [
            'items' => GalleryItem::with('category:id,name')
                ->when($request->query('category'), fn ($q, $id) => $q->where('category_id', $id))
                ->orderByDesc('is_featured')->orderBy('sort')->latest('id')->paginate(48)->withQueryString(),
            'categories' => Category::orderBy('sort')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'files' => ['required', 'array', 'max:20'],
            'files.*' => ['file', 'mimetypes:image/jpeg,image/png,image/webp,video/mp4,video/webm,video/quicktime', 'max:61440'],
            'category_id' => ['nullable', 'exists:categories,id'],
        ], ['files.required' => 'Choose at least one photo or video.']);

        foreach ($request->file('files') as $file) {
            GalleryItem::create([
                'path' => $file->store('gallery', 'public'),
                'kind' => str_starts_with((string) $file->getMimeType(), 'video/') ? 'video' : 'photo',
                'category_id' => $request->input('category_id'),
                'sort' => 0,
            ]);
        }

        return back()->with('status', count($request->file('files')).' added to the gallery.');
    }

    public function update(Request $request, GalleryItem $item)
    {
        $data = $request->validate([
            'caption' => ['nullable', 'string', 'max:200'],
            'category_id' => ['nullable', 'exists:categories,id'],
            'sort' => ['nullable', 'integer', 'min:0'],
        ]);
        $item->update($data + [
            'sort' => (int) ($data['sort'] ?? 0),
            'is_featured' => $request->boolean('is_featured'),
            'is_published' => $request->boolean('is_published'),
        ]);

        return back()->with('status', 'Saved.');
    }

    public function destroy(GalleryItem $item)
    {
        $item->delete();

        return back()->with('status', 'Removed from the gallery.');
    }
}
