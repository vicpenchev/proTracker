<?php

return [
    'currency_converter' => [
        'default' => env('CURRENCY_CONVERTER_API', 'exchange_rate_api'),
    ],

    'exchange_rate_api' => [
        'url' => env('EXCHANGE_RATE_API_URL', 'https://v6.exchangerate-api.com/v6/'),
        'api_key' => env('EXCHANGE_RATE_API_KEY', ''),
        'base_currency' => env('EXCHANGE_RATE_BASE_CURRENCY', 'EUR'),
    ],
];
