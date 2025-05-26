<?php
return [
    /*
    |--------------------------------------------------------------------------
    | Okeconnect Merchant ID
    |--------------------------------------------------------------------------
    |
    | ID merchant yang diberikan oleh sistem Okeconnect.
    |
    */
    'merchantId' => env('OKECONNECT_MERCHANT_ID', ''),

    /*
    |--------------------------------------------------------------------------
    | Okeconnect Merchant PIN
    |--------------------------------------------------------------------------
    |
    | PIN merchant yang dibuat di laman Okeconnect.
    |
    */
    'merchantPin' => env('OKECONNECT_MERCHANT_PIN', ''),

    /*
    |--------------------------------------------------------------------------
    | Okeconnect Account Password
    |--------------------------------------------------------------------------
    |
    | password akun Orderkuota.
    |
    */
    'accountPassword' => env('OKECONNECT_ACCOUNT_PASSWORD'),

        /*
        |--------------------------------------------------------------------------
        | Okeconnect server url
        |--------------------------------------------------------------------------
        |
        | server url untuk transaksi Okeconnect. 
        | default: https://h2h.okeconnect.com/trx
        |
        */
        'serverUrl' => env('OKECONNECT_SERVER_URL', 'https://h2h.okeconnect.com'),
];
