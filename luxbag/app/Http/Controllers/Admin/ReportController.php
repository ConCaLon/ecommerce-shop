<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return redirect()->route('admin.reports.revenue');
    }

    public function revenue(Request $request)
    {
        $period = $request->get('period', 'monthly');
        $year = $request->get('year', date('Y'));
        $month = $request->get('month', date('m'));

        if ($period === 'daily') {
            $data = Order::where('status', 'delivered')
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->select(DB::raw('DAY(created_at) as day'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
                ->groupBy('day')->orderBy('day')->get();

            $labels = $data->pluck('day')->map(fn($d) => "Ngày $d")->toArray();
        } else {
            $data = Order::where('status', 'delivered')
                ->whereYear('created_at', $year)
                ->select(DB::raw('MONTH(created_at) as month'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
                ->groupBy('month')->orderBy('month')->get();

            $monthNames = ['T1','T2','T3','T4','T5','T6','T7','T8','T9','T10','T11','T12'];
            $labels = $data->pluck('month')->map(fn($m) => $monthNames[$m - 1])->toArray();
        }

        $totalRevenue = $data->sum('revenue');
        $totalOrders  = $data->sum('orders');

        return view('admin.reports.revenue', compact(
            'data', 'labels', 'period', 'year', 'month',
            'totalRevenue', 'totalOrders'
        ));
    }

    public function topProducts(Request $request)
    {
        $limit = $request->get('limit', 10);
        $period = $request->get('period', 30); // days

        $topProducts = DB::table('order_details')
            ->join('products', 'order_details.product_id', '=', 'products.id')
            ->join('orders', 'order_details.order_id', '=', 'orders.id')
            ->where('orders.status', 'delivered')
            ->where('orders.created_at', '>=', Carbon::now()->subDays($period))
            ->select(
                'products.id', 'products.name', 'products.sku',
                DB::raw('SUM(order_details.quantity) as total_sold'),
                DB::raw('SUM(order_details.subtotal) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.sku')
            ->orderByDesc('total_sold')
            ->limit($limit)
            ->get();

        return view('admin.reports.top_products', compact('topProducts', 'limit', 'period'));
    }

    public function customers(Request $request)
    {
        $topCustomers = User::role('customer')
            ->withCount('orders')
            ->withSum(['orders' => fn($q) => $q->where('status', 'delivered')], 'total')
            ->orderByDesc('orders_sum_total')
            ->take(20)
            ->get();

        return view('admin.reports.customers', compact('topCustomers'));
    }

    public function export(Request $request)
    {
        $type = $request->get('type', 'revenue');
        $filename = 'report-' . date('Y-m-d') . '.csv';
        $callback = null;

        if ($type === 'revenue') {
            $data = Order::where('status', 'delivered')
                ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
                ->groupBy('date')->orderByDesc('date')->get();

            $filename = 'revenue-report-' . date('Y-m-d') . '.csv';
            $callback = function () use ($data) {
                $handle = fopen('php://output', 'w');
                fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));
                fputcsv($handle, ['Ngày', 'Doanh thu (đ)', 'Số đơn hàng']);
                foreach ($data as $row) {
                    fputcsv($handle, [$row->date, number_format($row->revenue, 0), $row->orders]);
                }
                fclose($handle);
            };
        } else {
            // Default fallback for unsupported types
            $callback = function () {
                $handle = fopen('php://output', 'w');
                fputcsv($handle, ['Không có dữ liệu']);
                fclose($handle);
            };
        }

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }
}
