<?php

namespace App\Controllers;

class BillingWebhooksController extends Controller
{
    public function handle()
    {
        $event = billing()->webhook();

        /**
         * $event->id() - the provider's unique event id (store it to skip redelivered events)
         * $event->type() - to get the event type
         * $event->is() - to check if the event is a specific type
         * $event->subscription() - to get the current subscription (if available)
         * $event->user() - to get the current user (if available)
         * $event->activateSubscription() - to activate the new subscription
         * $event->renewSubscription() - to extend the subscription after a successful renewal charge
         * $event->markSubscriptionPastDue() - to flag the subscription when a renewal charge fails
         * $event->cancelSubscription() - to cancel the subscription
         */

        if ($event->is('charge.success')) {
            // Payment was successful and the Checkout Session is complete
            // ✅ Give access to your service
            // $event->user() will give you the user who made the payment (if available)
            return;
        }

        if ($event->is('subscription.create')) {
            // Paystack created the subscription after the first charge
            $event->activateSubscription();

            return;
        }

        if ($event->is('invoice.update')) {
            // A renewal charge was attempted
            if (($event->data()['object']['status'] ?? null) === 'success') {
                // Renewal succeeded: push end_date a period forward
                $event->renewSubscription();
            }

            return;
        }

        if ($event->is('invoice.payment_failed')) {
            // Renewal charge failed: user enters dunning until Paystack retries
            $event->markSubscriptionPastDue();

            // 📧 Maybe email the user to update their card?
            // billing()->portal() gives them a link to do exactly that
            return;
        }

        if ($event->is('subscription.not_renew')) {
            // Subscription was disabled (cancelled at period end).
            // The user keeps access until the current period runs out.
            $event->cancelSubscription();

            return;
        }

        if ($event->is('subscription.disable')) {
            // Subscription has fully expired or was force-disabled
            $event->cancelSubscription(false);

            return;
        }

        // ... handle all other necessary events
    }
}
