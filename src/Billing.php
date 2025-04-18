<?php

namespace Leaf;

/**
 * Leaf Billing
 * -----------
 * Unified billing system for Leaf. Works with Stripe and Paystack,
 * and more providers coming soon.
 */
class Billing
{
    /**
     * Config for full billing library
     * @var array
     */
    protected $config = [];

    protected $instances = [];

    public function __construct($config = [])
    {
        $this->config = $config;

        if (function_exists('auth')) {
            $this->middleware('billing.subscribed', function ($plan = null) {
                response()->redirect('/billing/subscribe');
            });

            $this->middleware('billing.unsubscribed', function ($plan = null) {
                response()->redirect('/dashboard');
            });

            $this->middleware('billing.trial', function () {
                response()->redirect('/');
            });

            $this->middleware('billing.not-trial', function () {
                response()->redirect('/');
            });
        }
    }

    /**
     * Get a billing provider via name in config
     *
     * @param string|null $driver Provider name
     * @throws \Exception
     * @return \Leaf\Billing\BillingProvider
     */
    public function getDriver(?string $driver = null)
    {
        $driver = $driver ?? $this->config['default'] ?? 'stripe';

        if (!isset($this->instances[$driver])) {
            $instanceConfig = $this->config['connections'][$driver] ?? null;

            if (!$instanceConfig) {
                throw new \Exception("Driver $driver not found in billing config");
            }

            $className = ucfirst($instanceConfig['driver']);
            $provider = "Leaf\\Billing\\$className";

            $this->instances[$driver] = new $provider(array_merge(
                ['connection' => $instanceConfig],
                $this->config
            ));
        }

        return $this->instances[$driver];
    }

    /**
     * Register billing middleware for your Leaf apps
     * @param string $middleware The middleware to register
     * @param callable $callback The callback to run if middleware fails
     */
    public function middleware(string $middleware, callable $callback)
    {
        if ($middleware === 'billing.subscribed') {
            return app()->registerMiddleware('billing.subscribed', function ($plan = null) use ($callback) {
                if (!auth()->user() || !auth()->user()->hasActiveSubscription() || ($plan && (auth()->user()->subscription()['name'] ?? null) !== $plan[0])) {
                    $callback($plan);
                    exit;
                }
            });
        }

        if ($middleware === 'billing.unsubscribed') {
            return app()->registerMiddleware('billing.not-subscribed', function ($plan = null) use ($callback) {
                if (!auth()->user() || (auth()->user()->hasActiveSubscription() && ($plan ? ((auth()->user()->subscription()['name'] ?? null) === $plan[0]) : true))) {
                    $callback($plan);
                    exit;
                }
            });
        }

        if ($middleware === 'billing.trial') {
            return app()->registerMiddleware('billing.trial', function () use ($callback) {
                if (!auth()->user() || !auth()->user()->hasActiveSubscription() || auth()->user()->subscription()['status'] !== \Leaf\Billing\Subscription::STATUS_TRIAL) {
                    $callback();
                    exit;
                }
            });
        }

        if ($middleware === 'billing.not-trial') {
            return app()->registerMiddleware('billing.not-trial', function () use ($callback) {
                if (!auth()->user() || (auth()->user()->hasActiveSubscription() && auth()->user()->subscription()['status'] === \Leaf\Billing\Subscription::STATUS_TRIAL)) {
                    $callback();
                    exit;
                }
            });
        }

        app()->registerMiddleware($middleware, $callback);
    }

    /**
     * Get billing commands to register
     * @return array
     */
    public static function commands()
    {
        return [
            \Leaf\Billing\Commands\ScaffoldSubscriptionsCommand::class,
            \Leaf\Billing\Commands\ConfigBillingComand::class,
            // \Leaf\Billing\Commands\ScaffoldBillingPlansCommand::class,
            // \Leaf\Billing\Commands\BillingInitPlansCommand::class,
        ];
    }
}
