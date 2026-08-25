<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = User::role('customer')->withCount('orders');

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%")
                  ->orWhere('phone', 'LIKE', "%{$request->search}%");
            });
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $customers = $query->latest()->paginate(20)->withQueryString();
        return view('admin.customers.index', compact('customers'));
    }

    public function show(User $user)
    {
        $user->load(['orders' => fn($q) => $q->latest()->take(10)]);
        $totalSpent = $user->orders()->where('status', 'delivered')->sum('total');
        return view('admin.customers.show', compact('user', 'totalSpent'));
    }

    public function toggleStatus(User $user)
    {
        if ($user->isAdmin()) {
            return response()->json(['success' => false, 'message' => 'Không thể khóa tài khoản admin.']);
        }
        $user->update(['status' => $user->status === 'active' ? 'banned' : 'active']);
        return response()->json(['success' => true, 'status' => $user->status]);
    }

    public function updateRole(Request $request, User $user)
    {
        $request->validate(['role' => 'required|string|exists:roles,name']);
        $user->syncRoles([$request->role]);
        return back()->with('success', 'Vai trò đã được cập nhật.');
    }
}
