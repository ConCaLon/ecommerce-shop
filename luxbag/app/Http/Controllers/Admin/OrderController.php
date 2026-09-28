<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\User;
use App\Services\GhnService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderController extends Controller
{
    protected GhnService $ghnService;

    public function __construct(GhnService $ghnService)
    {
        $this->ghnService = $ghnService;
    }

    public function index(Request $request)
    {
        $query = Order::with(['user'])->orderByDesc('created_at');

        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('payment')) $query->where('payment_method', $request->payment);
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('order_code', 'LIKE', "%{$request->search}%")
                  ->orWhere('shipping_name', 'LIKE', "%{$request->search}%")
                  ->orWhere('shipping_phone', 'LIKE', "%{$request->search}%");
            });
        }
        if ($request->filled('date_from')) $query->where('created_at', '>=', $request->date_from);
        if ($request->filled('date_to'))   $query->where('created_at', '<=', $request->date_to . ' 23:59:59');

        $orders = $query->paginate(20)->withQueryString();

        $statusCounts = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')->pluck('count', 'status');

        return view('admin.orders.index', compact('orders', 'statusCounts'));
    }

    public function show(Order $order)
    {
        $order->load(['user', 'details.product.primaryImage', 'details.variant', 'payment', 'shippingMethod']);
        return view('admin.orders.show', compact('order'));
    }

    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,processing,shipping,delivered,cancelled,refunded',
            'note'   => 'nullable|string|max:500',
        ]);

        $timestamps = [
            'confirmed'  => 'confirmed_at',
            'shipping'   => 'shipped_at',
            'delivered'  => 'delivered_at',
            'cancelled'  => 'cancelled_at',
        ];

        $updateData = ['status' => $request->status];

        if (isset($timestamps[$request->status])) {
            $updateData[$timestamps[$request->status]] = now();
        }

        if ($request->status === 'delivered') {
            $updateData['payment_status'] = 'paid';
        }

        if ($request->status === 'cancelled' && $request->filled('note')) {
            $updateData['cancelled_reason'] = $request->note;
            // Restore stock
            foreach ($order->details as $detail) {
                if ($detail->product) {
                    $detail->product->increment('stock', $detail->quantity);
                    $detail->product->decrement('sold_count', $detail->quantity);
                }
            }
        }

        $order->update($updateData);

        // Log activity
        activity_log(auth()->id(), 'update_order_status', 'Order', $order->id,
            "Cập nhật đơn hàng #{$order->order_code} sang trạng thái: {$request->status}");

        return response()->json(['success' => true, 'status' => $request->status]);
    }

    public function invoice(Order $order)
    {
        $order->load(['user', 'details.product', 'details.variant', 'shippingMethod']);
        return view('admin.orders.invoice', compact('order'));
    }

    public function exportCsv(Request $request)
    {
        $query = Order::with('user')->orderByDesc('created_at');
        if ($request->filled('status')) $query->where('status', $request->status);
        if ($request->filled('date_from')) $query->where('created_at', '>=', $request->date_from);
        if ($request->filled('date_to')) $query->where('created_at', '<=', $request->date_to . ' 23:59:59');

        $orders = $query->get();

        $filename = 'orders-' . date('Y-m-d') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF)); // UTF-8 BOM
            fputcsv($handle, ['Mã đơn', 'Khách hàng', 'SĐT', 'Tổng tiền', 'Trạng thái', 'Thanh toán', 'Ngày đặt']);
            foreach ($orders as $order) {
                fputcsv($handle, [
                    $order->order_code,
                    $order->shipping_name,
                    $order->shipping_phone,
                    number_format($order->total, 0, ',', '.'),
                    $order->status_label,
                    $order->payment_method_label,
                    $order->created_at->format('d/m/Y H:i'),
                ]);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Đẩy đơn hàng lên GHN để tạo vận đơn.
     */
    public function pushToGhn(Order $order)
    {
        if ($order->ghn_order_code) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng này đã được đẩy lên GHN trước đó. Mã vận đơn: ' . $order->ghn_order_code,
            ], 422);
        }

        if (!$order->ghn_district_id || !$order->ghn_ward_code) {
            return response()->json([
                'success' => false,
                'message' => 'Đơn hàng thiếu thông tin địa chỉ GHN (district_id / ward_code). Khách hàng đã chọn địa chỉ qua GHN chưa?',
            ], 422);
        }

        $order->load('details.product');

        $result = $this->ghnService->createShippingOrder($order);

        if ($result && isset($result['order_code'])) {
            $order->update([
                'ghn_order_code' => $result['order_code'],
                'ghn_pushed_at'  => now(),
                'status'         => 'shipping',
                'shipped_at'     => now(),
            ]);

            activity_log(auth()->id(), 'push_ghn', 'Order', $order->id,
                "Đẩy đơn #{$order->order_code} lên GHN thành công. Mã vận đơn: {$result['order_code']}");

            return response()->json([
                'success'        => true,
                'ghn_order_code' => $result['order_code'],
                'message'        => 'Đẩy đơn lên GHN thành công! Mã vận đơn: ' . $result['order_code'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không thể tạo vận đơn GHN. Vui lòng kiểm tra log hoặc thử lại.',
        ], 500);
    }
}
