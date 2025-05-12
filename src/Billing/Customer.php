<?php

namespace Leaf\Billing;

/**
 * Customer
 * -----------
 * Customer class for Leaf Billing
 */
class Customer
{
    protected $providerCustomer;

    public function __construct($paymentCustomer)
    {
        $this->providerCustomer = $paymentCustomer;
    }

    /**
     * Return current customer id
     * @return string
     */
    public function id(): string
    {
        return $this->providerCustomer->id;
    }

    /**
     * The address of the customer
     */
    public function address()
    {
        return $this->providerCustomer->address;
    }

    /**
     * Return current provider customer object
     * @return object
     */
    public function customer(): object
    {
        return $this->providerCustomer;
    }

    /**
     * Description of the customer
     * @return string|null
     */
    public function description(): string
    {
        return $this->providerCustomer->description;
    }

    /**
     * Email of the customer
     * @return string|null
     */
    public function email(): ?string
    {
        return $this->providerCustomer->email;
    }

    /**
     * Name of the customer
     * @return string|null
     */
    public function name(): ?string
    {
        return $this->providerCustomer->name;
    }

    /**
     * Phone number of the customer
     * @return string|null
     */
    public function phone(): ?string
    {
        return $this->providerCustomer->phone;
    }

    /**
     * Current subscription of the customer
     * @return Subscription|null
     */
    public function subscription(): ?string
    {
        return $this->providerCustomer->subscriptions;
    }

    /**
     * Get current subscription tier of the customer
     * @return array|null
     */
    public function tier(): ?array
    {
        return $this->providerCustomer->subscriptions;
    }
    public function __call($method, $args)
    {
        return $this->providerCustomer->$method(...$args);
    }

    public function __get($property)
    {
        return $this->providerCustomer->$property;
    }

    public function __set($property, $value)
    {
        $this->providerCustomer->$property = $value;
    }

    public function __isset($property)
    {
        return isset($this->providerCustomer->$property);
    }
}
