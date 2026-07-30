<?php

/*
|--------------------------------------------------------------------------
| Test harness for leafs/billing
|--------------------------------------------------------------------------
| The lifecycle helpers on Event and Session run against the subscriptions
| table through the db() and tick() globals (normally provided by leaf's
| db and date modules — real leafs/db on sqlite and leafs/date are used
| here) and resolve tiers through billing(). billing() is shimmed with a
| minimal fake provider so no real Stripe/Paystack calls happen.
*/

use Leaf\Db;

define('SANDBOX', '/tmp/billing-test-sandbox' . (getenv('TEST_TOKEN') ? '-' . getenv('TEST_TOKEN') : ''));

const TIERS = [
    'price_basic' => [
        'id' => 'price_basic',
        'name' => 'Basic',
        'billingPeriod' => 'monthly',
        'amount' => 1000,
    ],
    'price_pro' => [
        'id' => 'price_pro',
        'name' => 'Pro',
        'billingPeriod' => 'yearly',
        'amount' => 10000,
    ],
];

function db(): Db
{
    static $db = null;

    if ($db === null) {
        $db = new Db();
        $db->connect([
            'dbtype' => 'sqlite',
            'dbname' => SANDBOX . '/billing.sqlite',
        ]);
    }

    return $db;
}

/**
 * Fake billing driver, resolved by the real billing() helper via the
 * `fake` connection configured below — no provider API calls happen.
 */
class FakeBillingDriver
{
    public function __construct($config = [])
    {
    }

    public function tier(string $id): ?array
    {
        return TIERS[$id] ?? null;
    }

    public function tiers(?string $billingPeriod = null): array
    {
        return TIERS;
    }

    public function providerName(): string
    {
        return 'fake';
    }
}

class_alias(FakeBillingDriver::class, 'Leaf\\Billing\\Fake');

/*
 * Minimal shim for Leaf\Config (normally shipped by leaf core) — just
 * enough for the billing() helper's singleton bookkeeping.
 */
if (!class_exists('Leaf\\Config')) {
    eval('namespace Leaf; class Config {
        protected static $items = [];

        public static function getStatic($key)
        {
            return static::$items[$key] ?? null;
        }

        public static function singleton($key, $callback)
        {
            static::$items[$key] = $callback();
        }

        public static function get($key)
        {
            return static::$items[$key] ?? null;
        }
    }');
}

// first call configures the singleton with the fake driver
billing([
    'default' => 'fake',
    'connections' => [
        'fake' => ['driver' => 'fake'],
    ],
]);

function setupBillingEnv(): void
{
    db()->query('CREATE TABLE IF NOT EXISTS subscriptions (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        user_id INTEGER,
        name TEXT,
        plan_id TEXT,
        payment_session_id TEXT,
        subscription_id TEXT,
        status TEXT,
        start_date TIMESTAMP,
        end_date TIMESTAMP,
        trial_ends_at TIMESTAMP
    )')->execute();

    db()->delete('subscriptions')->execute();
}

/** Insert a subscription row and return its id */
function seedSubscription(array $overrides = []): int
{
    db()->insert('subscriptions')->params(array_merge([
        'user_id' => 1,
        'name' => 'Basic',
        'plan_id' => 'price_basic',
        'payment_session_id' => 'cs_test_123',
        'subscription_id' => 'sub_123',
        'status' => \Leaf\Billing\Subscription::STATUS_INCOMPLETE,
        'start_date' => tick()->format('YYYY-MM-DD HH:mm:ss'),
        'end_date' => tick()->add(1, 'month')->format('YYYY-MM-DD HH:mm:ss'),
        'trial_ends_at' => null,
    ], $overrides))->execute();

    return (int) db()->lastInsertId();
}

function subscriptionRow(int $id): array
{
    return db()->select('subscriptions')->where('id', $id)->first();
}

/** Build an Event as a provider would from a webhook payload */
function billingEvent(string $type, array $object, array $extra = []): \Leaf\Billing\Event
{
    return new \Leaf\Billing\Event(array_merge([
        'type' => $type,
        'data' => ['object' => $object],
        'id' => 'evt_1',
        'created' => time(),
    ], $extra));
}

uses()->beforeEach(fn () => setupBillingEnv())->in(__DIR__);

if (!is_dir(SANDBOX)) {
    mkdir(SANDBOX, 0777, true);
}
