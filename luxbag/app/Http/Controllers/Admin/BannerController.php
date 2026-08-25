<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Banner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BannerController extends Controller
{
    public function index() {
        $banners = Banner::orderBy('sort_order')->paginate(20);
        return view('admin.banners.index', compact('banners'));
    }
    public function create() { return view('admin.banners.create'); }
    public function store(Request $request) {
        $request->validate(['image' => 'required|image|mimes:jpg,jpeg,png,webp|max:5120']);
        $data = $request->except('image');
        $data['image'] = $request->file('image')->store('banners', 'public');
        Banner::create($data);
        return redirect()->route('admin.banners.index')->with('success', 'Banner đã được tạo.');
    }
    public function edit(Banner $banner) { return view('admin.banners.edit', compact('banner')); }
    public function update(Request $request, Banner $banner) {
        $data = $request->except('image');
        if ($request->hasFile('image')) {
            Storage::disk('public')->delete($banner->image);
            $data['image'] = $request->file('image')->store('banners', 'public');
        }
        $banner->update($data);
        return redirect()->route('admin.banners.index')->with('success', 'Banner đã được cập nhật.');
    }
    public function destroy(Banner $banner) {
        Storage::disk('public')->delete($banner->image);
        $banner->delete();
        return back()->with('success', 'Banner đã được xóa.');
    }
    public function show(Banner $banner) { return redirect()->route('admin.banners.index'); }
}
