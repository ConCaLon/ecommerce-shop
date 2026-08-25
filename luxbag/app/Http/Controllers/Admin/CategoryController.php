<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::with('parent')
            ->withCount('products')
            ->orderBy('sort_order')
            ->paginate(20);
        $allCategories = Category::whereNull('parent_id')->get();
        return view('admin.categories.index', compact('categories', 'allCategories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'        => 'required|string|max:150',
            'parent_id'   => 'nullable|exists:categories,id',
            'description' => 'nullable|string',
            'image'       => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
            'sort_order'  => 'nullable|integer',
            'status'      => 'required|in:active,inactive',
        ]);

        $data = $request->except('image');
        $data['slug'] = Str::slug($request->name);

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $ext = $file->getClientOriginalExtension() ?: 'jpg';
            $filename = Str::random(16) . '.' . $ext;
            $file->storeAs('categories', $filename);
            $data['image'] = $filename;
        }

        Category::create($data);
        return back()->with('success', 'Danh mục đã được tạo.');
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name'      => 'required|string|max:150',
            'parent_id' => 'nullable|exists:categories,id',
            'image'     => 'nullable|image|mimes:jpg,jpeg,png,webp,gif,svg|max:5120',
            'status'    => 'required|in:active,inactive',
        ]);

        $data = $request->except('image');
        if ($request->hasFile('image')) {
            if ($category->image) Storage::disk('public')->delete('categories/' . $category->image);
            $ext = $request->file('image')->getClientOriginalExtension() ?: 'jpg';
            $filename = Str::random(16) . '.' . $ext;
            $request->file('image')->storeAs('categories', $filename);
            $data['image'] = $filename;
        }

        $category->update($data);
        return back()->with('success', 'Danh mục đã được cập nhật.');
    }

    public function destroy(Category $category)
    {
        // Detach products from this category (set category_id to null)
        $category->products()->update(['category_id' => null]);

        // Delete category image from storage
        if ($category->image) {
            Storage::disk('public')->delete('categories/' . $category->image);
        }

        $category->delete();
        return back()->with('success', 'Danh mục đã được xóa.');
    }

    public function create() { return view('admin.categories.create'); }
    public function edit(Category $category) { return view('admin.categories.edit', compact('category')); }
    public function show(Category $category) { return view('admin.categories.show', compact('category')); }
}
