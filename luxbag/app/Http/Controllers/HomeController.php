<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Banner;
use App\Models\FlashSale;
use App\Models\Setting;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        // Hero banners
        $banners = Banner::active()->hero()->orderBy('sort_order')->get();

        // Featured products
        $featuredProducts = Product::active()
            ->featured()
            ->with(['primaryImage', 'category', 'brand'])
            ->inStock()
            ->latest()
            ->take(8)
            ->get();

        // New arrivals
        $newProducts = Product::active()
            ->new()
            ->with(['primaryImage', 'category'])
            ->inStock()
            ->orderByDesc('created_at')
            ->take(8)
            ->get();

        // Best sellers
        $bestSellers = Product::active()
            ->with(['primaryImage', 'category'])
            ->inStock()
            ->orderByDesc('sold_count')
            ->take(8)
            ->get();

        // Categories (parent only with product count)
        $categories = Category::active()
            ->parents()
            ->withCount(['products' => fn($q) => $q->active()])
            ->orderBy('sort_order')
            ->get();

        // Active flash sale
        $flashSale = FlashSale::active()
            ->with(['products' => fn($q) => $q->active()->with('primaryImage')])
            ->first();

        return view('home.index', compact(
            'banners', 'featuredProducts', 'newProducts',
            'bestSellers', 'categories', 'flashSale'
        ));
    }
}
