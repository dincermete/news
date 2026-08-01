<?php

return [

    /*
    |--------------------------------------------------------------------------
    | TCMB exchange rate feed
    |--------------------------------------------------------------------------
    |
    | Daily rates are published around 15:30 Europe/Istanbul. We schedule a
    | fetch at 16:00 and use ForexSelling (bank sell) for USD → TRY conversion.
    |
    */

    'tcmb' => [
        'url' => env('TCMB_EXCHANGE_RATE_URL', 'https://www.tcmb.gov.tr/kurlar/today.xml'),
        'rate_field' => env('TCMB_RATE_FIELD', 'ForexSelling'),
        'source' => 'tcmb',
        'timeout' => (int) env('TCMB_HTTP_TIMEOUT', 15),
    ],

    'timezone' => env('CURRENCY_TIMEZONE', 'Europe/Istanbul'),

    'base_currency' => 'TRY',

];
