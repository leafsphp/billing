<?php

namespace Leaf\Billing;

use Leaf\Auth\Model;

/**
 * Leaf User Billable
 * -----------
 * Add billable and subscription features to your the auth user object
 */
trait Billable
{
    /**
     * Get user's subscriptions
     */
    public function subscriptions()
    {
        return (new Model([
            'user' => $this,
            'table' => 'subscriptions',
            'db' => auth()->db(),
        ]));
    }

    /**
     * Get user's subscription
     */
    public function subscription($id)
    {
        if (is_numeric($id)) {
            return $this->subscriptions()->find($id);
        } else {
            return $this->subscriptions()->where('name', $id)->first();
        }
    }

    /**
     * Check if user has a subscription or is subscribed to a plan
     */
    public function isSubscribed($id = null)
    {
        if ($id) {
            return !!$this->subscription($id);
        }

        return $this->subscriptions()->count() > 0;
    }

    /**
     * Check if a user is on trial
     */
    public function onTrial($id = null)
    {
        if ($id) {
            return ($this->subscription($id)['trial_ends_at'] ?? 0) > time();
        }

        return $this->subscriptions()->where('trial_ends_at', '>', time())->count() > 0;
    }

    /**
     * Check if a user is on grace period
     */
    public function onGracePeriod($id = null)
    {
        if ($id) {
            return ($this->subscription($id)['ends_at'] ?? 0) > time();
        }

        return $this->subscriptions()->where('ends_at', '>', time())->count() > 0;
    }
}
