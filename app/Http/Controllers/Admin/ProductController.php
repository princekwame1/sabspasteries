<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        return view('admin.products.index', [
            'products' => Product::with('category:id,name')->withCount('orders')
                ->when($request->query('category'), fn ($q, $id) => $q->where('category_id', $id))
                ->when($request->query('q'), fn ($q, $term) => $q->where('name', 'like', "%{$term}%"))
                ->orderBy('sort')->orderBy('name')->get(),
            'categories' => Category::orderBy('sort')->get(['id', 'name']),
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.products.form', [
            'product' => new Product(['is_active' => true, 'category_id' => $request->query('category'), 'sort' => Product::max('sort') + 1]),
            'categories' => Category::orderBy('sort')->get(['id', 'name']),
        ]);
    }

    public function store(Request $request)
    {
        $product = Product::create($this->validated($request, new Product));
        $this->storeImages($request, $product);

        return redirect()->route('admin.products.index')->with('status', '“'.$product->name.'” added to the menu.');
    }

    public function edit(Product $product)
    {
        return view('admin.products.form', [
            'product' => $product->load('images'),
            'categories' => Category::orderBy('sort')->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Product $product)
    {
        $product->update($this->validated($request, $product));
        $this->storeImages($request, $product);

        return redirect()->route('admin.products.edit', $product)->with('status', 'Saved.');
    }

    public function destroy(Product $product)
    {
        $product->delete(); // past orders keep their item name

        return redirect()->route('admin.products.index')->with('status', 'Product deleted.');
    }

    public function destroyImage(Product $product, ProductImage $image)
    {
        abort_unless($image->product_id === $product->id, 404);
        Storage::disk('public')->delete($image->path);
        $image->delete();

        return back()->with('status', 'Photo removed.');
    }

    private function validated(Request $request, Product $product): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'slug' => ['nullable', 'alpha_dash', 'max:140', Rule::unique('products', 'slug')->ignore($product)],
            'category_id' => ['nullable', 'exists:categories,id'],
            'summary' => ['nullable', 'string', 'max:300'],
            'description' => ['nullable', 'string', 'max:20000'],
            'price_from' => ['nullable', 'numeric', 'min:0', 'max:999999'],
            'price_note' => ['nullable', 'string', 'max:80'],
            'sizes' => ['nullable', 'string', 'max:2000'],
            'lead_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
            'photos' => ['nullable', 'array', 'max:12'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);
        $data['slug'] = ($data['slug'] ?? null) ?: ($product->slug ?: Slug::unique(Product::class, $data['name'], $product, 'treat'));
        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['sort'] = (int) ($data['sort'] ?? 0);

        if ($request->hasFile('image')) {
            $product->image_path && Storage::disk('public')->delete($product->image_path);
            $data['image_path'] = $request->file('image')->store('products', 'public');
        }
        unset($data['image'], $data['photos']);

        return $data;
    }

    /** Extra photos shown on the product page. */
    private function storeImages(Request $request, Product $product): void
    {
        $sort = (int) $product->images()->max('sort');
        foreach ($request->file('photos', []) as $file) {
            $product->images()->create(['path' => $file->store('products', 'public'), 'sort' => ++$sort]);
        }
    }
}
