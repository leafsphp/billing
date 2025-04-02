<?php

namespace App\Controllers\Billing;

class CallbacksController extends Controller
{
    public function handle()
    {
        $billingSession = billing()->callback();

        if (!$billingSession->isSuccessful()) {
            // Handle the error
            // Log the error or notify the user
            // replace the line below with your own handler :-)
            return response()->json([
                'message' => 'Payment failed',
            ]);
        }

        // tie provider customer id to user for future reference
        billing()->updateCustomer($billingSession->customer);

        // activate the subscription/trial mode tied to the billing session
        $billingSession->activateSubscription();

        // Perform any additional actions after the payment is successful
        // $billingSession->user() will give you the user who made the payment (if available)

        return response()->redirect('/');
    }
}
