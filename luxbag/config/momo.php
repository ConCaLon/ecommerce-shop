<?php

return [

    /*
    |--------------------------------------------------------------------------
    | MoMo Payment Gateway Configuration
    |--------------------------------------------------------------------------
    */

    'endpoint'     => env('MOMO_ENDPOINT', 'https://test-payment.momo.vn/v2/gateway/api/create'),
    'partner_code' => env('MOMO_PARTNER_CODE', ''),
    'access_key'   => env('MOMO_ACCESS_KEY', ''),
    'secret_key'   => env('MOMO_SECRET_KEY', ''),
    'verify_ssl'   => env('MOMO_VERIFY_SSL', true),
    'redirect_url' => env('MOMO_REDIRECT_URL', ''),
    'ipn_url'      => env('MOMO_IPN_URL', ''),

];
