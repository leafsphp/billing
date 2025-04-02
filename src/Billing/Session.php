<?php

namespace Leaf\Billing;

/**
 * Session
 * -----------
 * Session class for Leaf Billing
 */
class Session
{
    protected $providerSession;

    public function __construct($paymentSession)
    {
        $this->providerSession = $paymentSession;
    }

    /**
     * Return current session id
     * @return string
     */
    public function id(): string
    {
        return $this->providerSession->id;
    }

    /**
     * The ID of the subscription for Checkout Sessions
     * @return string
     */
    public function subscriptionId(): string
    {
        return $this->providerSession->subscription;
    }

    /**
     * Return current provider session object
     * @return object
     */
    public function session(): object
    {
        return $this->providerSession;
    }

    /**
     * Link to payment page
     * @return string
     */
    public function url(): string
    {
        return $this->providerSession->url;
    }

    /**
     * Link to payment page
     * @return string
     */
    public function link(): string
    {
        return $this->url();
    }

    /**
     * Check the payment status of the current session
     * @return string
     */
    public function paymentStatus(): string
    {
        return $this->providerSession->payment_status;
    }

    /**
     * Get the session currency
     * @return string
     */
    public function currency(): string
    {
        return $this->providerSession->currency;
    }

    /**
     * Get all supported payment methods
     * @return array
     */
    public function paymentMethods(): array
    {
        return $this->providerSession->payment_method_types;
    }

    /**
     * Status of session (paid, pending, expired)
     * @return string
     */
    public function status(): string
    {
        return $this->providerSession->status === 'open'
            ? 'pending'
            : ($this->providerSession->status === 'completed' ? 'paid' : 'expired');
    }

    /**
     * Check if the payment was successful
     * @return bool
     */
    public function isPaid(): bool
    {
        return $this->paymentStatus() === 'paid';
    }

    /**
     * Check if the payment was successful
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->isPaid();
    }

    /**
     * Check if the session has expired
     * @return bool
     */
    public function isExpired(): bool
    {
        return $this->status() === 'expired';
    }

    /**
     * Get when the session will expire
     * @return string
     */
    public function expiresAt(): string
    {
        return $this->providerSession->expires_at;
    }

    /**
     * Get the current session urls
     * @return object
     */
    public function urls(): object
    {
        return (object) [
            'success' => $this->providerSession->success_url,
            'cancel' => $this->providerSession->cancel_url,
        ];
    }

    /**
     * Updates a Session object.
     *
     * @param string $id the ID of the resource to update
     * @param null|array $params
     * @param null|array|string $opts
     *
     * @throws \Exception if the request fails
     *
     * @return Session the updated resource
     */
    public function update(?array $params = null, $opts = null): Session
    {
        return new static($this->providerSession->update($this->id(), $params, $opts));
    }

    /**
     * Get all metadata for the session
     * @return array
     */
    public function metadata(): array
    {
        return (array) $this->providerSession->metadata;
    }

    /**
     * Check if the current session is for a subscription
     * @return bool
     */
    public function isSubscription(): bool
    {
        return $this->providerSession->subscription !== null;
    }

    /**
     * Activate a subscription/trial
     * @return bool
     */
    public function activateSubscription(): bool
    {
        if (!$this->isSubscription()) {
            return false;
        }

        $currentSubscription = db()
            ->select('subscriptions')
            ->where('payment_session_id', $this->id())
            ->first();

        if (!$currentSubscription) {
            return false;
        }

        db()
            ->update('subscriptions')
            ->params([
                'status' => $currentSubscription['trial_ends_at'] ? Subscription::STATUS_TRIAL : Subscription::STATUS_ACTIVE,
                'subscription_id' => $this->subscriptionId(),
            ])
            ->where('payment_session_id', $this->id())
            ->execute();

        return true;
    }

    public function __call($method, $args)
    {
        return $this->providerSession->$method(...$args);
    }

    public function __get($property)
    {
        return $this->providerSession->$property;
    }

    public function __set($property, $value)
    {
        $this->providerSession->$property = $value;
    }

    public function __isset($property)
    {
        return isset($this->providerSession->$property);
    }
}
