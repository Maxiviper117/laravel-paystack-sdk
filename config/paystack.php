<?php

use Maxiviper117\Paystack\Support\Webhooks\PaystackWebhookIpAllowlist;

return [
    'secret_key' => env('PAYSTACK_SECRET_KEY'),
    'public_key' => env('PAYSTACK_PUBLIC_KEY'),
    'base_url' => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
    'timeout' => (int) env('PAYSTACK_TIMEOUT', 30),
    'connect_timeout' => (int) env('PAYSTACK_CONNECT_TIMEOUT', 10),
    'retry_times' => (int) env('PAYSTACK_RETRY_TIMES', 2),
    'retry_sleep_ms' => (int) env('PAYSTACK_RETRY_SLEEP_MS', 250),
    'throw_on_api_error' => env('PAYSTACK_THROW_ON_API_ERROR', true),
    'webhooks' => [
        'config_name' => 'paystack',
        'signing_secret' => env('PAYSTACK_SECRET_KEY'),
        'signature_header_name' => 'x-paystack-signature',
        'allowed_ips' => PaystackWebhookIpAllowlist::fromConfig(env('PAYSTACK_WEBHOOK_ALLOWED_IPS')),
        'store_headers' => ['x-paystack-signature', 'content-type', 'user-agent'],
        'delete_after_days' => (int) env('PAYSTACK_WEBHOOK_DELETE_AFTER_DAYS', 30),
        'queue' => env('PAYSTACK_WEBHOOK_QUEUE'),
        'connection' => env('PAYSTACK_WEBHOOK_CONNECTION'),
    ],
];
