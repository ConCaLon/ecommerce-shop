<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MomoController extends Controller
{
    protected MomoService $momoService;

    public function __construct(MomoService $momoService)
    {
        $this->momoService = $momoService;
    }

    /**
     * Xử lý redirect URL từ MoMo (user quay lại site).
     */
    public function callback(Request $request)
    {
        $data = $request->all();

        Log::info('MoMo Callback received', $data);

        // Extract order code from extraData
        $orderCode = $this->momoService->extractOrderCode($data);

        if (!$orderCode) {
            return redirect()->route('home')->with('error', 'Không tìm thấy thông tin đơn hàng.');
        }

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            return redirect()->route('home')->with('error', 'Đơn hàng không tồn tại.');
        }

        // Verify signature
        if ($this->momoService->verifySignature($data)) {
            if ($this->momoService->isPaymentSuccess($data)) {
                // Update payment status
                $this->updatePaymentStatus($order, $data, 'completed');
                return redirect()->route('orders.confirmation', $order->order_code)
                    ->with('momo_success', true);
            } else {
                // Payment failed
                $this->updatePaymentStatus($order, $data, 'failed');
                return redirect()->route('orders.show', $order->order_code)
                    ->with('error', 'Thanh toán MoMo không thành công. Mã lỗi: ' . ($data['resultCode'] ?? 'N/A'));
            }
        }

        // Invalid signature
        Log::warning('MoMo callback invalid signature', $data);
        return redirect()->route('orders.show', $order->order_code)
            ->with('error', 'Không thể xác thực thanh toán. Vui lòng liên hệ hỗ trợ.');
    }

    /**
     * Xử lý IPN (Instant Payment Notification) từ MoMo.
     * MoMo gọi POST đến endpoint này, cần trả về status 204 hoặc JSON.
     */
    public function ipn(Request $request)
    {
        $data = $request->all();

        Log::info('MoMo IPN received', $data);

        // Extract order code
        $orderCode = $this->momoService->extractOrderCode($data);

        if (!$orderCode) {
            Log::warning('MoMo IPN: Cannot extract order code');
            return response()->json(['message' => 'Order not found'], 400);
        }

        $order = Order::where('order_code', $orderCode)->first();

        if (!$order) {
            Log::warning('MoMo IPN: Order not found', ['order_code' => $orderCode]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Verify signature
        if (!$this->momoService->verifySignature($data)) {
            Log::warning('MoMo IPN: Invalid signature', $data);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        if ($this->momoService->isPaymentSuccess($data)) {
            $this->updatePaymentStatus($order, $data, 'completed');
        } else {
            $this->updatePaymentStatus($order, $data, 'failed');
        }

        // MoMo expects a 204 response
        return response()->noContent();
    }

    /**
     * Cập nhật trạng thái thanh toán.
     */
    protected function updatePaymentStatus(Order $order, array $data, string $status): void
    {
        $transId = $data['transId'] ?? null;

        // Update payment record
        $payment = $order->payment;
        if ($payment) {
            $payment->update([
                'status'           => $status,
                'transaction_id'   => $transId,
                'gateway_response' => $data,
                'paid_at'          => $status === 'completed' ? now() : null,
            ]);
        }

        // Update order payment status
        $order->update([
            'payment_status' => $status === 'completed' ? 'paid' : 'failed',
        ]);
    }
}
