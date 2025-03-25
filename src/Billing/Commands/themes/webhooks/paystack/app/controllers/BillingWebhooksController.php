<?php

namespace App\Controllers;

class BillingWebhooksController extends Controller
{
    public function handle()
    {
        $event = billing()->webhook();

        if ($event->is('charge.success')) {
            // Payment was successful and the Checkout Session is complete
            // ✅ Give access to your service
            // $event->user() will give you the user who made the payment (if available)
            return;
        }

        // ... handle all other necessary events
    }
}
