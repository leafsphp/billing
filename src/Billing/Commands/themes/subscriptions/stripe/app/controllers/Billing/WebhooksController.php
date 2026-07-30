<?php

namespace App\Controllers\Billing;

/**
 * Webhooks Controller
 * ----------
 * This controller processes all webhooks from the billing provider.
 * Since webhooks are stateless, sessions, authentication, and other
 * stateful data aren't available. However, Leaf automatically parses the webhook payload,
 * giving you direct access to the current user or subscription from the event data.
 */
class WebhooksController extends Controller
{
    public function handle()
    {
        $event = billing()->webhook();

        /**
         * $event->id() - the provider's unique event id (store it to skip redelivered events)
         * $event->type() - to get the event type
         * $event->is() - to check if the event is a specific type
         * $event->tier() - to get the subscription tier (if available)
         * $event->subscription() - to get the current subscription (if available)
         * $event->user() - to get the current user (returns auth()->user() if available)
         * $event->previousSubscriptionTier() - to get the previous subscription tier (if available)
         * $event->activateSubscription() - to activate the new subscription in webhook (if available)
         * $event->renewSubscription() - to extend the subscription after a successful renewal payment
         * $event->markSubscriptionPastDue() - to flag the subscription when a renewal payment fails
         * $event->cancelSubscription() - to cancel the subscription in webhook request (if available)
         */

        if ($event->is('invoice.payment_succeeded')) {
            // Payment was successful

            if ($event->data()['object']['billing_reason'] === 'subscription_cycle') {
                // Subscription renewed: push end_date a period forward and
                // clear any past_due state from failed earlier attempts
                $event->renewSubscription();
            }

            // Other payment succeeded events
            // ✅ Give access to your service

            return;
        }

        if ($event->is('invoice.payment_failed')) {
            // Renewal payment failed: user enters dunning. Stripe retries the
            // charge; invoice.payment_succeeded will clear this when it recovers
            $event->markSubscriptionPastDue();

            // 📧 Maybe email the user to update their card?
            // billing()->portal() gives them a link to do exactly that

            return;
        }

        if ($event->is('customer.subscription.updated')) {
            if ($event->activateSubscription()) {
                response()->json([
                    'status' => 'success',
                ]);
            } else {
                // Subscription was not activated
                // ❌ Retry or handle manually
                response()->json([
                    'status' => 'failed',
                ], 500);
            }

            return;
        }

        if ($event->is('customer.subscription.deleted')) {
            if ($event->cancelSubscription()) {
                response()->json([
                    'status' => 'success',
                ]);
            } else {
                // Subscription was not cancelled
                // ❌ Retry or handle manually
                response()->json([
                    'status' => 'failed',
                ], 500);
            }

            return;
        }

        if ($event->is('customer.subscription.trial_will_end')) {
            // Trial will end soon
            // 📧 Maybe send a trial ending mail?
            return;
        }

        if ($event->is('customer.subscription.paused')) {
            // Subscription was paused
            // ❌ Remove access to your service
            return;
        }

        if ($event->is('customer.subscription.resumed')) {
            // Subscription was resumed
            // ✅ Give access to your service
            return;
        }

        // ... handle all other necessary events
    }
}
// You can also use the event data to perform other actions, such as sending emails, updating your database, etc.
// You can also use the event data to perform other actions, such as sending emails, updating your database, etc.
