<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\FlashSale;
use App\Models\Product;
use Illuminate\Http\Request;

class FlashSaleController extends Controller
{
    public function index()
    {
        $flashSales = FlashSale::withCount('products')->orderByDesc('created_at')->paginate(10);
        return view('admin.flash_sales.index', compact('flashSales'));
    }
    public function create() { return view('admin.flash_sales.create'); }
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:150',
            'starts_at' => 'required|date',
            'ends_at' => 'required|date|after:starts_at',
        ]);
        FlashSale::create($request->all());
        return redirect()->route('admin.flash-sales.index')->with('success', 'Flash Sale đã được tạo.');
    }
    public function edit(FlashSale $flashSale) { return view('admin.flash_sales.edit', compact('flashSale')); }
    public function update(Request $request, FlashSale $flashSale)
    {
        $flashSale->update($request->all());
        return redirect()->route('admin.flash-sales.index')->with('success', 'Flash Sale đã được cập nhật.');
    }
    public function destroy(FlashSale $flashSale)
    {
        $flashSale->delete();
        return back()->with('success', 'Flash Sale đã được xóa.');
    }
    public function show(FlashSale $flashSale) { return view('admin.flash_sales.show', compact('flashSale')); }
    public function addProduct(Request $request, FlashSale $flashSale)
    {
        $request->validate(['product_id' => 'required|exists:products,id', 'discount_percent' => 'required|numeric|between:1,99']);
        $product = Product::findOrFail($request->product_id);
        $salePrice = $product->price * (1 - $request->discount_percent / 100);
        $flashSale->products()->syncWithoutDetaching([
            $product->id => ['discount_percent' => $request->discount_percent, 'sale_price' => $salePrice]
        ]);
        return back()->with('success', 'Đã thêm sản phẩm vào Flash Sale.');
    }
    public function removeProduct(FlashSale $flashSale, Product $product)
    {
        $flashSale->products()->detach($product->id);
        return back()->with('success', 'Đã xóa sản phẩm khỏi Flash Sale.');
    }
}
