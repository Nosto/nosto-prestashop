<?php
/**
 * 2013-2017 Nosto Solutions Ltd
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
 * @copyright 2013-2017 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\Operation\OrderConfirm as NostoSDKOrderConfirmOperation;

/**
 * Helper class for sending order data to Nosto.
 */
class NostoOrderService extends AbstractNostoService
{
    public static $syncInventoriesAfterOrder = true;

    public function send($params)
    {
        if (isset($params['id_order'])) {
            $order = new Order($params['id_order']);
            if ($order instanceof Order === false) {
                return;
            }
            $this->sendOrder($order);
        }
    }

    /**
     * Sends order data to Nosto.
     *
     * @param Order $order
     *
     * @suppress PhanTypeMismatchArgument
     */
    public function sendOrder(Order $order)
    {
        NostoHelperContext::runWithEachNostoAccount(function () use ($order) {
            // We need to forge the employee in order to get a price for a product
            $employeeId = false;
            if (!is_object(Context::getContext()->employee) && !is_object(Context::getContext()->cart)) {
                //if employee is null and cart is null, new Product() kills the process. (SoNice issue)
                $employeeId = 0;
            }
            NostoHelperContext::runInContext(
                function () use ($order) {
                    try {
                        $nostoOrder = NostoOrder::loadData($order);
                        $account = NostoHelperAccount::find();
                        if ($account !== null && $account->isConnectedToNosto()) {
                            $customerId = NostoCustomerManager::getNostoId($order);

                            $operation = new NostoSDKOrderConfirmOperation($account);
                            $operation->send($nostoOrder, $customerId);
                            try {
                                if (self::$syncInventoriesAfterOrder === true) {
                                    $purchasedItems = $nostoOrder->getPurchasedItems();
                                    $products = array();
                                    foreach ($purchasedItems as $item) {
                                        $productId = $item->getProductId();
                                        if (empty($productId) || $productId < 0) {
                                            continue;
                                        }
                                        $product = new Product($productId);
                                        if ($product instanceof Product) {
                                            $products[] = $product;
                                        }
                                    }
                                    $nostoProductOperation = new NostoProductService();
                                    $nostoProductOperation->updateBatch($products);
                                }
                            } catch (Exception $e) {
                                NostoHelperLogger::error($e, 'Failed to synchronize products after order');
                            }
                        }
                    } catch (Exception $e) {
                        NostoHelperLogger::error($e, 'Failed to send order confirmation');
                    }
                },
                false,
                false,
                false,
                $employeeId
            );
        });
    }
}
