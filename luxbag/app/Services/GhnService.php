<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

class GhnService
{
    protected string $baseUrl;
    protected string $token;
    protected string $shopId;
    protected bool $verifySsl;
    protected int $fromDistrictId;
    protected string $fromWardCode;

    public function __construct()
    {
        $this->baseUrl        = rtrim(config('ghn.base_url'), '/');
        $this->token          = config('ghn.token');
        $this->shopId         = config('ghn.shop_id');
        $this->verifySsl      = config('ghn.verify_ssl', true);
        $this->fromDistrictId = config('ghn.from_district_id');
        $this->fromWardCode   = config('ghn.from_ward_code');
    }

    /**
     * Send a request to GHN API.
     */
    protected function request(string $method, string $endpoint, array $data = []): ?array
    {
        try {
            $http = Http::withHeaders([
                'Token'   => $this->token,
                'ShopId'  => $this->shopId,
                'Content-Type' => 'application/json',
            ]);

            if (!$this->verifySsl) {
                $http = $http->withoutVerifying();
            }

            $url = $this->baseUrl . $endpoint;

            $response = $method === 'GET'
                ? $http->get($url, $data)
                : $http->post($url, $data);

            if ($response->successful()) {
                $body = $response->json();
                if (isset($body['code']) && $body['code'] == 200) {
                    return $body['data'] ?? [];
                }
                Log::warning('GHN API returned non-200 code', ['response' => $body]);
            } else {
                Log::error('GHN API HTTP error', [
                    'status' => $response->status(),
                    'body'   => $response->body(),
                ]);
            }
        } catch (\Exception $e) {
            Log::error('GHN API Exception: ' . $e->getMessage());
        }

        return null;
    }

    /**
     * Lấy danh sách tỉnh/thành phố.
     * Cache 24h vì danh sách tỉnh ít khi thay đổi.
     */
    public function getProvinces(): array
    {
        return Cache::remember('ghn_provinces', 86400, function () {
            $data = $this->request('GET', '/master-data/province');
            if (!$data) return [];

            return collect($data)->map(fn($p) => [
                'ProvinceID'   => $p['ProvinceID'],
                'ProvinceName' => $p['ProvinceName'],
            ])->sortBy('ProvinceName')->values()->toArray();
        });
    }

    /**
     * Lấy danh sách quận/huyện theo tỉnh.
     * Cache 24h.
     */
    public function getDistricts(int $provinceId): array
    {
        return Cache::remember("ghn_districts_{$provinceId}", 86400, function () use ($provinceId) {
            $data = $this->request('POST', '/master-data/district', [
                'province_id' => $provinceId,
            ]);
            if (!$data) return [];

            return collect($data)->map(fn($d) => [
                'DistrictID'   => $d['DistrictID'],
                'DistrictName' => $d['DistrictName'],
            ])->sortBy('DistrictName')->values()->toArray();
        });
    }

    /**
     * Lấy danh sách phường/xã theo quận/huyện.
     * Cache 24h.
     */
    public function getWards(int $districtId): array
    {
        return Cache::remember("ghn_wards_{$districtId}", 86400, function () use ($districtId) {
            $data = $this->request('POST', '/master-data/ward', [
                'district_id' => $districtId,
            ]);
            if (!$data) return [];

            return collect($data)->map(fn($w) => [
                'WardCode' => $w['WardCode'],
                'WardName' => $w['WardName'],
            ])->sortBy('WardName')->values()->toArray();
        });
    }

    /**
     * Tính phí vận chuyển.
     *
     * @param int    $toDistrictId  ID quận/huyện đích (từ GHN)
     * @param string $toWardCode    Mã phường/xã đích (từ GHN)
     * @param int    $weight        Tổng trọng lượng (gram)
     * @param int    $insuranceValue Giá trị bảo hiểm (= tổng tiền hàng, VNĐ)
     * @return array|null           ['total' => phí ship, 'service_fee' => ..., 'insurance_fee' => ...]
     */
    public function calculateShippingFee(int $toDistrictId, string $toWardCode, int $weight = 500, int $insuranceValue = 0): ?array
    {
        $data = $this->request('POST', '/v2/shipping-order/fee', [
            'service_type_id' => config('ghn.service_type_id', 2),
            'from_district_id' => $this->fromDistrictId,
            'from_ward_code'   => $this->fromWardCode,
            'to_district_id'   => $toDistrictId,
            'to_ward_code'     => $toWardCode,
            'weight'           => $weight,
            'insurance_value'  => $insuranceValue,
        ]);

        if ($data) {
            return [
                'total'          => $data['total'] ?? 0,
                'service_fee'    => $data['service_fee'] ?? 0,
                'insurance_fee'  => $data['insurance_fee'] ?? 0,
            ];
        }

        return null;
    }

    /**
     * Lấy thời gian giao hàng dự kiến.
     *
     * @return string|null  e.g. "2-3 ngày"
     */
    public function getEstimatedDeliveryTime(int $toDistrictId, string $toWardCode): ?string
    {
        $data = $this->request('POST', '/v2/shipping-order/leadtime', [
            'service_type_id'  => config('ghn.service_type_id', 2),
            'from_district_id' => $this->fromDistrictId,
            'from_ward_code'   => $this->fromWardCode,
            'to_district_id'   => $toDistrictId,
            'to_ward_code'     => $toWardCode,
        ]);

        if ($data && isset($data['leadtime'])) {
            $leadtime = $data['leadtime'];
            $deliveryDate = \Carbon\Carbon::createFromTimestamp($leadtime);
            $days = now()->diffInDays($deliveryDate);
            return $days <= 1 ? '1-2 ngày' : "{$days} ngày";
        }

        return null;
    }

    /**
     * Tính tổng trọng lượng từ giỏ hàng.
     * Mỗi sản phẩm dùng weight mặc định (config) × số lượng.
     */
    public function calculateTotalWeight($cartItems): int
    {
        $defaultWeight = config('ghn.default_weight', 500);
        $totalWeight = 0;

        foreach ($cartItems as $item) {
            $weight = $item->product->weight ?? $defaultWeight;
            $totalWeight += $weight * $item->quantity;
        }

        return $totalWeight;
    }

    /**
     * Tạo đơn vận chuyển trên GHN.
     * Trả về mã vận đơn GHN (order_code) nếu thành công, null nếu thất bại.
     *
     * @param \App\Models\Order $order  Đơn hàng đã load quan hệ details.product
     * @return array|null               ['order_code' => '...', 'expected_delivery_time' => '...', ...]
     */
    public function createShippingOrder(Order $order): ?array
    {
        $items = [];
        foreach ($order->details as $detail) {
            $items[] = [
                'name'     => mb_substr($detail->product_name, 0, 100),
                'code'     => (string) ($detail->product_sku ?? $detail->product_id),
                'quantity' => (int) $detail->quantity,
                'price'    => (int) $detail->unit_price,
                'weight'   => (int) ($detail->product->weight ?? config('ghn.default_weight', 500)),
            ];
        }

        // Tổng trọng lượng
        $totalWeight = 0;
        foreach ($order->details as $detail) {
            $w = $detail->product->weight ?? config('ghn.default_weight', 500);
            $totalWeight += (int) $w * (int) $detail->quantity;
        }

        $payload = [
            'payment_type_id' => $order->payment_method === 'cod' ? 2 : 1,
            // 2 = người mua trả phí ship (COD), 1 = người bán trả phí ship
            'note'            => $order->notes ?? '',
            'required_note'   => 'KHONGCHOXEMHANG',
            'to_name'         => $order->shipping_name,
            'to_phone'        => $order->shipping_phone,
            'to_address'      => $order->shipping_address,
            'to_ward_code'    => (string) $order->ghn_ward_code,
            'to_district_id'  => (int) $order->ghn_district_id,
            'cod_amount'      => $order->payment_method === 'cod' ? (int) $order->total : 0,
            'weight'          => $totalWeight > 0 ? $totalWeight : 500,
            'service_type_id' => config('ghn.service_type_id', 2),
            'items'           => $items,
        ];

        $data = $this->request('POST', '/v2/shipping-order/create', $payload);

        if ($data && isset($data['order_code'])) {
            Log::info('GHN shipping order created', [
                'local_order' => $order->order_code,
                'ghn_code'    => $data['order_code'],
            ]);
            return $data;
        }

        Log::error('GHN createShippingOrder failed', [
            'order_code' => $order->order_code,
            'payload'    => $payload,
        ]);
        return null;
    }
}
