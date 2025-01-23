<?php

if (!function_exists('billing')) {
    function billing($settings = [])
    {
        if (!(\Leaf\Config::getStatic('billing'))) {
            \Leaf\Config::singleton('billing', function () use ($settings) {
                $className = ucfirst($settings['provider'] ?? 'Stripe');
                $provider = "Leaf\\Billing\\$className";

                return new $provider($settings);
            });
        }

        return \Leaf\Config::get('billing');
    }
}
