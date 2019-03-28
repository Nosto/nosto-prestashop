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

require_once(dirname(__FILE__) . '/api.php');

/**
 * Front controller for gathering all existing orders from the shop and sending the meta-data to
 * Nosto.
 *
 * This controller should only be invoked once, when the Nosto module has been installed.
 */
class NostoTaggingOrderModuleFrontController extends NostoTaggingApiModuleFrontController
{
    /**
     * @inheritdoc
     */
    public function initContent()
    {
        $collection = new Nosto\Object\Order\OrderCollection();

        $id = Tools::getValue(NostoTagging::ID);
        if (!empty($id)) {
            $orders = Order::getByReference($id);
            if ($orders->count() == 0) {
                Controller::getController('PageNotFoundController')->run();
            }
            $nostoOrder = NostoOrder::loadData($orders[0]);
            $collection->append($nostoOrder);
        } else {
            foreach ($this->getOrderIds() as $idOrder) {
                $order = new Order($idOrder);
                if (!Validate::isLoadedObject($order)) {
                    continue;
                }
                $nostoOrder = NostoOrder::loadData($order);
                $collection->append($nostoOrder);
            }
        }

        $this->encryptOutput($collection);
    }

    /**
     * Returns a list of all order ids with limit and offset applied.
     *
     * @return array the order id list.
     */
    protected function getOrderIds()
    {
        $where = sprintf(
            '`id_shop_group` = %s AND `id_shop` = %s AND `id_lang` = %s',
            pSQL((string)NostoHelperContext::getShopGroupId()),
            pSQL((string)NostoHelperContext::getShopId()),
            pSQL((string)NostoHelperContext::getLanguageId())
        );

        /** @noinspection SqlNoDataSourceInspection */
        $sql = sprintf(
            '
                SELECT id_order
                FROM %sorders
                WHERE %s
                ORDER BY date_add DESC
                LIMIT %d
                OFFSET %d
            ',
            pSQL(_DB_PREFIX_),
            $where,
            $this->limit,
            $this->offset
        );

        $rows = Db::getInstance()->executeS($sql);
        $orderIds = array();
        foreach ($rows as $row) {
            $orderIds[] = (int)$row['id_order'];
        }

        return $orderIds;
    }
}
