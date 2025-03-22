<?php

return [
    /*
    |--------------------------------------------------------------------------
    | M-Pesa API Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration for the M-Pesa integration.
    |
    */

    // M-Pesa environment - 'sandbox' for testing, 'production' for live
    'environment' => env('MPESA_ENVIRONMENT', 'sandbox'),

    // Business shortcode (Paybill number or Till number)
    'shortcode' => env('MPESA_SHORTCODE'),

    // M-Pesa API access credentials
    'consumer_key' => env('MPESA_CONSUMER_KEY'),
    'consumer_secret' => env('MPESA_CONSUMER_SECRET'),

    // B2C configuration
    'b2c_shortcode' => env('MPESA_B2C_SHORTCODE'),
    'b2c_initiator' => env('MPESA_B2C_INITIATOR'),
    'security_credential' => env('MPESA_SECURITY_CREDENTIAL'),

    // M-Pesa callback URLs
    'callback_url' => env('MPESA_CALLBACK_URL'),
    'timeout_url' => env('MPESA_TIMEOUT_URL'),
    'result_url' => env('MPESA_RESULT_URL'),

    // Currency exchange rate - USD to KES
    'exchange_rate' => (float) env('MPESA_EXCHANGE_RATE', 140.00),

    // API endpoints
    'api' => [
        'sandbox' => [
            'auth' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'b2c_payment' => 'https://sandbox.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'transaction_status' => 'https://sandbox.safaricom.co.ke/mpesa/transactionstatus/v1/query',
            'account_balance' => 'https://sandbox.safaricom.co.ke/mpesa/accountbalance/v1/query',
        ],
        'production' => [
            'auth' => 'https://api.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'stk_push' => 'https://api.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'b2c_payment' => 'https://api.safaricom.co.ke/mpesa/b2c/v1/paymentrequest',
            'transaction_status' => 'https://api.safaricom.co.ke/mpesa/transactionstatus/v1/query',
            'account_balance' => 'https://api.safaricom.co.ke/mpesa/accountbalance/v1/query',
        ],
    ],
    
    // Transaction types
    'transaction_types' => [
        'b2c_payment' => 'BusinessPayment', // Sending money to customer (Withdrawal)
        'salary_payment' => 'SalaryPayment', // Paying salary
        'promotion_payment' => 'PromotionPayment', // Business promotion
    ],
];