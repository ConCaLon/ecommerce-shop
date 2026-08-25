<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use App\Models\Review;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // ─── Today's Stats ────────────────────────────────────────
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $thisMonth = Carbon::now()->startOfMonth();
        $lastMonth = Carbon::now()->subMonth()->startOfMonth();
        $lastMonthEnd = Carbon::now()->subMonth()->endOfMonth();

        $stats = [
            'revenue_today' => Order::where('status', 'delivered')
                ->whereDate('created_at', $today)->sum('total'),
            'revenue_month' => Order::where('status', 'delivered')
                ->where('created_at', '>=', $thisMonth)->sum('total'),
            'orders_today' => Order::whereDate('created_at', $today)->count(),
            'orders_pending' => Order::where('status', 'pending')->count(),
            'orders_shipping' => Order::where('status', 'shipping')->count(),
            'new_customers' => User::whereDate('created_at', $today)->count(),
            'total_customers' => User::role('customer')->count(),
            'total_products' => Product::active()->count(),
            'low_stock' => Product::active()->where('stock', '<', 10)->count(),
        ];

        // ─── Revenue Chart (last 30 days) ─────────────────────────
        $revenueChart = Order::where('status', 'delivered')
            ->where('created_at', '>=', Carbon::now()->subDays(29))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get()
            ->keyBy('date');

        $chartDates = [];
        $chartRevenue = [];
        $chartOrders = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $chartDates[] = Carbon::now()->subDays($i)->format('d/m');
            $chartRevenue[] = $revenueChart[$date]->revenue ?? 0;
            $chartOrders[] = $revenueChart[$date]->orders ?? 0;
        }

        // ─── Top Products ─────────────────────────────────────────
        $topProducts = Product::active()
            ->with('primaryImage')
            ->orderByDesc('sold_count')
            ->take(5)
            ->get();

        // ─── Recent Orders ────────────────────────────────────────
        $recentOrders = Order::with('user')
            ->orderByDesc('created_at')
            ->take(10)
            ->get();

        // ─── Order Status Distribution ────────────────────────────
        $orderStatusChart = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->pluck('count', 'status');

        // ─── Category Revenue ─────────────────────────────────────
        $categoryRevenue = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.status', 'delivered')
            ->select('categories.name', DB::raw('SUM(order_details.subtotal) as revenue'))
            ->groupBy('categories.id', 'categories.name')
            ->orderByDesc('revenue')
            ->take(6)
            ->get();

        return view('admin.dashboard.index', compact(
            'stats', 'chartDates', 'chartRevenue', 'chartOrders',
            'topProducts', 'recentOrders', 'orderStatusChart', 'categoryRevenue'
        ));
    }
}
