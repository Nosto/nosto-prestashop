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

use \Nosto\Object\Order\OrderStatus as NostoSDKOrderStatus;

class NostoOrderStatus extends NostoSDKOrderStatus
{
    /**
     * Loads the order status data from the order model.
     *
     * @param Order $order the model.
     * @return NostoOrderStatus
     */
    public static function loadData(Order $order)
    {
        $status = new NostoOrderStatus();
        // We prefer to use the English state name for the status code, as we
        // use it as an unique identifier of that particular order status.
        // The status label will primarily be in the language of the order.
        $idLang = (int)Language::getIdByIso('en');
        if (empty($idLang)) {
            $idLang = (int)$order->id_lang;
        }

        $state = $order->getCurrentStateFull($idLang);
        if (!empty($state['name'])) {
            $stateName = $state['name'];
            $status->setCode(self::convertNameToCode($stateName));
            if ($idLang !== (int)$order->id_lang) {
                $state = $order->getCurrentStateFull((int)$order->id_lang);
                if (!empty($state['name'])) {
                    $stateName = $state['name'];
                }
            }
            $status->setLabel($stateName);
        }
        return $status;
    }

    /**
     * Converts a human readable name to a machine readable name,
     * i.e. converts the name to a lower case alphanumeric string.
     *
     * @param string $name the name to convert.
     * @return string the converted name.
     */
    private static function convertNameToCode($name)
    {
        $pattern = array('/[^a-zA-Z0-9]+/', '/_+/', '/^_+/', '/_+$/');
        $replacement = array('_', '_', '', '');
        return Tools::strtolower(preg_replace($pattern, $replacement, $name));
    }
}
