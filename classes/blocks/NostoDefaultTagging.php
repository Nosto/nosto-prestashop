<?php

use Nosto\NostoException;

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

class NostoDefaultTagging
{
    /**
     * Renders the main tagging by if it hasn't already been rendered. This acts as a safeguard
     * so that if the top hook isn't fired, the tagging might be rendered in the bottom hook.
     *
     * @param NostoTagging $module the instance of the module for rendering the template
     * @return string the tagging
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws ReflectionException
     * @throws NostoException
     */
    public static function get(NostoTagging $module)
    {
        if (!NostoHelperAccount::existsAndIsConnected()) {
            return '';
        }

        $html = '';
        $html .= NostoPageTypeTagging::get();
        $html .= NostoCustomerTagging::get();
        $html .= NostoCartTagging::get();
        $html .= NostoVariationTagging::get();
        if (NostoHelperController::isController('category')) {
            $html .= NostoCategoryTagging::get();
        } elseif (NostoHelperController::isController('manufacturer')) {
            $html .= NostoBrandTagging::get();
        } elseif (NostoHelperController::isController('search')) {
            $html .= NostoSearchTagging::get();
        } elseif (NostoHelperController::isController('product')) {
            $html .= NostoProductTagging::get();
        } elseif (NostoHelperController::isController('order-confirmation')) {
            $html .= NostoOrderTagging::get();
        }
        $html .= $module->render('views/templates/hook/top_nosto-elements.tpl');

        return $html;
    }
}
