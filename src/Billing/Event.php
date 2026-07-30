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
        // webhooks are stateless, so the subscription is resolved straight
        // from the subscriptions table without needing an auth context
        if ($subscriptionId = $this->event['data']['object']['id'] ?? null) {
            if ($subscription = db()->select('subscriptions')->where('subscription_id', $subscriptionId)->first()) {
                return $subscription;
            }
        }

        if ($subscriptionCode = $this->event['data']['object']['subscription_code'] ?? null) {
            if ($subscription = db()->select('subscriptions')->where('subscription_id', $subscriptionCode)->first()) {
                return $subscription;
            }
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
     * Get the provider's unique id for this event.
     * Store handled ids to make your webhook handler idempotent —
     * providers redeliver events, so the same event can arrive twice.
     *
     * @return string|null
     */
    public function id(): ?string
    {
        return $this->event['id'] ?? null;
    }

    /**
     * Get the event creation timestamp
     * @return int|null
     */
    public function createdAt(): ?int
    {
        return $this->event['created'] ?? null;
    }

    /**
     * Activate new subscription if available
     * @return bool
     */
    public function activateSubscription(): bool
    {
        if ($subscription = $this->subscription()) {
            // resolved before the update chain starts: tier() queries through
            // the same db instance and would clobber a half-built query
            $planId = $this->tier()['id'] ?? $subscription['plan_id'];

            db()
                ->update('subscriptions')
                ->params([
                    'status' => Subscription::STATUS_ACTIVE,
                    'plan_id' => $planId,
                    'trial_ends_at' => null,
                ])
                ->where('id', $subscription['id'])
                ->execute();

            return true;
        }

        return false;
    }

    /**
     * Renew the subscription tied to this event.
     * Call this on a successful renewal payment: it pushes end_date one
     * billing period forward and clears past_due/trial back to active.
     *
     * @return bool
     */
    public function renewSubscription(): bool
    {
        if (!($subscription = $this->subscription())) {
            return false;
        }

        $tier = billing()->tier($subscription['plan_id']);
        $period = rtrim($tier['billingPeriod'] ?? 'monthly', 'ly');
        $currentEnd = $subscription['end_date'] ?? null;

        // renew from the current period end when it's still in the future,
        // otherwise from now (e.g. recovering from past_due)
        $renewFrom = ($currentEnd && strtotime($currentEnd) > time())
            ? tick($currentEnd)
            : tick();

        db()
            ->update('subscriptions')
            ->params([
                'status' => Subscription::STATUS_ACTIVE,
                'trial_ends_at' => null,
                'end_date' => $renewFrom->add(1, $period)->format('YYYY-MM-DD HH:mm:ss'),
            ])
            ->where('id', $subscription['id'])
            ->execute();

        return true;
    }

    /**
     * Mark the subscription tied to this event as past due.
     * Call this when a renewal payment fails: the user enters dunning and
     * hasActiveSubscription() returns false until payment recovers.
     *
     * @return bool
     */
    public function markSubscriptionPastDue(): bool
    {
        if (!($subscription = $this->subscription())) {
            return false;
        }

        db()
            ->update('subscriptions')
            ->params([
                'status' => Subscription::STATUS_PAST_DUE,
            ])
            ->where('id', $subscription['id'])
            ->execute();

        return true;
    }

    /**
     * Cancel subscription in webhook (if available)
     *
     * The subscription keeps its current end_date when it is in the future,
     * so a user who cancelled at period end keeps access until then (grace
     * period). Pass false to revoke access immediately.
     *
     * @param bool $keepGracePeriod Keep access until the paid-for period ends
     * @return bool
     */
    public function cancelSubscription(bool $keepGracePeriod = true): bool
    {
        if ($subscription = $this->subscription()) {
            $currentEnd = $subscription['end_date'] ?? null;
            $keepCurrentEnd = $keepGracePeriod && $currentEnd && strtotime($currentEnd) > time();

            db()
                ->update('subscriptions')
                ->params([
                    'status' => Subscription::STATUS_CANCELLED,
                    'end_date' => $keepCurrentEnd ? $currentEnd : tick()->format('YYYY-MM-DD HH:mm:ss'),
                ])
                ->where('id', $subscription['id'])
                ->execute();

            return true;
        }

        return false;
    }
}
