<?php

if (!function_exists('billing')) {
    /**
     * Get instance of billing provider
     * @param array|string|null $settings Billing settings or provider name
     * @return \Leaf\Billing\BillingProvider
     */
    function billing($settings = null)
    {
        if (!(\Leaf\Config::getStatic('billing'))) {
            \Leaf\Config::singleton('billing', function () use ($settings) {
                return new \Leaf\Billing($settings ?? []);
            });
        }

        return \Leaf\Config::get('billing')->getDriver(
            is_array($settings) ? null : $settings
        );
    }
}
