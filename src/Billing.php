<?php

namespace Leaf;

/**
 * Leaf Billing
 * -----------
 * Unified billing system for Leaf. Works with Stripe, Paystack and LemonSqueezy.
 * More providers coming soon.
 */
trait Billing
{
    /**
     * Billing configuration
     */
    public $config = [
        'mode' => 'purchase',
        'currency.name' => 'usd',
        'currency.symbol' => '$',
    ];

    /**
     * Billing provider
     */
    public $provider;

    public function __construct($billingSettings = [])
    {
        $this->initProvider($billingSettings);
    }

    /**
     * Get/Set billing configuration
     * @param string|array $key Config key or array of config
     */
    public function config($key = null)
    {
        if (is_array($key)) {
            $this->config = array_merge($this->config, $key);
            return $this;
        }

        return $key ? $this->config[$key] : $this->config;
    }

    /**
     * Get all tiers
     */
    public function tiers($type = 'monthly')
    {
        return array_filter($this->tiers, function ($tier) use ($type) {
            return $tier['type'] === $type;
        });
    }

    /**
     * Get a tier
     */
    public function tier($id)
    {
        return $this->tiers[$id];
    }

    /**
     * Return current billing provider interface
     */
    public function provider()
    {
        return $this->provider;
    }

    /**
     * Get all populated tiers
     *
     */
    public function groups()
    {
        $populatedTiers = [];

        foreach ($this->tiers as $tier) {
            $populatedTiers[] = $tier['type'];
        }

        return array_unique($populatedTiers);
    }

    /**
     * Get groups with their tiers
     */
    public function groupsWithTiers()
    {
        $groups = [];

        foreach ($this->groups() as $group) {
            $groups[$group] = $this->tiers($group);
        }

        return $groups;
    }
}
