<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Bank transfer discount
    |--------------------------------------------------------------------------
    |
    | Percentage discount applied to the payable amount when the customer
    | chooses bank transfer (havale / EFT).
    |
    */

    'bank_transfer_discount_percent' => 2,

    /*
    |--------------------------------------------------------------------------
    | Bank accounts shown on checkout (havale)
    |--------------------------------------------------------------------------
    */

    'banks' => [
        [
            'name' => 'Ziraat Bankası',
            'iban' => 'TR00 0000 0000 0000 0000 0000 00',
            'account_name' => 'NewsTanıtım',
        ],
        [
            'name' => 'Garanti BBVA',
            'iban' => 'TR00 0000 0000 0000 0000 0000 01',
            'account_name' => 'NewsTanıtım',
        ],
    ],
];
