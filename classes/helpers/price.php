<?php
/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
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
     * @param Product|ProductCore $product the product.
     * @return float the price.
     */
    public static function getProductWholesalePriceInclTax(Product $product)
    {
        $wholesale_price_exc_taxes = $product->wholesale_price;
        if ($wholesale_price_exc_taxes > 0) {
            if ($product->tax_rate > 0) {
                $wholesale_price_inc_taxes = NostoHelperPrice::roundPrice(
                    $wholesale_price_exc_taxes * (1 + $product->tax_rate / 100)
                );
            } else {
                $wholesale_price_inc_taxes = $wholesale_price_exc_taxes;
            }
        } else {
            $wholesale_price_inc_taxes = null;
        }

        return $wholesale_price_inc_taxes;
    }

    /**
     * Returns the cart item price including taxes for the given currency.
     *
     * @param Cart|CartCore $cart the cart.
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
            $id_address = (int)$cart->id_address_invoice;
        } else {
            $id_address = (int)$item[self::ID_ADDRESS_DELIVERY];
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
                self::ID_ADDRESS => (Address::addressExists($id_address) ? (int)$id_address : null),
            )
        );
    }

    /**
     * Returns the product price for the given currency.
     * The price is rounded according to the configured rounding mode in PS.
     *
     * @param int $id_product the product ID.
     * @param Currency|CurrencyCore $currency the currency object.
     * @param array $options options for the Product::getPriceStatic method.
     * @return float the price.
     */
    public static function calcPrice(
        $id_product,
        Currency $currency,
        array $options = array()
    ) {
        $employeeId = NostoHelperContext::getEmployeeId();
        if (empty(NostoHelperContext::getEmployee())) {
            $employee = new Employee();
            $employeeId = $employee->id;
        }

        return NostoHelperContext::runInContext(
            function () use ($options, $id_product) {
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
                $specific_price_output = null;

                $value = Product::getPriceStatic(
                    (int)$id_product,
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
                    $specific_price_output,
                    $options['with_eco_tax'],
                    $options['use_group_reduction'],
                    Context::getContext(),
                    $options['use_customer_price']
                );

                return NostoHelperPrice::roundPrice($value);
            },
            false,
            false,
            $currency->id,
            $employeeId
        );
    }

    /**
     * Rounds the price according to the PS rounding mode setting.
     *
     * @param float $price the price to round.
     * @return float the rounded price.
     */
    protected static function roundPrice($price)
    {
        return Tools::ps_round($price, 2);
    }
}
