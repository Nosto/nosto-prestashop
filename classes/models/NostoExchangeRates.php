<?php

use Nosto\Object\ExchangeRate;
use Nosto\Object\ExchangeRateCollection;

class NostoExchangeRates extends ExchangeRateCollection
{
    /**
     * @param Context $context
     * @return NostoExchangeRates
     */
    public static function loadData(Context $context)
    {
        $base_currency_code = NostoTaggingHelperCurrency::getBaseCurrency($context)->iso_code;
        $currencies = NostoTaggingHelperCurrency::getCurrencies($context, true);
        $rates = new NostoExchangeRates();
        foreach ($currencies as $currency) {
            // Skip base currencyCode.
            if (
                $currency['iso_code'] === $base_currency_code
                || $currency['deleted'] == 1
            ) {
                continue;
            }

            $rate = new ExchangeRate($currency['iso_code'], $currency['conversion_rate']);
            $rates->addRate($currency['iso_code'], $rate);
        }

        return $rates;
    }
}