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
     * Create subscription tiers on billing provider
     * @return bool
     */
    public function initialize(): bool;

    /**
     * Retrieve stripe customer for current user
     * @return \Stripe\Customer|null
     */
    public function customer(): ?\Stripe\Customer;

    /**
     * Update user with provider customer id
     * @param string $customerId
     * @return void
     */
    public function updateCustomer(string $customerId): void;

    /**
     * Create stripe customer and update user
     * @param array|null $data The data for customer
     * @return bool
     */
    public function createCustomer(?array $data = null): bool;

    /**
     * Create a checkout session for an instant payment
     * @param array $data
     * @return Session
     */
    public function charge(array $data): Session;

    /**
     * Create a checkout session for a subscription
     * @param array $data The data for the subscription [name/id required]
     * @return Session
     */
    public function subscribe(array $data): Session;

    /**
     * Switch user to a different subscription tier
     * @param array $data The data for new subscription [name/id required]
     * @return bool
     */
    public function changeSubcription(array $data): bool;

    /**
     * Cancel a subscription using its id
     * @param string $id The subscription id
     * @return bool
     */
    public function cancelSubscription(string $id): bool;

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
     * @param string|null $billingPeriod The period to get tiers for (will return all tiers if null)
     * @return array
     */
    public function tiers(?string $billingPeriod = null): array;

    /**
     * Get a tier by it's id
     * @param string $id The billing tier id
     * @return array|null
     */
    public function tier(string $id): ?array;

    /**
     * Get billing periods as defined in the billing config
     * @return array
     */
    public function periods(): array;

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
