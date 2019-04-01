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
     * Returns the product wholesale price including taxes for the given currency.
     *
     * @param Product $product the product.
     * @return float|null the price.
     */
    public static function getProductWholesalePriceInclTax(Product $product)
    {
        $wholesalePriceExcTaxes = $product->wholesale_price;
        if ($wholesalePriceExcTaxes > 0) {
            if ($product->tax_rate > 0) {
                return NostoHelperPrice::roundPrice(
                    $wholesalePriceExcTaxes * (1 + $product->tax_rate / 100)
                );
            } else {
                return $wholesalePriceExcTaxes;
            }
        } else {
            return null;
        }
    }

    /**
     * Returns the cart item price including taxes for the given currency.
     *
     * @param Cart $cart the cart.
     * @param array $item the cart item.
     * @param Currency $currency the currency.
     * @return float the price.
     */
    public function getCartItemPriceInclTax(
        Cart $cart,
        array $item,
        Currency $currency
    ) {
        if (Configuration::get(self::PS_TAX_ADDRESS_TYPE) == self::ID_ADDRESS_INVOICE) {
            $idAddress = (int)$cart->id_address_invoice;
        } else {
            $idAddress = (int)$item[self::ID_ADDRESS_DELIVERY];
        }

        return NostoHelperPrice::calcPrice(
            (int)$item[self::ID_PRODUCT],
            $currency,
            array(
                'user_reduction' => true,
                self::ID_PRODUCT_ATTRIBUTE => (
                isset($item[self::ID_PRODUCT_ATTRIBUTE]) ? (int)$item[self::ID_PRODUCT_ATTRIBUTE] : null
                ),
                self::ID_CUSTOMER => ((int)$cart->id_customer ? (int)$cart->id_customer : null),
                self::ID_CART => (int)$cart->id,
                self::ID_ADDRESS => (Address::addressExists($idAddress) ? (int)$idAddress : null),
            )
        );
    }

    /**
     * Returns the product price for the given currency.
     * The price is rounded according to the configured rounding mode in PS.
     *
     * @param int $idProduct the product ID.
     * @param Currency $currency the currency object.
     * @param array $options options for the Product::getPriceStatic method.
     * @return float the price.
     */
    public static function calcPrice(
        $idProduct,
        Currency $currency,
        array $options = array()
    ) {
        $employeeId = NostoHelperContext::getEmployeeId();
        if ($employeeId === null) {
            $employee = new Employee();
            $employeeId = $employee->id;
        }

        return NostoHelperContext::runInContext(
            function () use ($options, $idProduct) {
                $options = array_merge(array(
                    'include_tax' => true,
                    'id_product_attribute' => null,
                    'decimals' => 6,
                    'divisor' => null,
                    'only_reduction' => false,
                    'user_reduction' => true,
                    'quantity' => 1,
                    'force_associated_tax' => false,
                    'id_customer' => null,
                    'id_cart' => null,
                    'id_address' => null,
                    'with_eco_tax' => true,
                    'use_group_reduction' => true,
                    'use_customer_price' => true,
                ), $options);
                // This option is used as a reference, so we need it in a separate variable.
                $specificPriceOutput = null;

                $value = Product::getPriceStatic(
                    (int)$idProduct,
                    $options['include_tax'],
                    $options['id_product_attribute'],
                    $options['decimals'],
                    $options['divisor'],
                    $options['only_reduction'],
                    $options['user_reduction'],
                    $options['quantity'],
                    $options['force_associated_tax'],
                    $options['id_customer'],
                    $options['id_cart'],
                    $options['id_address'],
                    $specificPriceOutput,
                    $options['with_eco_tax'],
                    $options['use_group_reduction'],
                    Context::getContext(),
                    $options['use_customer_price']
                );

                //A hack to fix the multi-currency issue. Function Tools::convertPrice() caches the
                //default currency. If multi-store is enabled and default currencies are different in
                //different stores, it cause problem. Big number 1000,000 is used to avoid rounding issue.
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
            true,
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
        $currencyDecimalsEnabled = $currency ? (int)$currency->decimals : 1;

        return (float)Tools::ps_round(
            $price,
            $currencyDecimalsEnabled * _PS_PRICE_DISPLAY_PRECISION_
        );
    }
}
