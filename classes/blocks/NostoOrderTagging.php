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
class OrderTagging
{

    /**
     * Tries to resolve current / active order confirmation in context
     *
     * @return Order|null
     */
    private static function resolveOrderInContext()
    {
        $order = null;
        if ($id_order = (int)Tools::getValue('id_order')) {
            $order = new Order($id_order);
        }
        if (
            $order instanceof Order === false
            || !Validate::isLoadedObject($order)
        ) {
            $order = null;
        }

        return $order;
    }

    /**
     * Render meta-data (tagging) for a completed order.
     *
     * @param NostoTagging $module the instance of the module for rendering the template
     * @return string The rendered HTML
     */
    public static function get(NostoTagging $module)
    {
        $order = self::resolveOrderInContext();
        if (!$order instanceof Order) {
            return null;
        }

        $nosto_order = new NostoTaggingOrder();
        $nosto_order->loadData(Context::getContext(), $order);

        Context::getContext()->smarty->assign(array(
            'nosto_order' => $nosto_order,
        ));

        return $module->display("NostoTagging.php", 'views/templates/hook/order-confirmation_order-tagging.tpl');
    }
}