<?php

use Leaf\Billing\Currency;
use Leaf\Billing\Tier;

/*
 * Currency resolution reads the billing config through the Leaf\Billing
 * instance, so each test registers a config and clears it afterwards.
 */

function billingConfig(array $currency): void
{
    \Leaf\Config::singleton('billing', function () use ($currency) {
        return new \Leaf\Billing([
            'default' => 'stripe',
            'connections' => [
                'stripe' => ['driver' => 'stripe', 'currency' => $currency],
            ],
        ]);
    });
}

beforeEach(function () {
    // other test files share this singleton, so put it back afterwards
    $this->originalBilling = \Leaf\Config::getStatic('billing');
});

afterEach(function () {
    $original = $this->originalBilling;
    \Leaf\Config::singleton('billing', fn () => $original);
});

test('falls back to sensible defaults with no config', function () {
    \Leaf\Config::singleton('billing', fn () => null);

    expect(Currency::code())->toBe('usd');
    expect(Currency::symbol())->toBe('$');
});

test('reads the charge currency from the active connection', function () {
    billingConfig(['name' => 'GHS', 'symbol' => 'GH₵']);

    expect(Currency::code())->toBe('ghs');
    expect(Currency::symbol())->toBe('GH₵');
});

test('display currency falls back to the charge currency', function () {
    billingConfig(['name' => 'GHS', 'symbol' => 'GH₵']);

    expect(Currency::displayCode())->toBe('ghs');
    expect(Currency::convertsForDisplay())->toBeFalse();
    expect(Currency::convert(100))->toBe(100);
});

test('converts amounts into the display currency', function () {
    billingConfig([
        'name' => 'GHS',
        'symbol' => 'GH₵',
        'display' => 'USD',
        'displaySymbol' => '$',
        'conversion' => 0.07,
    ]);

    expect(Currency::convertsForDisplay())->toBeTrue();
    expect(Currency::displayCode())->toBe('usd');
    expect(Currency::convert(100))->toBe(7.0);
    expect(Currency::format(100))->toBe('$7');
});

test('a display currency without a conversion rate leaves amounts alone', function () {
    billingConfig(['name' => 'GHS', 'display' => 'USD', 'displaySymbol' => '$']);

    expect(Currency::convert(100))->toBe(100);
});

test('format uses the charge symbol when no display currency is set', function () {
    billingConfig(['name' => 'GHS', 'symbol' => 'GH₵']);

    expect(Currency::format(25))->toBe('GH₵25');
    expect(Currency::format(25.5))->toBe('GH₵25.5');
});

test('tiers carry both charge and display prices', function () {
    billingConfig([
        'name' => 'GHS',
        'symbol' => 'GH₵',
        'display' => 'USD',
        'displaySymbol' => '$',
        'conversion' => 0.07,
    ]);

    $tier = (new Tier('price_starter', [
        'name' => 'Starter',
        'billingPeriod' => 'monthly',
        'price.monthly' => 100,
        'price.yearly' => 1000,
    ]))->toArray();

    expect($tier['price'])->toBe(100);          // charged in GHS
    expect($tier['displayPrice'])->toBe(7.0);   // shown in USD
    expect($tier['currency'])->toBe('ghs');
    expect($tier['displayCurrency'])->toBe('usd');
    expect($tier['formattedPrice'])->toBe('$7');
});
