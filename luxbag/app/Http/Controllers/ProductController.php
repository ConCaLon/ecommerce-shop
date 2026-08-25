<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::active()
            ->with(['primaryImage', 'category', 'brand'])
            ->inStock();

        // Filters
        if ($request->filled('danh-muc')) {
            $category = Category::where('slug', $request->get('danh-muc'))->first();
            if ($category) $query->byCategory($category->id);
        }
        if ($request->filled('thuong-hieu')) {
            $brand = Brand::where('slug', $request->get('thuong-hieu'))->first();
            if ($brand) $query->byBrand($brand->id);
        }
        if ($request->filled('gia-min') || $request->filled('gia-max')) {
            $query->priceBetween($request->get('gia-min'), $request->get('gia-max'));
        }
        if ($request->filled('tim-kiem')) {
            $query->search($request->get('tim-kiem'));
        }

        // Sorting
        switch ($request->get('sap-xep', 'newest')) {
            case 'price-asc':   $query->orderBy('price'); break;
            case 'price-desc':  $query->orderByDesc('price'); break;
            case 'best-seller': $query->orderByDesc('sold_count'); break;
            case 'rating':      $query->orderByDesc('avg_rating'); break;
            default:            $query->orderByDesc('created_at');
        }

        $products = $query->paginate(16)->withQueryString();
        $categories = Category::active()->parents()
            ->withCount(['products' => fn($q) => $q->active()])
            ->orderBy('sort_order')->get();
        $brands = Brand::active()
            ->withCount(['products' => fn($q) => $q->active()])
            ->get();

        $priceMin = Product::active()->min('price');
        $priceMax = Product::active()->max('price');

        return view('products.index', compact('products', 'categories', 'brands', 'priceMin', 'priceMax'));
    }

    public function show(Product $product)
    {
        abort_if($product->status !== 'active', 404);

        $product->load(['images', 'variants', 'category', 'brand', 'reviews.user']);
        $product->incrementViews();

        // Related products
        $related = Product::active()
            ->where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['primaryImage'])
            ->inStock()
            ->take(6)
            ->get();

        // Check if current user wishlisted
        $isWishlisted = false;
        if (auth()->check()) {
            $isWishlisted = $product->wishlistedBy()->where('user_id', auth()->id())->exists();
        }

        // Check if user can review
        $canReview = false;
        $userReview = null;
        if (auth()->check()) {
            $deliveredOrder = auth()->user()->orders()
                ->where('status', 'delivered')
                ->whereHas('details', fn($q) => $q->where('product_id', $product->id))
                ->first();
            $canReview = $deliveredOrder !== null;
            $userReview = $product->reviews()
                ->where('user_id', auth()->id())
                ->where('status', 'approved')
                ->first();
        }

        // Rating breakdown
        $ratingBreakdown = [];
        for ($i = 5; $i >= 1; $i--) {
            $count = $product->reviews()->where('rating', $i)->count();
            $ratingBreakdown[$i] = [
                'count' => $count,
                'percent' => $product->review_count > 0
                    ? round($count / $product->review_count * 100)
                    : 0,
            ];
        }

        return view('products.show', compact(
            'product', 'related', 'isWishlisted',
            'canReview', 'userReview', 'ratingBreakdown'
        ));
    }

    public function search(Request $request)
    {
        $keyword = $request->get('q', '');
        $products = collect();

        if (strlen($keyword) >= 2) {
            $products = Product::active()
                ->search($keyword)
                ->with(['primaryImage', 'category'])
                ->inStock()
                ->paginate(16)
                ->withQueryString();
        }

        return view('products.search', compact('products', 'keyword'));
    }

    public function byCategory(Category $category)
    {
        $products = Product::active()
            ->byCategory($category->id)
            ->with(['primaryImage', 'category', 'brand'])
            ->inStock()
            ->paginate(16);

        return view('products.category', compact('products', 'category'));
    }

    public function byBrand(Brand $brand)
    {
        $products = Product::active()
            ->byBrand($brand->id)
            ->with(['primaryImage', 'category'])
            ->inStock()
            ->paginate(16);

        return view('products.brand', compact('products', 'brand'));
    }
}
