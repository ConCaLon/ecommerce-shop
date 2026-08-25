<?php

namespace App\Http\Controllers;

use App\Models\Wishlist;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
    public function index()
    {
        $wishlistItems = Wishlist::where('user_id', Auth::id())
            ->with(['product.primaryImage', 'product.category'])
            ->latest()
            ->paginate(12);

        return view('profile.wishlist', compact('wishlistItems'));
    }

    public function toggle($productId)
    {
        $product = Product::findOrFail($productId);

        $existing = Wishlist::where([
            'user_id' => Auth::id(),
            'product_id' => $productId,
        ])->first();

        if ($existing) {
            $existing->delete();
            $action = 'removed';
            $message = 'Đã xóa khỏi danh sách yêu thích.';
        } else {
            Wishlist::create(['user_id' => Auth::id(), 'product_id' => $productId]);
            $action = 'added';
            $message = 'Đã thêm vào danh sách yêu thích!';
        }

        $count = Wishlist::where('user_id', Auth::id())->count();

        if (request()->ajax()) {
            return response()->json(['success' => true, 'action' => $action, 'message' => $message, 'count' => $count]);
        }

        return back()->with('success', $message);
    }

    public function remove($productId)
    {
        Wishlist::where(['user_id' => Auth::id(), 'product_id' => $productId])->delete();
        return back()->with('success', 'Đã xóa khỏi danh sách yêu thích.');
    }
}
