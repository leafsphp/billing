<?php

namespace App\Controllers\Billing;

/**
 * Billing Callback
 * ----
 * This controller processes the billing provider’s callback after a payment.
 * Since it's usually stateful, it can use sessions, authentication, and other stateful data.
 * It runs when the user is redirected back to your app after completing payment.
 */
class CallbacksController extends Controller
{
    public function handle()
    {
        $billingSession = billing()->callback();

        if (!$billingSession->isSuccessful()) {
            // replace the line below with your own handler :-)
            return response()->json([
                'message' => 'Payment failed',
            ]);
        }

        // tie provider customer id to user for future reference
        billing()->updateCustomer($billingSession->customer);

        // activate the subscription/trial mode tied to the billing session
        $billingSession->activateSubscription();

        // ...

        return response()->redirect('/dashboard');
    }
}
