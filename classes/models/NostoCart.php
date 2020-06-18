<?php
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

use Nosto\Object\Cart\Cart as NostoSDKCart;
use Nosto\Object\Cart\LineItem as NostoSDKCartItem;

class NostoCart extends NostoSDKCart
{
    /**
     * The name of the hash parameter to look from URL
     */
    const HASH_PARAM = 'h';

    /**
     * @param $idCurrency
     * @return Currency
     * @suppress PhanTypeMismatchArgument
     */
    private static function loadCurrency($idCurrency)
    {
        return new Currency($idCurrency);
    }

    /**
     * Loads the cart data from supplied cart object.
     *
     * @param Cart $cart the cart model to process
     * @return NostoCart|null the cart object
     */
    public static function loadData(Cart $cart)
    {
        $nostoCart = new NostoCart();

        $currency = self::loadCurrency($cart->id_currency);
        if (Validate::isLoadedObject($cart)
            && $cart->getProducts() !== array()
            && Validate::isLoadedObject($currency)
        ) {
            self::loadCartItems($cart, $nostoCart, $currency);
        }

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoCart), array(
            'cart' => $cart,
            'nosto_cart' => $nostoCart
        ));

        return $nostoCart;
    }

    /**
     * Load cart items and restore cart link if there is any item in cart
     * @param Cart $cart
     * @param NostoCart $nostoCart
     * @param Currency $currency
     */
    private static function loadCartItems(Cart $cart, NostoCart $nostoCart, Currency $currency)
    {
        $products = $cart->getProducts();
        $cartRules = (array)$cart->getCartRules(CartRule::FILTER_ACTION_GIFT);

        $giftProducts = array();
        foreach ($cartRules as $cartRule) {
            if ((int)$cartRule['gift_product']) {
                foreach ($products as $key => &$product) {
                    if (empty($product['gift'])
                        && (int)$product['id_product'] === (int)$cartRule['gift_product']
                        && (int)$product['id_product_attribute'] === (int)$cartRule['gift_product_attribute']
                    ) {
                        $product['cart_quantity'] = (int)$product['cart_quantity'];
                        $product['cart_quantity']--;

                        if (!($product['cart_quantity'] > 0)) {
                            unset($products[$key]);
                        }

                        $giftProduct = $product;
                        $giftProduct['cart_quantity'] = 1;
                        $giftProduct['price_wt'] = 0;
                        $giftProduct['gift'] = true;

                        $giftProducts[] = $giftProduct;

                        break; // One gift product per cart rule
                    }
                }
                unset($product);
            }
        }

        $items = array_merge($products, $giftProducts);

        foreach ($items as $item) {
            $name = $item['name'];
            if (isset($item['attributes_small'])) {
                $name .= ' (' . $item['attributes_small'] . ')';
            }

            $nostoLineItem = new NostoSDKCartItem();
            $nostoLineItem->setProductId($item['id_product']);
            $nostoLineItem->setSkuId($item['id_product_attribute']);
            // @phan-suppress-next-line PhanTypePossiblyInvalidDimOffset
            $nostoLineItem->setQuantity((int)$item['cart_quantity']);
            $nostoLineItem->setName((string)$name);
            if (is_numeric($item['price_wt'])) {
                $nostoLineItem->setPrice(
                    NostoHelperPrice::roundPrice($item['price_wt'], $currency)
                );
            }
            $nostoLineItem->setPriceCurrencyCode((string)$currency->iso_code);
            $nostoCart->addItem($nostoLineItem);
        }

        $hash = NostoCustomerManager::getRestoreCartHash($cart->id);
        if ($hash) {
            $nostoCart->setRestoreLink(
                NostoHelperUrl::getModuleUrl(
                    NostoTagging::MODULE_NAME,
                    'restoreCart',
                    array(self::HASH_PARAM => $hash)
                )
            );
        }
    }
}
