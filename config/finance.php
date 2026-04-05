<?php

return [

    'plaid' => [
        'client_id'    => env('PLAID_CLIENT_ID'),
        'secret'       => env('PLAID_SECRET'),
        'access_token' => env('PLAID_ACCESS_TOKEN'), // permanent token from ExchangePublicToken
        'environment'  => env('PLAID_ENVIRONMENT', 'sandbox'), // sandbox | development | production
        'products'     => ['transactions'],
        'country_codes' => ['US'],
        'webhook'      => env('PLAID_WEBHOOK_URL'),
    ],

    'privacy' => [
        // Run a local model before sending data to any cloud provider.
        // Set driver to 'passthrough' to skip (trust your cloud provider).
        'driver'   => env('FINANCE_PRIVACY_DRIVER', 'ollama'), // ollama | passthrough
        'endpoint' => env('FINANCE_PRIVACY_ENDPOINT', 'http://localhost:11434'),
        'model'    => env('FINANCE_PRIVACY_MODEL', 'llama3.2'),

        // What gets anonymized before leaving the local machine
        'redact' => [
            'merchant_names' => true,  // "Whole Foods" → "Grocery Store"
            'exact_amounts'  => true,  // $2,847.32 → ~$2,800
            'account_ids'    => true,
        ],
    ],

    'storage' => [
        // Where real (unredacted) transaction data is stored locally
        'driver'  => env('FINANCE_STORAGE_DRIVER', 'database'),
        'encrypt' => env('FINANCE_STORAGE_ENCRYPT', true),
    ],

];
