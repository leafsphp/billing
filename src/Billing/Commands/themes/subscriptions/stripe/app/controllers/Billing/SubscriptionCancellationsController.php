<?php

namespace App\Controllers\Billing;

/**
 * Subscription Cancellations
 * ------------
 * This controller manages user subscription cancellations
 * by triggering the cancellation on the billing provider.
 * It's an asynchronous process handled by the webhooks controller,
 * so the user retains access to the service until the cancellation is fully processed.
 */
class SubscriptionCancellationsController extends Controller
{
    public function handle(string $tierId)
    {
        if (auth()->user()->cancelSubscription()) {
            // subscription cancellation has been initiated
            // you can redirect to a page or show a message
            // indicating that the subscription has been cancelled
        }

        return response()->redirect('/dashboard');
    }
}
