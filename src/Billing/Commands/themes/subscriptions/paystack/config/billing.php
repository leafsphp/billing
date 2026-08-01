<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default payment provider
    |--------------------------------------------------------------------------
    |
    | Here you may specify which of the provider connections below you wish
    | to use as your default connection for all payment processing. You
    | may use many connections at once using the Database library.
    |
    */

    'default' => _env('BILLING_PROVIDER', 'paystack'),

    /*
    |--------------------------------------------------------------------------
    | Provider Connections
    |--------------------------------------------------------------------------
    |
    | Here are each of the provider connections setup for your application.
    | We have provided examples of configuring each provider platform
    | that is supported by Leaf's billing module.
    |
    */

    'connections' => [
        'stripe' => [
            'driver' => 'stripe',
            'secrets.apiKey' => _env('STRIPE_API_KEY'),
            'secrets.publishableKey' => _env('STRIPE_PUBLISHABLE_KEY'),
            'secrets.webhook' => _env('STRIPE_WEBHOOK_SECRET'),
            'version' => _env('STRIPE_API_VERSION', '2023-10-16'),
            'currency' => [
                'name' => _env('STRIPE_CURRENCY', _env('BILLING_CURRENCY', 'usd')),
                'symbol' => _env('STRIPE_CURRENCY_SYMBOL', _env('BILLING_CURRENCY_SYMBOL', '$')),
                'locale' => _env('STRIPE_CURRENCY_LOCALE', _env('BILLING_CURRENCY_LOCALE', 'en_US')),

                // show prices in a different currency from the one you
                // charge in. Leave unset to display the charge currency.
                'display' => _env('BILLING_CURRENCY_DISPLAY', null),
                'displaySymbol' => _env('BILLING_CURRENCY_DISPLAY_SYMBOL', null),
                'conversion' => _env('BILLING_CURRENCY_DISPLAY_CONVERSION', null),
            ],
        ],

        'paystack' => [
            'driver' => 'paystack',
            'secrets.publicKey' => _env('PAYSTACK_PUBLIC_KEY'),
            'secrets.secretKey' => _env('PAYSTACK_SECRET_KEY'),
            'secrets.webhook' => _env('PAYSTACK_WEBHOOK_SECRET'),
            'version' => _env('PAYSTACK_API_VERSION', null),
            'currency' => [
                'name' => _env('PAYSTACK_CURRENCY', _env('BILLING_CURRENCY', 'ngn')),
                'symbol' => _env('PAYSTACK_CURRENCY_SYMBOL', _env('BILLING_CURRENCY_SYMBOL', '₦')),
                'locale' => _env('PAYSTACK_CURRENCY_LOCALE', _env('BILLING_CURRENCY_LOCALE', 'en_US')),

                // show prices in a different currency from the one you
                // charge in. Leave unset to display the charge currency.
                'display' => _env('BILLING_CURRENCY_DISPLAY', null),
                'displaySymbol' => _env('BILLING_CURRENCY_DISPLAY_SYMBOL', null),
                'conversion' => _env('BILLING_CURRENCY_DISPLAY_CONVERSION', null),
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing URLs
    |--------------------------------------------------------------------------
    |
    | Here you may specify the URLs to redirect to after a successful or
    | cancelled payment. You may use these URLs to show a success or
    | cancelled message to the user.
    |
    */

    'urls' => [
        'success' => _env('BILLING_SUCCESS_URL', '/billing/callback'),
        'cancel' => _env('BILLING_CANCEL_URL', '/billing/callback'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Billing Tiers
    |--------------------------------------------------------------------------
    |
    | Here you may specify the billing tiers available for your application.
    | You may specify as many tiers as you want, each with its own
    | features and pricing details.
    |
    */

    'tiers' => [
        [
            'name' => 'Starter',
            'description' => 'For individuals and small teams',
            'trialDays' => 5,
            'price.monthly' => 10,
            'price.yearly' => 100,
            'discount' => 0,
            'features' => [
                [
                    'title' => 'Something 1',
                    'description' =>
                        'Expertly crafted functionality including auth, mailing, billing, blogs, e-commerce, dashboards, and more.',
                ],
                [
                    'title' => 'Another thing 1',
                    'description' =>
                        'Beautiful templates and page sections built with Blade, Alpine.js, and Tailwind CSS to skip the boilerplate and build faster.',
                ],
                [
                    'title' => 'Something else 1',
                    'description' =>
                        'Get instant access to everything we have today, plus any new functionality and Leaf Zero templates we add in the future.',
                ],
            ],
        ],

        // As many tiers as you want
    ],
];
