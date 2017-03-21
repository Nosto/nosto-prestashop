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
 * Model for tagging orders.
 */
class NostoTaggingOrder extends NostoTaggingModel implements NostoOrderInterface
{
    /**
     * @var bool if we should include special line items such as discounts and shipping costs.
     */
    public $include_special_items = true;

    /**
     * @var string the order number.
     */
    protected $order_number;

    /**
     * @var NostoTaggingOrderBuyer buyer info.
     */
    protected $buyer_info = array();

    /**
     * @var string the order creation date.
     */
    protected $created_date;

    /**
     * @var NostoTaggingOrderPurchasedItem[] purchased items in the order.
     */
    protected $purchased_items = array();

    /**
     * @var string the payment provider module and version used in the order.
     */
    protected $payment_provider;

    /**
     * @var string external order reference.
     */
    protected $external_order_ref;

    /**
     * @var NostoTaggingOrderStatus the order status.
     */
    protected $order_status;

    /**
     * @inheritdoc
     */
    public function getOrderNumber()
    {
        return $this->order_number;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedDate()
    {
        return $this->created_date;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentProvider()
    {
        return $this->payment_provider;
    }

    /**
     * @inheritdoc
     */
    public function getBuyerInfo()
    {
        return $this->buyer_info;
    }

    /**
     * @inheritdoc
     */
    public function getPurchasedItems()
    {
        return $this->purchased_items;
    }

    /**
     * @inheritdoc
     */
    public function getOrderStatus()
    {
        return $this->order_status;
    }

    /**
     * Sets the unique order number identifying the order.
     *
     * The number must be a non-empty value.
     *
     * @param string|int $order_number the order number.
     */
    public function setOrderNumber($order_number)
    {
        $this->order_number = $order_number;
    }

    /**
     * Sets the date when the order was placed.
     *
     * The date must be an instance of the NostoDate class.
     *
     * @param string $created_date the creation date.
     */
    public function setCreatedDate($created_date)
    {
        $this->created_date = $created_date;
    }

    /**
     * Sets the payment provider used for placing the order.
     *
     * The provider must be a non-empty string value. Preferred formatting is "[provider name] [provider version]".
     *
     * @param string $payment_provider the payment provider.
     */
    public function setPaymentProvider($payment_provider)
    {
        $this->payment_provider = $payment_provider;
    }

    /**
     * Sets the buyer info of the user who placed the order.
     *
     * The info object must implement the NostoOrderBuyerInterface interface.
     *
     * @param NostoOrderBuyerInterface $buyer_info the buyer info object.
     */
    public function setBuyerInfo(NostoOrderBuyerInterface $buyer_info)
    {
        $this->buyer_info = $buyer_info;
    }

    /**
     * Adds a purchased item to the order.
     *
     * The item object must implement the NostoOrderItemInterface interface.
     *
     * @param NostoOrderPurchasedItemInterface $purchased_item the item object.
     */
    public function addPurchasedItem(NostoOrderPurchasedItemInterface $purchased_item)
    {
        $this->purchased_items[] = $purchased_item;
    }

    /**
     * Sets the order status.
     *
     * The status object must implement the NostoOrderStatusInterface interface.
     *
     * @param NostoOrderStatusInterface $order_status the status object.
     */
    public function setOrderStatus(NostoOrderStatusInterface $order_status)
    {
        $this->order_status = $order_status;
    }

    /**
     * Gets the external order ref
     *
     * @return string
     */
    public function getExternalOrderRef()
    {
        return $this->external_order_ref;
    }

    /**
     * Sets the external order ref
     *
     * @param string $external_order_ref
     */
    public function setExternalOrderRef($external_order_ref)
    {
        $this->external_order_ref = $external_order_ref;
    }

    /**
     * Loads the order data from supplied context and order objects.
     *
     * @param Context $context the context object.
     * @param Order $order the order object.
     */
    public function loadData(Context $context, Order $order)
    {
        if (!Validate::isLoadedObject($order)) {
            return;
        }

        /** @var NostoHelperDate $nosto_helper_date */
        $nosto_helper_date = Nosto::helper('nosto/date');

        $customer = new Customer((int)$order->id_customer);
        // The order reference was introduced in prestashop 1.5 where orders can be split into multiple ones.
        if (isset($order->reference)) {
            $this->order_number = $order->reference;
            $this->external_order_ref = $order->id;
        } else {
            $this->order_number = $order->id;
        }
        $this->order_number = isset($order->reference) ? (string)$order->reference : $order->id;
        $this->buyer_info = new NostoTaggingOrderBuyer();
        $this->buyer_info->loadData($customer);
        $this->created_date = $nosto_helper_date->format($order->date_add);
        $this->purchased_items = $this->findPurchasedItems($context, $order);
        $this->payment_provider = 'unknown';

        if (!empty($order->module)) {
            $payment_module = Module::getInstanceByName($order->module);
            if ($payment_module !== false && isset($payment_module->version)) {
                $this->payment_provider = $order->module.' ['.$payment_module->version.']';
            } else {
                $this->payment_provider = $order->module.' [unknown]';
            }
        }

        $this->order_status = new NostoTaggingOrderStatus();
        $this->order_status->loadData($order);

        $this->dispatchHookActionLoadAfter(array(
            'nosto_order' => $this,
            'order' => $order,
            'context' => $context
        ));
    }

    /**
     * Finds purchased items for the order.
     *
     * @param Context $context the context.
     * @param Order $order the order object.
     * @return NostoTaggingOrderPurchasedItem[] the purchased items.
     */
    protected function findPurchasedItems(Context $context, Order $order)
    {
        $purchased_items = array();

        $currency = new Currency($order->id_currency);
        if (!Validate::isLoadedObject($currency)) {
            return $purchased_items;
        }

        $products = array();
        $total_discounts_tax_incl = 0;
        $total_shipping_tax_incl = 0;
        $total_wrapping_tax_incl = 0;
        $total_gift_tax_incl = 0;

        // One order can be split into multiple orders, so we need to combine their data.
        $order_collection = Order::getByReference($order->reference);
        foreach ($order_collection as $item) {
            /** @var $item Order */
            $products = array_merge($products, $item->getProducts());
            $total_discounts_tax_incl = Tools::ps_round(
                $total_discounts_tax_incl + $item->total_discounts_tax_incl,
                2
            );
            $total_shipping_tax_incl = Tools::ps_round(
                $total_shipping_tax_incl + $item->total_shipping_tax_incl,
                2
            );
            $total_wrapping_tax_incl = Tools::ps_round(
                $total_wrapping_tax_incl + $item->total_wrapping_tax_incl,
                2
            );
        }

        // We need the cart rules used for the order to check for gift products and free shipping.
        // The cart is the same even if the order is split into many objects.
        $cart = new Cart($order->id_cart);
        if (Validate::isLoadedObject($cart)) {
            $cart_rules = (array)$cart->getCartRules();
        } else {
            $cart_rules = array();
        }

        $gift_products = array();
        foreach ($cart_rules as $cart_rule) {
            if ((int)$cart_rule['gift_product']) {
                foreach ($products as $key => &$product) {
                    if (empty($product['gift'])
                    && (int)$product['product_id'] === (int)$cart_rule['gift_product']
                    && (int)$product['product_attribute_id'] === (int)$cart_rule['gift_product_attribute']) {
                        $product['product_quantity'] = (int)$product['product_quantity'];
                        $product['product_quantity']--;
                        if (!($product['product_quantity'] > 0)) {
                            unset($products[$key]);
                        }
                        if (isset($product['product_price_wt'])) {
                            $product_price_wt = $product['product_price_wt'];
                        } else {
                            $product_price_wt = 0;
                        }
                        $total_gift_tax_incl = Tools::ps_round(
                            $total_gift_tax_incl + $product_price_wt,
                            2
                        );
                        $gift_product = $product;
                        $gift_product['product_quantity'] = 1;
                        $gift_product['product_price_wt'] = 0;
                        $gift_product['gift'] = true;
                        $gift_products[] = $gift_product;

                        break; // One gift product per cart rule
                    }
                }
                unset($product);
            }
        }
        $items = array_merge($products, $gift_products);

        if (!$context) {
            $context = Context::getContext();
        }

        /** @var NostoHelperPrice $nosto_helper_price */
        $nosto_helper_price = Nosto::helper('nosto/price');

        $id_lang = (int)$context->language->id;
        foreach ($items as $item) {
            $p = new Product($item['product_id'], false, $context->language->id);
            if (Validate::isLoadedObject($p)) {
                $product_name = $p->name;
                $id_attribute = (int)$item['product_attribute_id'];
                $attribute_combinations = $this->getProductAttributeCombinationsById($p, $id_attribute, $id_lang);
                if (!empty($attribute_combinations)) {
                    $attribute_combination_names = array();
                    foreach ($attribute_combinations as $attribute_combination) {
                        $attribute_combination_names[] = $attribute_combination['attribute_name'];
                    }
                    if (!empty($attribute_combination_names)) {
                        $product_name .= ' ('.implode(', ', $attribute_combination_names).')';
                    }
                }

                $purchased_item = new NostoTaggingOrderPurchasedItem();
                $purchased_item->setProductId((int)$p->id);
                $purchased_item->setQuantity((int)$item['product_quantity']);
                $purchased_item->setName((string)$product_name);
                $purchased_item->setUnitPrice($nosto_helper_price->format($item['product_price_wt']));
                $purchased_item->setCurrencyCode((string)$currency->iso_code);
                $purchased_items[] = $purchased_item;
            }
        }

        if ($this->include_special_items && !empty($purchased_items)) {
        // Add special items for discounts, shipping and gift wrapping.

            if ($total_discounts_tax_incl > 0) {
            // Subtract possible gift product price from total as gifts are tagged with price zero (0).
                $total_discounts_tax_incl = Tools::ps_round($total_discounts_tax_incl - $total_gift_tax_incl, 2);
                if ($total_discounts_tax_incl > 0) {
                    $purchased_item = new NostoTaggingOrderPurchasedItem();
                    $purchased_item->setProductId(-1);
                    $purchased_item->setQuantity(1);
                    $purchased_item->setName('Discount');
                    // Note the negative value.
                    $purchased_item->setUnitPrice($nosto_helper_price->format(-$total_discounts_tax_incl));
                    $purchased_item->setCurrencyCode((string)$currency->iso_code);
                    $purchased_items[] = $purchased_item;
                }
            }

            // Check is free shipping applies to the cart.
            $free_shipping = false;
            if (isset($cart_rules)) {
                foreach ($cart_rules as $cart_rule) {
                    if ((int)$cart_rule['free_shipping']) {
                        $free_shipping = true;
                        break;
                    }
                }
            }

            if (!$free_shipping && $total_shipping_tax_incl > 0) {
                $purchased_item = new NostoTaggingOrderPurchasedItem();
                $purchased_item->setProductId(-1);
                $purchased_item->setQuantity(1);
                $purchased_item->setName('Shipping');
                $purchased_item->setUnitPrice($nosto_helper_price->format($total_shipping_tax_incl));
                $purchased_item->setCurrencyCode((string)$currency->iso_code);
                $purchased_items[] = $purchased_item;
            }

            if ($total_wrapping_tax_incl > 0) {
                $purchased_item = new NostoTaggingOrderPurchasedItem();
                $purchased_item->setProductId(-1);
                $purchased_item->setQuantity(1);
                $purchased_item->setName('Gift Wrapping');
                $purchased_item->setUnitPrice($nosto_helper_price->format($total_wrapping_tax_incl));
                $purchased_item->setCurrencyCode((string)$currency->iso_code);
                $purchased_items[] = $purchased_item;
            }
        }

        return $purchased_items;
    }

    /**
     * Returns the product attribute combination by id_product_attribute.
     *
     * @param Product|ProductCore $product the product model.
     * @param int $id_product_attribute the product attribute ID.
     * @param int $id_lang the language ID.
     * @return array the attribute combinations.
     */
    protected function getProductAttributeCombinationsById($product, $id_product_attribute, $id_lang)
    {
        return $product->getAttributeCombinationsById($id_product_attribute, $id_lang);
    }
}
