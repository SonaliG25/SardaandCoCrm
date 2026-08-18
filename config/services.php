<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

       /*
    |--------------------------------------------------------------------------
    | WooCommerce API Configuration
    |--------------------------------------------------------------------------
    */
    'woocommerce' => [
        'url' => env('WOOCOMMERCE_URL'),
        'consumer_key' => env('WOOCOMMERCE_CONSUMER_KEY'),
        'consumer_secret' => env('WOOCOMMERCE_CONSUMER_SECRET'),
        'webhook_secret' => env('WOOCOMMERCE_WEBHOOK_SECRET'),
        'version' => env('WOOCOMMERCE_API_VERSION', 'wc/v3'),
        'timeout' => env('WOOCOMMERCE_TIMEOUT', 30),
    ],

    /*
    |--------------------------------------------------------------------------
    | Shipping Partners API Configuration
    |--------------------------------------------------------------------------
    */
 'delhivery' => [
    // Use only api_token
    'api_token' => env('DELHIVERY_API_TOKEN'),
    'client_id' => env('DELHIVERY_CLIENT_ID'),
    
    // Rest remains same...
    'api_base_url' => env('DELHIVERY_MODE', 'staging') === 'production' 
        ? env('DELHIVERY_API_BASE_URL', 'https://track.delhivery.com/api/') 
        : env('DELHIVERY_STAGING_URL', 'https://staging-express.delhivery.com/api/'),
    'mode' => env('DELHIVERY_MODE', 'staging'),
    'tracking_url' => env('DELHIVERY_TRACKING_URL', 'https://www.delhivery.com/track/package/'),
    'timeout' => 30,
    
    'business' => [
        'name' => env('DELHIVERY_BUSINESS_NAME', 'Sarda & Co'),
        'address' => env('DELHIVERY_BUSINESS_ADDRESS', 'Your warehouse address'),
        'city' => env('DELHIVERY_BUSINESS_CITY', 'Mumbai'),
        'state' => env('DELHIVERY_BUSINESS_STATE', 'Maharashtra'),
        'pincode' => env('DELHIVERY_BUSINESS_PINCODE', '400001'),
        'phone' => env('DELHIVERY_BUSINESS_PHONE', '9876543210'),
        'gst' => env('DELHIVERY_BUSINESS_GST', ''),
    ],
],
'razorpay' => [
    'key_id' => env('RAZORPAY_KEY_ID'),
    'key_secret' => env('RAZORPAY_KEY_SECRET'),
    'webhook_secret' => env('RAZORPAY_WEBHOOK_SECRET'),
],
];
