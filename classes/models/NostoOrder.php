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
 * @copyright 2013-2019 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\Object\Order\Order as NostoSDKOrder;
use Nosto\Types\Order\BuyerInterface as NostoSDKBuyer;
use Nosto\Object\Cart\LineItem as NostoSDKLineItem;

class NostoOrder extends NostoSDKOrder
{

    /**
     * @param Order $order
     * @return Customer
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @suppress PhanTypeMismatchArgument
     */
    private static function loadCustomer(Order $order)
    {
        return new Customer((string)$order->id_customer);
    }

    /**
     * @param Order $order
     * @return Currency
     * @suppress PhanTypeMismatchArgument
     */
    private static function loadCurrency(Order $order)
    {
        return new Currency((string)$order->id_currency);
    }

    /**
     * Loads the order data from supplied context and order objects.
     *
     * @param Order $order the order model to process
     * @return NostoOrder|null the order object
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function loadData(Order $order)
    {
        if (!Validate::isLoadedObject($order)) {
            return null;
        }

        $nostoOrder = new NostoOrder();
        $customer = self::loadCustomer($order);

        // The order reference was introduced in prestashop 1.5 where orders can be split into multiple ones.
        if (isset($order->reference)) {
            $nostoOrder->setOrderNumber($order->reference);
            $nostoOrder->setExternalOrderRef((string)$order->id);
        } else {
            $nostoOrder->setOrderNumber((string)$order->id);
        }
        $nostoOrder->setOrderNumber(isset($order->reference) ? (string)$order->reference : $order->id);
        $customer = NostoOrderBuyer::loadData($customer, $order);
        if ($customer instanceof NostoSDKBuyer) {
            $nostoOrder->setCustomer($customer);
        }
        $nostoOrder->setCreatedAt(DateTime::createFromFormat('Y-m-d H:i:s', $order->date_add));
        $nostoOrder->setPurchasedItems(self::findPurchasedItems($order));
        $nostoOrder->setPaymentProvider('unknown');

        if (!empty($order->module)) {
            $paymentModule = Module::getInstanceByName($order->module);
            if ($paymentModule !== false && isset($paymentModule->version)) {
                $nostoOrder->setPaymentProvider($order->module . ' [' . $paymentModule->version . ']');
            } else {
                $nostoOrder->setPaymentProvider($order->module . ' [unknown]');
            }
        }

        $nostoOrder->setOrderStatus(NostoOrderStatus::loadData($order));

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoOrder), array(
            'order' => $order,
            'nosto_order' => $nostoOrder
        ));
        return $nostoOrder;
    }

    /**
     * Finds purchased items for the order.
     *
     * @param Order $order the order object.
     * @return NostoOrderPurchasedItem[] the purchased items.
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected static function findPurchasedItems(Order $order)
    {
        $purchasedItems = array();

        $currency = self::loadCurrency($order);
        if (!Validate::isLoadedObject($currency)) {
            return $purchasedItems;
        }

        $products = array();
        $totalDiscountsTaxIncl = 0;
        $totalShippingTaxIncl = 0;
        $totalWrappingTaxIncl = 0;
        $totalGiftTaxIncl = 0;
        $totalProductTaxIncl = 0;

        // One order can be split into multiple orders, so we need to combine their data.
        $orderCollection = Order::getByReference($order->reference);
        foreach ($orderCollection as $item) {
            /** @var $item Order */
            /** @phan-suppress-next-line PhanUndeclaredMethod */
            $products = array_merge($products, $item->getProducts());
            /** @phan-suppress-next-line PhanUndeclaredProperty */
            $totalDiscountsTaxIncl += $item->total_discounts_tax_incl;
            /** @phan-suppress-next-line PhanUndeclaredProperty */
            $totalShippingTaxIncl += $item->total_shipping_tax_incl;
            /** @phan-suppress-next-line PhanUndeclaredProperty */
            $totalWrappingTaxIncl += $item->total_wrapping_tax_incl;
            /** @phan-suppress-next-line PhanUndeclaredProperty */
            $totalProductTaxIncl += $item->total_products_wt;
        }

        // We need the cart rules used for the order to check for gift products and free shipping.
        // The cart is the same even if the order is split into many objects.
        $cart = new Cart($order->id_cart);
        if (Validate::isLoadedObject($cart)) {
            $cartRules = (array)$cart->getCartRules();
        } else {
            $cartRules = array();
        }

        $giftProducts = array();
        foreach ($cartRules as $cartRule) {
            if ((int)$cartRule['gift_product']) {
                foreach ($products as $key => &$product) {
                    if (empty($product['gift'])
                        && (int)$product['product_id'] === (int)$cartRule['gift_product']
                        && (int)$product['product_attribute_id'] === (int)$cartRule['gift_product_attribute']
                    ) {
                        $product['product_quantity'] = (int)$product['product_quantity'];
                        $product['product_quantity']--;
                        if (!($product['product_quantity'] > 0)) {
                            unset($products[$key]);
                        }
                        if (isset($product['product_price_wt'])) {
                            $productPriceWt = $product['product_price_wt'];
                        } else {
                            $productPriceWt = 0;
                        }
                        $totalGiftTaxIncl += $productPriceWt;
                        $giftProduct = $product;
                        $giftProduct['product_quantity'] = 1;
                        $giftProduct['product_price_wt'] = 0;
                        $giftProduct['gift'] = true;
                        $giftProducts[] = $giftProduct;

                        break; // One gift product per cart rule
                    }
                }
                unset($product);
            }
        }
        $items = array_merge($products, $giftProducts);

        $languageId = NostoHelperContext::getLanguageId();
        foreach ($items as $item) {
            $p = new Product($item['product_id'], false, $languageId);
            if (Validate::isLoadedObject($p)) {
                $productName = $p->name;
                $idAttribute = (int)$item['product_attribute_id'];
                $attributeCombinations = $p->getAttributeCombinationsById($idAttribute, $languageId);
                if (!empty($attributeCombinations)) {
                    $attributeCombinationNames = array();
                    foreach ($attributeCombinations as $attributeCombination) {
                        $attributeCombinationNames[] = $attributeCombination['attribute_name'];
                    }
                    if (!empty($attributeCombinationNames)) {
                        $productName .= ' (' . implode(', ', $attributeCombinationNames) . ')';
                    }
                }

                $purchasedItem = new NostoOrderPurchasedItem();
                $purchasedItem->setProductId((string)$p->id);
                $purchasedItem->setSkuId((string)$idAttribute);
                $purchasedItem->setQuantity((int)$item['product_quantity']);
                $purchasedItem->setName((string)$productName);
                $purchasedItem->setPrice($item['product_price_wt']);
                $purchasedItem->setPriceCurrencyCode((string)$currency->iso_code);
                $purchasedItems[] = $purchasedItem;
            }
        }

        if (!empty($purchasedItems)) {
            // Add special items for discounts, shipping and gift wrapping.

            if ($totalDiscountsTaxIncl > 0) {
                // Subtract possible gift product price from total as gifts are tagged with price zero (0).
                $totalDiscountsTaxIncl -= $totalGiftTaxIncl;
                if ($totalDiscountsTaxIncl > 0) {
                    $purchasedItem = new NostoOrderPurchasedItem();
                    $purchasedItem->setProductId(NostoSDKLineItem::PSEUDO_PRODUCT_ID);
                    $purchasedItem->setQuantity(1);
                    $purchasedItem->setName('Discount');
                    // Note the negative value.
                    $purchasedItem->setPrice(NostoHelperPrice::roundPrice(-$totalDiscountsTaxIncl, $currency));
                    $purchasedItem->setPriceCurrencyCode((string)$currency->iso_code);
                    $purchasedItems[] = $purchasedItem;
                }
            }

            // Check is free shipping applies to the cart.
            $freeShipping = false;
            if (isset($cartRules)) {
                foreach ($cartRules as $cartRule) {
                    if ((int)$cartRule['free_shipping']) {
                        $freeShipping = true;
                        break;
                    }
                }
            }

            if ($freeShipping && $totalShippingTaxIncl > 0 && $totalDiscountsTaxIncl > 0) {
                $totalDiscountsTaxIncl -= $totalShippingTaxIncl;
            }

            //deduct the gift product among from product among
            $totalProductTaxIncl -= $totalGiftTaxIncl;
            $discountPercentage = max(0, $totalDiscountsTaxIncl / $totalProductTaxIncl);

            /** @var NostoOrderPurchasedItem $purchasedItem */
            foreach ($purchasedItems as $purchasedItem) {
                if ($purchasedItem->getProductId() === NostoSDKLineItem::PSEUDO_PRODUCT_ID) {
                    continue;
                }

                $unitPrice = $purchasedItem->getUnitPrice() * (1 - $discountPercentage);
                $purchasedItem->setPrice(NostoHelperPrice::roundPrice($unitPrice, $currency));
            }

            if (!$freeShipping && $totalShippingTaxIncl > 0) {
                $purchasedItem = new NostoOrderPurchasedItem();
                $purchasedItem->setProductId(NostoSDKLineItem::PSEUDO_PRODUCT_ID);
                $purchasedItem->setQuantity(1);
                $purchasedItem->setName('Shipping');
                $purchasedItem->setPrice(NostoHelperPrice::roundPrice($totalShippingTaxIncl, $currency));
                $purchasedItem->setPriceCurrencyCode((string)$currency->iso_code);
                $purchasedItems[] = $purchasedItem;
            }

            if ($totalWrappingTaxIncl > 0) {
                $purchasedItem = new NostoOrderPurchasedItem();
                $purchasedItem->setProductId(NostoSDKLineItem::PSEUDO_PRODUCT_ID);
                $purchasedItem->setQuantity(1);
                $purchasedItem->setName('Gift Wrapping');
                $purchasedItem->setPrice(NostoHelperPrice::roundPrice($totalWrappingTaxIncl, $currency));
                $purchasedItem->setPriceCurrencyCode((string)$currency->iso_code);
                $purchasedItems[] = $purchasedItem;
            }
        }

        return $purchasedItems;
    }
}
