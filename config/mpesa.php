<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa Daraja API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration settings for the M-Pesa Daraja API.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | API Environment
    |--------------------------------------------------------------------------
    |
    | The environment in which the API is running.
    | Options: 'sandbox', 'production'
    |
    */
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    /*
    |--------------------------------------------------------------------------
    | API Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for the API endpoints.
    |
    */
    'base_url' => env('MPESA_ENVIRONMENT', 'sandbox') === 'production'
        ? 'https://api.safaricom.co.ke'
        : 'https://sandbox.safaricom.co.ke',

    /*
    |--------------------------------------------------------------------------
    | API Credentials
    |--------------------------------------------------------------------------
    |
    | Consumer Key and Consumer Secret for authentication.
    |
    */
    'consumer_key' => env('MPESA_CONSUMER_KEY', ''),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET', ''),

    /*
    |--------------------------------------------------------------------------
    | Business Shortcode
    |--------------------------------------------------------------------------
    |
    | The organization's shortcode (Paybill or Till Number).
    |
    */
    'shortcode' => env('MPESA_SHORTCODE', ''),

    /*
    |--------------------------------------------------------------------------
    | STK Push Passkey
    |--------------------------------------------------------------------------
    |
    | The passkey for STK Push transactions.
    |
    */
    'passkey' => env('MPESA_PASSKEY', ''),

    /*
    |--------------------------------------------------------------------------
    | B2C Credentials
    |--------------------------------------------------------------------------
    |
    | Credentials for B2C (Business to Customer) transactions.
    |
    */
    'initiator_name' => env('MPESA_INITIATOR_NAME', ''),
    'security_credential' => env('MPESA_SECURITY_CREDENTIAL', ''),

    /*
    |--------------------------------------------------------------------------
    | Callback URLs
    |--------------------------------------------------------------------------
    |
    | URLs for receiving callbacks from M-Pesa.
    |
    */
    'callback_url' => env('MPESA_CALLBACK_URL', ''),
    'timeout_url' => env('MPESA_TIMEOUT_URL', ''),
    'b2c_result_url' => env('MPESA_B2C_RESULT_URL', ''),
    'b2c_queue_timeout_url' => env('MPESA_B2C_QUEUE_TIMEOUT_URL', ''),
];