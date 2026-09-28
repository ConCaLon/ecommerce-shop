<?php

namespace App\Http\Controllers;

use App\Services\GhnService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GhnController extends Controller
{
    protected GhnService $ghnService;

    public function __construct(GhnService $ghnService)
    {
        $this->ghnService = $ghnService;
    }

    /**
     * Lấy danh sách tỉnh/thành phố.
     */
    public function provinces(): JsonResponse
    {
        $provinces = $this->ghnService->getProvinces();
        return response()->json($provinces);
    }

    /**
     * Lấy danh sách quận/huyện theo tỉnh.
     */
    public function districts(int $provinceId): JsonResponse
    {
        $districts = $this->ghnService->getDistricts($provinceId);
        return response()->json($districts);
    }

    /**
     * Lấy danh sách phường/xã theo quận/huyện.
     */
    public function wards(int $districtId): JsonResponse
    {
        $wards = $this->ghnService->getWards($districtId);
        return response()->json($wards);
    }

    /**
     * Tính phí vận chuyển GHN.
     */
    public function calculateFee(Request $request): JsonResponse
    {
        $request->validate([
            'to_district_id' => 'required|integer',
            'to_ward_code'   => 'required|string',
            'insurance_value' => 'nullable|integer|min:0',
            'weight'         => 'nullable|integer|min:1',
        ]);

        $result = $this->ghnService->calculateShippingFee(
            $request->to_district_id,
            $request->to_ward_code,
            $request->weight ?? config('ghn.default_weight', 500),
            $request->insurance_value ?? 0
        );

        if ($result) {
            // Also get estimated delivery time
            $estimatedTime = $this->ghnService->getEstimatedDeliveryTime(
                $request->to_district_id,
                $request->to_ward_code
            );

            return response()->json([
                'success'        => true,
                'total'          => $result['total'],
                'service_fee'    => $result['service_fee'],
                'insurance_fee'  => $result['insurance_fee'],
                'estimated_time' => $estimatedTime,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Không thể tính phí vận chuyển. Vui lòng thử lại.',
        ], 422);
    }
}
