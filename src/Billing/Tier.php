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

        unset($this->tierData['price.yearly']);
        unset($this->tierData['price.monthly']);
        unset($this->tierData['price.weekly']);
        unset($this->tierData['price.daily']);

        $this->tierData['id'] = $id;
        $this->tierData['link'] = "/billing/payments/$id";
        $this->tierData['billingPeriod'] = $tierData['billingPeriod'] ?? 'one-time';
        $this->tierData['price'] = $tierData['price'] ?? ($tierData['billingPeriod'] === 'daily' ? $tierData['price.daily'] : ($tierData['billingPeriod'] === 'monthly' ? $tierData['price.monthly'] : $tierData['price.yearly']));

        // prices are charged in one currency but may need to be shown in
        // another, so carry both rather than making views do the maths
        $this->tierData['currency'] = $this->tierData['currency'] ?? Currency::code();
        $this->tierData['currencySymbol'] = Currency::symbol();
        $this->tierData['displayCurrency'] = Currency::displayCode();
        $this->tierData['displayPrice'] = Currency::convert($this->tierData['price']);
        $this->tierData['formattedPrice'] = Currency::format($this->tierData['price']);
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
