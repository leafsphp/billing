<?php

namespace Leaf\Billing;

/**
 * Leaf Billing
 * -----------
 * Unified billing system for Leaf. Works with Stripe and Paystack,
 * and more providers coming soon.
 */
interface BillingProvider
{
    /**
     * Create a checkout session for an instant payment
     * @param array $data
     * @return Session
     */
    public function charge(array $data): Session;

    /**
     * Create a checkout session for a subscription
     * @param array $data
     * @return Session
     */
    public function subscribe(array $data): Session;

    /**
     * Get a subscription by ID
     * @param string $id
     * @return Subscription|null
     */
    public function subscription(string $id): ?Subscription;

    /**
     * Get all subscriptions
     * @return array
     */
    public function subscriptions(): array;

    /**
     * Get a session by ID
     * @param string $id
     * @return Session|null
     */
    public function session(string $id): ?Session;

    /**
     * Parse a webhook event
     * @return Event|null
     */
    public function webhook(): ?Event;

    /**
     * Parse a session from callback
     * @return Session|null
     */
    public function callback(): ?Session;

    /**
     * Get tiers set in the billing config
     * @return array
     */
    public function tiers(): array;

    /**
     * Get billing periods as defined in the billing config
     * @return array
     */
    public function periods(): array;

    /**
     * Get tiers ordered by billing period
     * @param string|null $period The period to get tiers for (will return all tiers if null)
     * @return array
     */
    public function tiersByPeriod($period = null): array;

    /**
     * Get the provider name
     * @return string
     */
    public function providerName(): string;

    /**
     * Return current billing provider interface
     * @return mixed
     */
    public function provider();

    /**
     * Get all errors from the last operation
     * @return array
     */
    public function errors(): array;
}
