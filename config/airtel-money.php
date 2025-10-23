<?php

return [
    'client_id' => env('AIRTEL_MONEY_CLIENT_ID'),
    'client_secret' => env('AIRTEL_MONEY_CLIENT_SECRET'),
    'country' => env('AIRTEL_MONEY_COUNTRY', 'UG'),
    'currency' => env('AIRTEL_MONEY_CURRENCY', 'UGX'),
    // in storage path...
    'public_key' => env('AIRTEL_MONEY_PUBLIC_KEY', 'airtel.pub'),
    'encrypted_pin' => env('AIRTEL_MONEY_ENCRYPTED_PIN'),

    'base_uri' => env('AIRTEL_MONEY_BASE_URI', 'https://openapiuat.airtel.africa'),

    'token_uri' => env('AIRTEL_MONEY_TOKEN_URI', '/auth/oauth2/token'),
    'kyc_uri' => env('AIRTEL_MONEY_KYC_URI', '/standard/v1/users/:phoneNumber'),

    'collection' => [
        'payment_uri' => env('AIRTEL_MONEY_COLLECTION_PAYMENT_URI', '/merchant/v2/payments/'),
        'refund_uri' => env('AIRTEL_MONEY_COLLECTION_REFUND_URI', '/standard/v1/payments/refund'),
        'transaction_inquiry_uri' => env('AIRTEL_MONEY_COLLECTION_TRANSACTION_INQUIRY_URI', '/standard/v1/payments/:transactionId'),
        'balance_inquiry_uri' => env('AIRTEL_MONEY_COLLECTION_BALANCE_INQUIRY_URI', '/standard/v1/users/balance'),
    ],

    'disbursement' => [
        'payment_uri' => env('AIRTEL_MONEY_DISBURSEMENT_PAYMENT_URI', '/standard/v1/disbursements/'),
        'transaction_inquiry_uri' => env('AIRTEL_MONEY_DISBURSEMENT_TRANSACTION_INQUIRY_URI', '/standard/v2/disbursements/:transactionId'),
    ],

    /*
     * http://docs.guzzlephp.org/en/stable/request-options.html
     */
    'guzzle' => [
        'options' => [
            // 'verify' => false,
        ],
    ],
];
