<?php

namespace App\Http\Controllers;

use App\Models\Review;
use App\Models\Product;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function store(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'title'   => 'nullable|string|max:200',
            'comment' => 'nullable|string|max:1000',
        ]);

        // Verify purchase
        $deliveredOrder = Auth::user()->orders()
            ->where('status', 'delivered')
            ->whereHas('details', fn($q) => $q->where('product_id', $productId))
            ->first();

        if (!$deliveredOrder) {
            return back()->with('error', 'Bạn chỉ có thể đánh giá sản phẩm đã mua.');
        }

        // Check existing review
        $existingReview = Review::where([
            'product_id' => $productId,
            'user_id'    => Auth::id(),
        ])->first();

        if ($existingReview) {
            return back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi.');
        }

        Review::create([
            'product_id' => $productId,
            'user_id'    => Auth::id(),
            'order_id'   => $deliveredOrder->id,
            'rating'     => $request->rating,
            'title'      => $request->title,
            'comment'    => $request->comment,
            'status'     => 'approved', // Auto-approve; change to 'pending' for moderation
        ]);

        $product->updateRating();

        return back()->with('success', 'Cảm ơn bạn đã đánh giá sản phẩm!');
    }

    public function update(Request $request, $id)
    {
        $review = Review::where('user_id', Auth::id())->findOrFail($id);

        $request->validate([
            'rating'  => 'required|integer|between:1,5',
            'title'   => 'nullable|string|max:200',
            'comment' => 'nullable|string|max:1000',
        ]);

        $review->update([
            'rating'  => $request->rating,
            'title'   => $request->title,
            'comment' => $request->comment,
            'status'  => 'approved', // Re-approve after edit
        ]);

        $review->product->updateRating();

        return back()->with('success', 'Đánh giá đã được cập nhật!');
    }

    public function destroy($id)
    {
        $review = Review::where('user_id', Auth::id())->findOrFail($id);
        $product = $review->product;
        $review->delete();
        $product->updateRating();

        return back()->with('success', 'Đã xóa đánh giá.');
    }

    public function markHelpful($id)
    {
        $review = Review::findOrFail($id);
        $review->increment('helpful_count');
        return response()->json(['success' => true, 'count' => $review->helpful_count]);
    }
}
