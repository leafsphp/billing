<!-- markdownlint-disable no-inline-html -->
<p align="center">
  <br><br>
  <img src="https://leafphp.dev/logo-circle.png" height="100"/>
  <br>
</p>

<h1 align="center">Leaf Billing (Beta)</h1>

<p align="center">
  <a href="https://packagist.org/packages/leafs/billing"
    ><img
      src="https://poser.pugx.org/leafs/billing/v/stable"
      alt="Latest Stable Version"
  /></a>
  <a href="https://packagist.org/packages/leafs/billing"
    ><img
      src="https://poser.pugx.org/leafs/billing/downloads"
      alt="Total Downloads"
  /></a>
  <a href="https://packagist.org/packages/leafs/billing"
    ><img
      src="https://poser.pugx.org/leafs/billing/license"
      alt="License"
  /></a>
</p>
<br />
<br />

Leaf's billing system helps makers move faster by handling payments and subscriptions out of the box. With built-in Stripe support—and more providers like Paystack coming soon—you can set up one-time payments or recurring subscriptions in just a few minutes. That means less time worrying about billing and more time building.

## Setting up

To get started, create a Stripe account and grab your API keys. Then, drop them into your `.env` file:

```env [Stripe]
BILLING_PROVIDER=stripe
STRIPE_API_KEY=sk_test_XXXX
STRIPE_PUBLISHABLE_KEY=pk_test_XXXX
STRIPE_WEBHOOK_SECRET=whsec_XXXX # only if you are using webhooks
```

You then have to install the Stripe module for Leaf:

```bash
leaf install stripe
```

## Billing on-the-fly

Billing on-the-fly is the fastest way to charge customers—ideal for one-time payments, donations, or services. Just generate a payment link with Leaf Billing, and we’ll handle the rest. You can do this using the `billing()` helper in your controller.

```php
...

public function handleCartPurchase($cartId) {
    $cart = Cart::find($cartId);

    $session = billing()->charge([
        'currency' => 'USD',
        'description' => 'Purchase of items in cart',
        'metadata' => [
            'cart_id' => $cartId,
            'items' => $cart->items(),
        ]
    ]);

    $cart->payment_session = $session->id;
    $cart->save();

    response()->redirect($session->url);
}
```

Leaf takes care of the entire payment session for you—automatically tracking the user (if available), any metadata you provide, and the payment status, keeping your code clean and focused on your app.

## Billing Callbacks

By default, Leaf Billing redirects users to `/billing/callback` after a payment is completed or canceled. You can customize this behavior by setting `BILLING_SUCCESS_URL` and `BILLING_CANCEL_URL` in your `.env` file, or by passing custom URLs directly to the `charge()` method.

```php [CallbacksController.php]
<?php

namespace App\Controllers\Billing;

/**
 * Billing Callback
 * ---
 * Handles the redirect from the billing provider after payment.
 * This is a stateful controller, so sessions and auth are available.
 */
class CallbacksController extends Controller
{
    public function handle()
    {
        $billingSession = billing()->callback();

        if (!$billingSession->isSuccessful()) {
            return response()->json(['message' => 'Payment failed']);
        }

        return response()->json(['message' => 'Payment successful']);
    }
}
```

`billing()->callback()` parses and validates the callback, returning a BillingSession with full payment details. Use `isSuccessful()` to determine the outcome. This is ideal for one-time payments—no subscription logic needed.

## Billing with subscriptions

Unlike one-time payments, subscriptions require a more structured setup—but Leaf Billing makes it effortless. Just run the `scaffold:subscriptions` command to instantly generate everything you need: billing config, controllers, routes, and views. You'll be up and running with subscriptions in minutes.

```bash
php leaf scaffold:subscriptions
```

You then need to update the generated `config/billing.php` file with your subscription tiers under the `tiers` key:

```php
...
    'tiers' => [
        [
            'name' => 'Starter',
            'description' => 'For individuals and small teams',
            'trialDays' => 5,
            'price.monthly' => 100,
            'price.yearly' => 1000,
            'discount' => 25,
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
        [
            'name' => 'Pro',
            'description' => 'For larger teams and companies',
            'trialDays' => 10,
            'price.monthly' => 200,
            'price.yearly' => 2000,
            'discount' => 50,
            'features' => [
                [
                    'title' => 'Something 2',
                    'description' =>
                        'Expertly crafted functionality including auth, mailing, billing, blogs, e-commerce, dashboards, and more.',
                ],
                [
                    'title' => 'Another thing 2',
                    'description' =>
                        'Beautiful templates and page sections built with Blade, Alpine.js, and Tailwind CSS to skip the boilerplate and build faster.',
                ],
                [
                    'title' => 'Something else 2',
                    'description' =>
                        'Get instant access to everything we have today, plus any new functionality and Leaf Zero templates we add in the future.',
                ],
            ],
        ],
    ]
];
```

## Displaying your plans

The `scaffold:subscriptions` command also generates a pricing component tailored to your chosen view engine—Blade, React, Vue, or Svelte. You can display your plans with just one line of code. The component is fully customizable, so you can tweak the design to match your app’s look and feel seamlessly.

```blade [Blade]
@component('components.billing.pricing')
```

```jsx [React]
import Pricing from '@/components/billing/pricing';

...

<Pricing />
```

```vue [Vue]
<script setup>
import Pricing from '@/components/billing/pricing.vue';

...
</script>

<template>
  <Pricing />
</template>
```

```svelte [Svelte]
<script>
import Pricing from '@/components/billing/pricing.svelte';
</script>

<Pricing />
```

## Billing Events/Webhooks

Once you’ve charged a customer—especially for a subscription—you’ll want to track their payment status. The best way to do this is through webhooks. When you run the `scaffold:subscriptions` command, Leaf Billing automatically generates a webhook controller that listens for events from your billing provider and handles them for you.

```php [WebhooksController.php]
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
         * $event->type() - to get the event type
         * $event->is() - to check if the event is a specific type
         * $event->tier() - to get the subscription tier (if available)
         * $event->subscription() - to get the current subscription (if available)
         * $event->user() - to get the current user (returns auth()->user() if available)
         * $event->previousSubscriptionTier() - to get the previous subscription tier (if available)
         * $event->cancelSubscription() - to cancel the subscription in webhook request (if available)
         * $event->activateSubscription() - to activate the new subscription in webhook (if available)
         */

        if ($event->is('invoice.payment_succeeded')) {
            // Payment was successful

            if ($event->data()['object']['billing_reason'] === 'subscription_cycle') {
                // Subscription renewed/charged after trial/cycle
                // ✅ Give access to your service
            }

            // Other payment succeeded events
            // ✅ Give access to your service

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
```

Since webhooks are stateless, you can't use the `session()` or `auth()` helpers to retrieve the user who made the payment. This is a common issue with webhooks, as they are designed to be stateless and don't have access to the session or authentication data. However, Leaf Billing automatically parses the webhook payload and provides you with a `BillingEvent` instance, which gives you access to the user who made the payment, the subscription, and all other relevant details.
