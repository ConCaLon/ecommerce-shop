<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request)
    {
        $query = Review::with(['product', 'user'])->latest();
        if ($request->filled('status')) $query->where('status', $request->status);
        $reviews = $query->paginate(20)->withQueryString();
        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(Review $review)
    {
        $review->update(['status' => 'approved']);
        $review->product->updateRating();
        return back()->with('success', 'Đánh giá đã được duyệt.');
    }

    public function reject(Review $review)
    {
        $review->update(['status' => 'rejected']);
        $review->product->updateRating();
        return back()->with('success', 'Đánh giá đã bị từ chối.');
    }

    public function reply(Request $request, Review $review)
    {
        $request->validate(['reply' => 'required|string|max:1000']);
        $review->update(['admin_reply' => $request->reply]);
        return back()->with('success', 'Phản hồi đã được lưu.');
    }

    public function destroy(Review $review)
    {
        $product = $review->product;
        $review->delete();
        $product->updateRating();
        return back()->with('success', 'Đánh giá đã được xóa.');
    }
}
