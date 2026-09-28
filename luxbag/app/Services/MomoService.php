<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoService
{
    protected string $endpoint;
    protected string $partnerCode;
    protected string $accessKey;
    protected string $secretKey;
    protected bool $verifySsl;
    protected string $redirectUrl;
    protected string $ipnUrl;

    public function __construct()
    {
        $this->endpoint    = config('momo.endpoint');
        $this->partnerCode = config('momo.partner_code');
        $this->accessKey   = config('momo.access_key');
        $this->secretKey   = config('momo.secret_key');
        $this->verifySsl   = config('momo.verify_ssl', true);
        $this->redirectUrl = config('momo.redirect_url');
        $this->ipnUrl      = config('momo.ipn_url');
    }

    /**
     * Tạo link thanh toán MoMo.
     *
     * @param int    $orderId    ID đơn hàng trong DB
     * @param string $orderCode  Mã đơn hàng hiển thị (e.g. LB20260921ABCD)
     * @param int    $amount     Số tiền (VNĐ, integer)
     * @param string $orderInfo  Mô tả đơn hàng
     * @return array ['success' => bool, 'payUrl' => string|null, 'message' => string]
     */
    public function createPayment(int $orderId, string $orderCode, int $amount, string $orderInfo = ''): array
    {
        $requestId = $this->partnerCode . '_' . time() . '_' . $orderId;
        $orderId_momo = $orderCode . '_' . time(); // MoMo yêu cầu orderId unique mỗi request

        if (empty($orderInfo)) {
            $orderInfo = "Thanh toán đơn hàng {$orderCode} tại LuxBag";
        }

        $extraData = base64_encode(json_encode(['order_code' => $orderCode]));

        // Build raw signature string
        $rawSignature = "accessKey={$this->accessKey}"
            . "&amount={$amount}"
            . "&extraData={$extraData}"
            . "&ipnUrl={$this->ipnUrl}"
            . "&orderId={$orderId_momo}"
            . "&orderInfo={$orderInfo}"
            . "&partnerCode={$this->partnerCode}"
            . "&redirectUrl={$this->redirectUrl}"
            . "&requestId={$requestId}"
            . "&requestType=payWithMethod";

        $signature = hash_hmac('sha256', $rawSignature, $this->secretKey);

        $payload = [
            'partnerCode' => $this->partnerCode,
            'partnerName' => 'LuxBag',
            'storeId'     => 'LuxBagStore',
            'requestId'   => $requestId,
            'amount'      => $amount,
            'orderId'     => $orderId_momo,
            'orderInfo'   => $orderInfo,
            'redirectUrl' => $this->redirectUrl,
            'ipnUrl'      => $this->ipnUrl,
            'lang'        => 'vi',
            'requestType' => 'payWithMethod',
            'extraData'   => $extraData,
            'signature'   => $signature,
        ];

        try {
            $http = Http::asJson();
            if (!$this->verifySsl) {
                $http = $http->withoutVerifying();
            }

            $response = $http->post($this->endpoint, $payload);
            $body = $response->json();

            Log::info('MoMo Create Payment Response', ['body' => $body]);

            if (isset($body['resultCode']) && $body['resultCode'] == 0) {
                return [
                    'success' => true,
                    'payUrl'  => $body['payUrl'] ?? null,
                    'message' => $body['message'] ?? 'Success',
                ];
            }

            return [
                'success' => false,
                'payUrl'  => null,
                'message' => $body['message'] ?? 'Không thể tạo thanh toán MoMo',
            ];
        } catch (\Exception $e) {
            Log::error('MoMo Create Payment Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'payUrl'  => null,
                'message' => 'Lỗi kết nối đến MoMo: ' . $e->getMessage(),
            ];
        }
    }

    /**
     * Xác thực callback/IPN từ MoMo bằng HMAC-SHA256 signature.
     *
     * @param array $data  Request data từ MoMo
     * @return bool
     */
    public function verifySignature(array $data): bool
    {
        $rawSignature = "accessKey={$this->accessKey}"
            . "&amount={$data['amount']}"
            . "&extraData={$data['extraData']}"
            . "&message={$data['message']}"
            . "&orderId={$data['orderId']}"
            . "&orderInfo={$data['orderInfo']}"
            . "&orderType={$data['orderType']}"
            . "&partnerCode={$data['partnerCode']}"
            . "&payType={$data['payType']}"
            . "&requestId={$data['requestId']}"
            . "&responseTime={$data['responseTime']}"
            . "&resultCode={$data['resultCode']}"
            . "&transId={$data['transId']}";

        $expectedSignature = hash_hmac('sha256', $rawSignature, $this->secretKey);

        return hash_equals($expectedSignature, $data['signature'] ?? '');
    }

    /**
     * Kiểm tra kết quả thanh toán có thành công không.
     *
     * @param array $data  Request data từ MoMo callback/IPN
     * @return bool
     */
    public function isPaymentSuccess(array $data): bool
    {
        return isset($data['resultCode']) && (int) $data['resultCode'] === 0;
    }

    /**
     * Trích xuất order_code từ extraData.
     */
    public function extractOrderCode(array $data): ?string
    {
        if (!empty($data['extraData'])) {
            $extra = json_decode(base64_decode($data['extraData']), true);
            return $extra['order_code'] ?? null;
        }
        return null;
    }
}
