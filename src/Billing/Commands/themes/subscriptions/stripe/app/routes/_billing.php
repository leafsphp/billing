<?php

app()->group('/billing', [
    'middleware' => 'auth.required',
    'namespace' => 'App\Controllers\Billing',
    function () {
        app()->get('/payments/{id}', 'TierSubscriptionsController@handle');
        app()->get('/callback', 'CallbacksController@handle');
        app()->post('/webhook', 'WebhooksController@handle');
    }
]);
