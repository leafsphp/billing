<?php

namespace App\Controllers\Billing;

/**
 * Tier Subscriptions
 * ------------
 * This controller checks if the user has an active subscription.
 * If they do, it updates the subscription on the billing provider.
 * If not, it creates a new subscription and redirects the user to the billing provider’s checkout page.
 */
class TierSubscriptionsController extends Controller
{
    public function handle(string $tierId)
    {
        if (auth()->user()->hasActiveSubscription()) {
            billing()->changeSubscription([
                'id' => $tierId,
            ]);

            return response()->redirect('/dashboard');
        }

        $session = billing()->subscribe([
            'id' => $tierId,
        ]);

        return response()->redirect(
            $session->url()
        );
    }
}
