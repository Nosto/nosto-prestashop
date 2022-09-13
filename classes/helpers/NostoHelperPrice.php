<?php /** @noinspection PhpUnused */
/**
 * 2013-2020 Nosto Solutions Ltd
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
 * @copyright 2013-2020 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for price operations.
 */
class NostoHelperPrice
{
    const PS_TAX_ADDRESS_TYPE = 'PS_TAX_ADDRESS_TYPE';
    const ID_ADDRESS_INVOICE = 'id_address_invoice';
    const ID_ADDRESS_DELIVERY = 'id_address_delivery';
    const ID_PRODUCT_ATTRIBUTE = 'id_product_attribute';
    const ID_CUSTOMER = 'id_customer';
    const ID_CART = 'id_cart';
    const ID_ADDRESS = 'id_address';
    const ID_PRODUCT = 'id_product';

    /**
     * Returns the product wholesale price for the given currency.
     *
     * @param Product $product the product.
     * @return float|null the price.
     */
    public static function getProductWholesalePrice(Product $product)
    {
        return $product->wholesale_price > 0 ? self::roundPrice($product->wholesale_price) : null;
    }

    /**
     * Returns the product price for the given currency.
     * The price is rounded according to the configured rounding mode in PS.
     *
     * @param int $idProduct the product ID.
     * @param Currency $currency the currency object.
     * @param bool $isUserReduced
     * @param int|null $productAttributeId
     * @return float the price.
     * @throws PrestaShopException
     */
    public static function calcPrice(
        $idProduct,
        Currency $currency,
        $isUserReduced = true,
        $productAttributeId = null
    ) {
        $employeeId = NostoHelperContext::getEmployeeId();
        if ($employeeId === null) {
            $employee = new Employee();
            $employeeId = $employee->id;
        }

        return NostoHelperContext::runInContext(
            function () use ($isUserReduced, $productAttributeId, $idProduct) {
                // This option is used as a reference, so we need it in a separate variable.
                $specificPriceOutput = null;

                $value = Product::getPriceStatic(
                    (int)$idProduct,
                    true,
                    $productAttributeId,
                     6,
                    null,
                    false,
                    $isUserReduced,
                    1,
                    false,
                    null,
                    null,
                    null,
                    $specificPriceOutput,
                    true,
                    true,
                    Context::getContext(),
                    true
                );

                //A hack to fix the multi-currency issue. Function Tools::convertPrice() caches the
                //default currency. If multi-store is enabled and default currencies are different in
                //different stores, it cause problem. Big number 1000,000 is used to avoid rounding issue.
                // @phan-suppress-next-line PhanDeprecatedFunction
                /** @noinspection PhpDeprecationInspection */
                /** @phan-suppress-next-line  PhanDeprecatedFunction */
                $exchangeRate = Tools::convertPrice(
                    1000000,
                    Currency::getCurrencyInstance((int)Configuration::get('PS_CURRENCY_DEFAULT'))
                ) / 1000000;
                $value *= $exchangeRate;

                return NostoHelperPrice::roundPrice($value);
            },
            false,
            false,
            $currency->id,
            $employeeId
        );
    }

    /**
     * Get product price based on customer group
     * @param int $productId
     * @param int $groupId customer group id
     * @param bool $useReduction use group reduction or not
     * @param int $decimals
     * @return float price
     * @suppress PhanTypeMismatchArgument
     */
    public static function getProductPriceForGroup(
        $productId,
        $groupId,
        $useReduction = true,
        $decimals = 6
    ) {
        $specificPrice = 0;
        $price = Product::priceCalculation(
            NostoHelperContext::getShopId(),
            $productId,
            null,
            NostoHelperContext::getCountryId(),
            0,
            '',
            NostoHelperContext::getCurrencyId(),
            $groupId,
            2,
            Group::getPriceDisplayMethod($groupId) == PS_TAX_INC, 
            $decimals,
            false,
            $useReduction,
            true,
            $specificPrice,
            $useReduction
        );

        //A hack to fix the multi-currency issue. Function Tools::convertPrice() caches the
        //default currency. If multi-store is enabled and default currencies are different in
        //different stores, it cause problem. Big number 1000,000 is used to avoid rounding issue.
        // @phan-suppress-next-line PhanDeprecatedFunction
        /** @noinspection PhpDeprecationInspection */
        /** @phan-suppress-next-line  PhanDeprecatedFunction */
        $exchangeRate = Tools::convertPrice(
            1000000,
            Currency::getCurrencyInstance((int)Configuration::get('PS_CURRENCY_DEFAULT'))
        ) / 1000000;
        $price *= $exchangeRate;

        return NostoHelperPrice::roundPrice($price);
    }
    /**
     * Rounds the price according to the PS rounding mode setting.
     *
     * @param float $price the price to round.
     * @param Currency|null $currency
     * @return float the rounded price.
     */
    public static function roundPrice($price, Currency $currency = null)
    {
        if ($currency === null) {
            $currency = NostoHelperContext::getCurrency();
        }
        //if the decimals is disabled for this currency, then the precision should be 0
        /** @noinspection PhpDeprecationInspection */
        /** @phan-suppress-next-line  PhanDeprecatedProperty */
        $currencyDecimalsEnabled = $currency ? (int)$currency->decimals : 1;

        return (float)Tools::ps_round(
            $price,
            $currencyDecimalsEnabled * (int)_PS_PRICE_DISPLAY_PRECISION_
        );
    }
}
