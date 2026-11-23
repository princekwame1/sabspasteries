<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Support\Slug;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function index()
    {
        return view('admin.categories.index', ['categories' => Category::withCount('products')->orderBy('sort')->orderBy('name')->get()]);
    }

    public function create()
    {
        return view('admin.categories.form', ['category' => new Category(['is_active' => true, 'sort' => Category::max('sort') + 1])]);
    }

    public function store(Request $request)
    {
        Category::create($this->validated($request, new Category));

        return redirect()->route('admin.categories.index')->with('status', 'Category added.');
    }

    public function edit(Category $category)
    {
        return view('admin.categories.form', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $category->update($this->validated($request, $category));

        return redirect()->route('admin.categories.index')->with('status', 'Category saved.');
    }

    public function destroy(Category $category)
    {
        $category->delete(); // its products stay, without a category

        return back()->with('status', 'Category deleted.');
    }

    private function validated(Request $request, Category $category): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'slug' => ['nullable', 'alpha_dash', 'max:100', Rule::unique('categories', 'slug')->ignore($category)],
            'summary' => ['nullable', 'string', 'max:300'],
            'sort' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ]);
        $data['slug'] = ($data['slug'] ?? null) ?: ($category->slug ?: Slug::unique(Category::class, $data['name'], $category, 'category'));
        $data['is_active'] = $request->boolean('is_active');
        $data['sort'] = (int) ($data['sort'] ?? 0);

        if ($request->hasFile('image')) {
            $category->image_path && Storage::disk('public')->delete($category->image_path);
            $data['image_path'] = $request->file('image')->store('categories', 'public');
        } elseif ($request->boolean('remove_image') && $category->image_path) {
            Storage::disk('public')->delete($category->image_path);
            $data['image_path'] = null;
        }
        unset($data['image']);

        return $data;
    }
}
