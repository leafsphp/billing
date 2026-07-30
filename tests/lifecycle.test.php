<?php

use Leaf\Billing\Session;
use Leaf\Billing\Subscription;

test('events expose id and type for idempotency checks', function () {
    $event = billingEvent('invoice.payment_succeeded', ['id' => 'sub_123']);

    expect($event->id())->toBe('evt_1');
    expect($event->type())->toBe('invoice.payment_succeeded');
    expect($event->is('invoice.payment_succeeded'))->toBeTrue();
    expect($event->is('other.event'))->toBeFalse();
});

test('event resolves the subscription from the db without an auth context', function () {
    $id = seedSubscription();

    $event = billingEvent('customer.subscription.updated', ['id' => 'sub_123']);

    expect($event->subscription()['id'])->toBe($id);
});

test('event resolves paystack subscriptions by subscription_code', function () {
    $id = seedSubscription(['subscription_id' => 'SUB_paystack1']);

    $event = billingEvent('subscription.create', ['subscription_code' => 'SUB_paystack1']);

    expect($event->subscription()['id'])->toBe($id);
});

test('activateSubscription flips an incomplete subscription to active', function () {
    $id = seedSubscription(['status' => Subscription::STATUS_INCOMPLETE]);

    $event = billingEvent('customer.subscription.updated', ['id' => 'sub_123']);

    expect($event->activateSubscription())->toBeTrue();
    expect(subscriptionRow($id)['status'])->toBe(Subscription::STATUS_ACTIVE);
});

test('renewSubscription extends end_date one billing period past the current one', function () {
    $end = tick()->add(3, 'days')->format('YYYY-MM-DD HH:mm:ss');
    $id = seedSubscription(['status' => Subscription::STATUS_ACTIVE, 'end_date' => $end]);

    $event = billingEvent('invoice.payment_succeeded', ['id' => 'sub_123']);

    expect($event->renewSubscription())->toBeTrue();

    $row = subscriptionRow($id);
    expect($row['status'])->toBe(Subscription::STATUS_ACTIVE);
    expect(strtotime($row['end_date']))->toBe(strtotime('+1 month', strtotime($end)));
});

test('renewSubscription recovers a past_due subscription from now', function () {
    $expired = tick()->subtract(2, 'days')->format('YYYY-MM-DD HH:mm:ss');
    $id = seedSubscription(['status' => Subscription::STATUS_PAST_DUE, 'end_date' => $expired]);

    $event = billingEvent('invoice.payment_succeeded', ['id' => 'sub_123']);

    expect($event->renewSubscription())->toBeTrue();

    $row = subscriptionRow($id);
    expect($row['status'])->toBe(Subscription::STATUS_ACTIVE);
    expect(strtotime($row['end_date']))->toBeGreaterThan(time());
});

test('markSubscriptionPastDue puts the subscription into dunning', function () {
    $id = seedSubscription(['status' => Subscription::STATUS_ACTIVE]);

    $event = billingEvent('invoice.payment_failed', ['id' => 'sub_123']);

    expect($event->markSubscriptionPastDue())->toBeTrue();
    expect(subscriptionRow($id)['status'])->toBe(Subscription::STATUS_PAST_DUE);
});

test('cancelSubscription keeps the paid-for period as a grace period', function () {
    $end = tick()->add(10, 'days')->format('YYYY-MM-DD HH:mm:ss');
    $id = seedSubscription(['status' => Subscription::STATUS_ACTIVE, 'end_date' => $end]);

    $event = billingEvent('customer.subscription.deleted', ['id' => 'sub_123']);

    expect($event->cancelSubscription())->toBeTrue();

    $row = subscriptionRow($id);
    expect($row['status'])->toBe(Subscription::STATUS_CANCELLED);
    expect($row['end_date'])->toBe($end);
});

test('cancelSubscription can revoke access immediately', function () {
    $end = tick()->add(10, 'days')->format('YYYY-MM-DD HH:mm:ss');
    $id = seedSubscription(['status' => Subscription::STATUS_ACTIVE, 'end_date' => $end]);

    $event = billingEvent('subscription.disable', ['id' => 'sub_123']);

    expect($event->cancelSubscription(false))->toBeTrue();

    $row = subscriptionRow($id);
    expect($row['status'])->toBe(Subscription::STATUS_CANCELLED);
    expect(strtotime($row['end_date']))->toBeLessThanOrEqual(time());
});

test('lifecycle helpers return false when the event has no known subscription', function () {
    $event = billingEvent('invoice.payment_succeeded', ['id' => 'sub_unknown']);

    expect($event->activateSubscription())->toBeFalse();
    expect($event->renewSubscription())->toBeFalse();
    expect($event->markSubscriptionPastDue())->toBeFalse();
    expect($event->cancelSubscription())->toBeFalse();
});

test('session activation marks the subscription active and refreshes dates', function () {
    $staleDate = tick()->subtract(2, 'days')->format('YYYY-MM-DD HH:mm:ss');
    $id = seedSubscription([
        'status' => Subscription::STATUS_INCOMPLETE,
        'subscription_id' => null,
        'start_date' => $staleDate,
        'end_date' => $staleDate,
    ]);

    $session = new Session((object) [
        'id' => 'cs_test_123',
        'subscription' => 'sub_from_provider',
    ]);

    expect($session->activateSubscription())->toBeTrue();

    $row = subscriptionRow($id);
    expect($row['status'])->toBe(Subscription::STATUS_ACTIVE);
    expect($row['subscription_id'])->toBe('sub_from_provider');
    expect(strtotime($row['end_date']))->toBeGreaterThan(time());
});

test('session activation respects a pending trial', function () {
    $id = seedSubscription([
        'status' => Subscription::STATUS_INCOMPLETE,
        'trial_ends_at' => tick()->add(7, 'days')->format('YYYY-MM-DD HH:mm:ss'),
    ]);

    $session = new Session((object) [
        'id' => 'cs_test_123',
        'subscription' => 'sub_from_provider',
    ]);

    expect($session->activateSubscription())->toBeTrue();
    expect(subscriptionRow($id)['status'])->toBe(Subscription::STATUS_TRIAL);
});

test('session activation fails for one-time payments and unknown sessions', function () {
    $oneTime = new Session((object) ['id' => 'cs_x', 'subscription' => null]);
    expect($oneTime->activateSubscription())->toBeFalse();

    $unknown = new Session((object) ['id' => 'cs_missing', 'subscription' => 'sub_y']);
    expect($unknown->activateSubscription())->toBeFalse();
});
