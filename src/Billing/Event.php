<?php

namespace Leaf\Billing;

/**
 * Event
 * -----------
 * Leaf Billing Event class
 */
class Event
{
    protected $event;

    public function __construct($event)
    {
        $this->event = $event;
    }

    /**
     * Get the event type
     * @return string
     */
    public function type(): string
    {
        return $this->event['type'];
    }

    /**
     * Get the event data
     * @return mixed
     */
    public function data()
    {
        return $this->event['data'];
    }

    /**
     * Check if the event is of a specific type
     * @param string $type The type to check
     * @return bool
     */
    public function is(string $type): bool
    {
        return $this->type() === $type;
    }

    /**
     * Get user tied to the event
     * @return \Leaf\Auth\User|null
     */
    public function user()
    {
        if (
            ($customerId = $this->event['data']['object']['customer'] ?? null)
            && $user = db()
                ->select(auth()->config('db.table'))
                ->where('billing_id', $customerId)
                ->first()
        ) {
            return auth()->find($user[auth()->config('id.key') ?? 'id']);
        }

        if (isset($this->event['data']['object']['metadata']['user_id'])) {
            return auth()->find($this->event['data']['object']['metadata']['user_id']);
        }

        return null;
    }

    /**
     * Get subscription tied to the event
     * @return array|null
     */
    public function subscription()
    {
        if ($subscriptionId = $this->event['data']['object']['id'] ?? null) {
            return $this->user() ? $this->user()->subscriptions()->where('subscription_id', $subscriptionId)->first() : null;
        }

        return null;
    }

    /**
     * Get subscription tier tied to the event
     * @return array|null
     */
    public function tier(): ?array
    {
        if ($subscription = $this->event['data']['object']['items']['data'][0]['plan'] ?? null) {
            return billing()->tier($subscription['id']);
        }

        if ($subscription = $this->subscription()) {
            return billing()->tier($subscription['plan_id']);
        }

        return null;
    }

    /**
     * Get payment associated with the event
     * @return Session|null
     */
    public function session(): ?Session
    {
        $sessionId = $this->event['data']['object']['metadata']['session_id'] ?? null;

        return $sessionId ? billing()->session($sessionId) : null;
    }

    /**
     * Get metadata associated with the event
     * @return array
     */
    public function metadata()
    {
        return $this->event['data']['object']['metadata'] ?? [];
    }

    /**
     * Get previous subscription if available
     * @return array|null
     */
    public function previousSubscriptionTier()
    {
        if ($plan = $this->event['data']['previous_attributes']['items']['data'][0]['plan'] ?? null) {
            return billing()->tier($plan['id']);
        }

        return null;
    }

    /**
     * Activate new subscription if available
     * @return bool
     */
    public function activateSubscription(): bool
    {
        if ($subscription = $this->subscription()) {
            db()
                ->update('subscriptions')
                ->params([
                    'status' => Subscription::STATUS_ACTIVE,
                    'plan_id' => $this->tier()['id'] ?? null,
                    'trial_ends_at' => null,
                ])
                ->where('id', $subscription['id'])
                ->execute();

            return true;
        }

        return false;
    }

    /**
     * Cancel subscription in webhook (if available)
     * @return bool
     */
    public function cancelSubscription(): bool
    {
        if ($subscription = $this->subscription()) {
            db()
                ->update('subscriptions')
                ->params([
                    'status' => Subscription::STATUS_CANCELLED,
                    'end_date' => tick()->format('Y-m-d H:i:s'),
                ])
                ->where('id', $subscription['id'])
                ->execute();

            return true;
        }

        return false;
    }
}
