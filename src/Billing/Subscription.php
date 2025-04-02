<?php

namespace Leaf\Billing;

/**
 * Subscription
 * -----------
 * Subscription class for Leaf Billing
 */
class Subscription
{
    public const STATUS_ACTIVE = 'active';
    public const STATUS_TRIAL = 'trial';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_PAST_DUE = 'past_due';
    public const STATUS_INCOMPLETE = 'incomplete';

    public function __construct($id)
    {
        //
    }
}
