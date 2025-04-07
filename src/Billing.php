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
