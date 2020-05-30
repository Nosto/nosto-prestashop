<?php
/**
 * 2013-2019 Nosto Solutions Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@nosto.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2019 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\NostoException;
use Nosto\Object\ExchangeRate as NostoSDKExchangeRate;
use Nosto\Object\ExchangeRateCollection as NostoSDKExchangeRateCollection;

class NostoExchangeRates extends NostoSDKExchangeRateCollection
{
    /**
     * @return NostoExchangeRates the exchange rates object
     * @throws NostoException
     */
    public static function loadData()
    {
        $baseCurrencyCode = NostoHelperCurrency::getBaseCurrency()->iso_code;
        $currencies = NostoHelperCurrency::getCurrencies(true);
        $nostoRates = new NostoExchangeRates();
        foreach ($currencies as $currency) {
            // Skip base currencyCode.
            if ($currency['iso_code'] === $baseCurrencyCode
                || $currency['deleted'] == 1
            ) {
                continue;
            }

            $rate = new NostoSDKExchangeRate($currency['iso_code'], $currency['conversion_rate']);
            $nostoRates->addRate($currency['iso_code'], $rate);
        }

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoRates), array(
            'nosto_exchange_rates' => $nostoRates
        ));
        return $nostoRates;
    }
}
