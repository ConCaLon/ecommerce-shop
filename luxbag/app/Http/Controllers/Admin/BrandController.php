<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class BrandController extends Controller
{
    public function index() {
        $brands = Brand::withCount('products')->orderBy('name')->paginate(20);
        return view('admin.brands.index', compact('brands'));
    }
    public function create() { return view('admin.brands.create'); }
    public function store(Request $request) {
        $request->validate(['name' => 'required|string|max:150', 'website' => 'nullable|url']);
        $data = $request->except('logo');
        $data['slug'] = Str::slug($request->name);
        if ($request->hasFile('logo')) {
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }
        Brand::create($data);
        return redirect()->route('admin.brands.index')->with('success', 'Thương hiệu đã được tạo.');
    }
    public function edit(Brand $brand) { return view('admin.brands.edit', compact('brand')); }
    public function update(Request $request, Brand $brand) {
        $data = $request->except('logo');
        if ($request->hasFile('logo')) {
            if ($brand->logo) Storage::disk('public')->delete($brand->logo);
            $data['logo'] = $request->file('logo')->store('brands', 'public');
        }
        $brand->update($data);
        return redirect()->route('admin.brands.index')->with('success', 'Thương hiệu đã được cập nhật.');
    }
    public function destroy(Brand $brand) {
        $brand->delete();
        return back()->with('success', 'Thương hiệu đã được xóa.');
    }
    public function show(Brand $brand) { return redirect()->route('admin.brands.index'); }
}
