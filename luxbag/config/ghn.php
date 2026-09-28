<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Giao Hàng Nhanh (GHN) API Configuration
    |--------------------------------------------------------------------------
    */

    'base_url'         => env('GHN_BASE_URL', 'https://dev-online-gateway.ghn.vn/shiip/public-api'),
    'token'            => env('GHN_TOKEN', ''),
    'shop_id'          => env('GHN_SHOP_ID', ''),
    'verify_ssl'       => env('GHN_VERIFY_SSL', true),

    // Địa chỉ gửi hàng (shop)
    'from_district_id' => (int) env('GHN_FROM_DISTRICT_ID', 0),
    'from_ward_code'   => env('GHN_FROM_WARD_CODE', ''),

    // Service type: 2 = E-commerce standard
    'service_type_id'  => 2,

    // Default weight per product (gram) khi chưa có cột weight
    'default_weight'   => 500,

];
