<?php

return [
    'merchant_id' => env('PAYTR_MERCHANT_ID'),
    'merchant_key' => env('PAYTR_MERCHANT_KEY'),
    'merchant_salt' => env('PAYTR_MERCHANT_SALT'),
    'token_url' => env('PAYTR_TOKEN_URL', 'https://www.paytr.com/odeme/api/get-token'),
    'ok_url' => env('PAYTR_OK_URL', env('APP_URL').'/paytr/ok'),
    'fail_url' => env('PAYTR_FAIL_URL', env('APP_URL').'/paytr/fail'),
    'test_mode' => env('PAYTR_TEST_MODE', '1'),
    'debug_on' => env('PAYTR_DEBUG_ON', '1'),
    'no_installment' => env('PAYTR_NO_INSTALLMENT', '0'),
    'max_installment' => env('PAYTR_MAX_INSTALLMENT', '0'),
    'timeout_limit' => env('PAYTR_TIMEOUT_LIMIT', '30'),
];
