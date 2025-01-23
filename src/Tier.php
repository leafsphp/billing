<?php

namespace Leaf\Billing;

/**
 * Tier transformations for Leaf Billing
 * ----
 * This class is used to transform billing tiers
 * into a more accessible format for accessing
 */
class Tier
{
    protected $tierData;

    public function __construct(string $id, array $tierData)
    {
        $this->tierData = $tierData;

        $this->tierData['id'] = $id;
        $this->tierData['link'] = "/billing/payments/$id";
        $this->tierData['type'] = $tierData['type'] ?? 'one-time';
        $this->tierData['price'] = $tierData['price'] ?? ($tierData['type'] === 'daily' ? $tierData['price.daily'] : ($tierData['type'] === 'monthly' ? $tierData['price.monthly'] : $tierData['price.yearly']));
    }

    public function __get($key)
    {
        return $this->tierData[$key] ?? null;
    }

    public function __set($key, $value)
    {
        $this->tierData[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->tierData[$key]);
    }

    public function __unset($key)
    {
        unset($this->tierData[$key]);
    }

    public function toArray()
    {
        return $this->tierData;
    }

    public function toJson()
    {
        return json_encode($this->tierData);
    }
}
