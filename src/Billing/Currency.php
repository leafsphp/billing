<?php

namespace Leaf\Billing;

/**
 * Billing Currency
 * ----
 * Resolves the currency your customers are charged in, and the (optional)
 * currency you display prices in when the two need to differ.
 */
class Currency
{
    /**
     * Resolved currency settings for the active billing connection
     * @return array
     */
    public static function config(): array
    {
        $defaults = [
            'name' => _env('BILLING_CURRENCY', 'usd'),
            'symbol' => _env('BILLING_CURRENCY_SYMBOL', '$'),
            'locale' => _env('BILLING_CURRENCY_LOCALE', 'en_US'),
            'display' => _env('BILLING_CURRENCY_DISPLAY', null),
            'displaySymbol' => _env('BILLING_CURRENCY_DISPLAY_SYMBOL', null),
            'conversion' => _env('BILLING_CURRENCY_DISPLAY_CONVERSION', null),
        ];

        $billing = \Leaf\Config::getStatic('billing');

        if (!$billing) {
            return $defaults;
        }

        $connection = $billing->config('default') ?? 'stripe';
        $currency = $billing->config("connections.$connection.currency") ?? [];

        return array_merge($defaults, array_filter(
            $currency,
            function ($value) {
                return $value !== null && $value !== '';
            }
        ));
    }

    /**
     * The currency customers are actually charged in
     */
    public static function code(): string
    {
        return strtolower((string) (static::config()['name'] ?? 'usd'));
    }

    /**
     * Symbol for the charge currency
     */
    public static function symbol(): string
    {
        return (string) (static::config()['symbol'] ?? '$');
    }

    /**
     * The currency prices are shown in. Falls back to the charge currency
     * when no display currency is configured.
     */
    public static function displayCode(): string
    {
        $config = static::config();

        return strtolower((string) ($config['display'] ?? $config['name'] ?? 'usd'));
    }

    /**
     * Symbol for the display currency
     */
    public static function displaySymbol(): string
    {
        $config = static::config();

        return (string) ($config['displaySymbol'] ?? $config['symbol'] ?? '$');
    }

    /**
     * Is a separate display currency configured?
     */
    public static function convertsForDisplay(): bool
    {
        $config = static::config();

        return !empty($config['display'])
            && strtolower((string) $config['display']) !== strtolower((string) ($config['name'] ?? ''));
    }

    /**
     * Convert an amount from the charge currency to the display currency
     *
     * @param int|float $amount Amount in the charge currency
     * @return int|float
     */
    public static function convert($amount)
    {
        $rate = static::config()['conversion'] ?? null;

        if (!static::convertsForDisplay() || !$rate) {
            return $amount;
        }

        return round(((float) $amount) * ((float) $rate), 2);
    }

    /**
     * Format an amount for display, converting it first when a display
     * currency is configured
     *
     * @param int|float $amount Amount in the charge currency
     * @param bool $convert Apply the display conversion?
     */
    public static function format($amount, bool $convert = true): string
    {
        $value = $convert ? static::convert($amount) : $amount;
        $symbol = $convert && static::convertsForDisplay() ? static::displaySymbol() : static::symbol();

        return $symbol . rtrim(rtrim(number_format((float) $value, 2, '.', ','), '0'), '.');
    }
}
