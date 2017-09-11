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
 * Helper class for sending order data to Nosto.
 */
class NostoTaggingHelperOrderOperation extends NostoTaggingHelperOperation
{
    /**
     * Flag for disabling inventory sync for slow connections
     *
     * @var bool
     */
    public static $syncInventoriesAfterOrder = true;

    /**
     * Sends order data to Nosto.
     *
     * @param Order $order
     * @param Context $context
     */
    public function send(Order $order, Context $context)
    {
        $nosto_order = new NostoTaggingOrder();
        $nosto_order->loadData($context, $order);
        $id_shop_group = isset($order->id_shop_group) ? $order->id_shop_group : null;
        $id_shop = isset($order->id_shop) ? $order->id_shop : null;
        // This is done out of context, so we need to specify the exact parameters to get the correct account.
        /** @var NostoAccount $account */
        $account = NostoTaggingHelperAccount::find($order->id_lang, $id_shop_group, $id_shop);
        if ($account !== null && $account->isConnectedToNosto()) {
            /* @var NostoTaggingHelperCustomer $helper_customer */
            $helper_customer = Nosto::helper('nosto_tagging/customer');
            $customer_id = $helper_customer->getNostoId($order);
            try {
                NostoOrderConfirmation::send($nosto_order, $account, $customer_id);
                try {
                    $this->syncInventoryLevel($nosto_order);
                } catch (NostoException $e) {
                    /* @var NostoTaggingHelperLogger $logger */
                    $logger = Nosto::helper('nosto_tagging/logger');
                    $logger->error(
                        'Failed to synchronize products after order: %s',
                        $e->getMessage()
                    );
                }
            } catch (NostoException $e) {
                /* @var NostoTaggingHelperLogger $logger */
                $logger = Nosto::helper('nosto_tagging/logger');
                $logger->error(
                    'Failed to send order confirmation: %s',
                    $e->getMessage()
                );
            }
        }
    }

    /**
     * Sends product updates to Nosto to keep up with the inventory level
     *
     * @param NostoTaggingOrder $order
     */
    private function syncInventoryLevel(NostoTaggingOrder $order)
    {
        if (self::$syncInventoriesAfterOrder === true) {
            $purchasedtems = $order->getPurchasedItems();
            $products = array();
            /* @var NostoOrderPurchasedItem $item */
            foreach ($purchasedtems as $item) {
                $productId = $item->getProductId();
                if (empty($productId) || $productId < 0) {
                    continue;
                }
                $product = new Product($productId);
                if ($product instanceof Product) {
                    $products[] = $product;
                }
            }
            /* @var $nostoProductOperation NostoTaggingHelperProductOperation */
            $nostoProductOperation = Nosto::helper('nosto_tagging/product_operation');
            $nostoProductOperation->updateBatch($products);
        }
    }
}
