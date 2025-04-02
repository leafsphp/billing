<?php

namespace App\Controllers\Billing;

class TierSubscriptionsController extends Controller
{
    public function handle(string $tierId)
    {
        $session = billing()->subscribe([
            'id' => $tierId,
            // // you can override any other config you need
            // 'urls' => [
            //     'success' => '/some-route',
            //     'expired' => '/some-route',
            // ],
            // // user + tier data is already passed in metadata so no need to pass it again
            // // but you can pass other data you need
            // 'metadata' => [
            //     'some' => 'data'
            // ]
        ]);

        response()->redirect(
            $session->url()
        );
    }
}
