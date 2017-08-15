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
class NostoDefaultTagging
{

    /**
     * @var boolean keeps the state of Nosto default tagging
     */
    private static $taggingRendered = false;

    /**
     * Renders the main tagging by if it hasn't already been rendered. This acts as a safeguard
     * so that if the top hook isn't fired, the tagging might be rendered in the bottom hook.
     *
     * @param NostoTagging $module the instance of the module for rendering the template
     * @return string the tagging
     */
    public static function get(NostoTagging $module)
    {
        if (self::$taggingRendered === false) {
            self::$taggingRendered = true;
            $html = self::generateDefaultTagging($module);
        } else {
            $html = '';
        }

        return $html;
    }

    /**
     * Renders the main tagging by checking the controller name and delegating accordingly
     *
     * @param NostoTagging $module the instance of the module for rendering the template
     * @return string the tagging
     */
    private static function generateDefaultTagging(NostoTagging $module)
    {
        if (!Nosto::isContextConnected()) {
            return '';
        }

        $html = '';
        $html .= NostoCustomerTagging::get($module);
        $html .= NostoCartTagging::get($module);
        $html .= NostoVariationTagging::get($module);
        if (NostoHelperController::isController('category')) {
            $html .= NostoCategoryTagging::get($module);
        } elseif (NostoHelperController::isController('manufacturer')) {
            $html .= NostoBrandTagging::get($module);
        } elseif (NostoHelperController::isController('search')) {
            $html .= NostoSearchTagging::get($module);
        } elseif (NostoHelperController::isController('product')) {
            $html .= NostoProductTagging::get($module);
        } elseif (NostoHelperController::isController('order-confirmation')) {
            $html .= NostoOrderTagging::get($module);
        }
        $html .= $module->render('views/templates/hook/top_nosto-elements.tpl');
        $html .= NostoHeaderContent::getHiddenRecommendationElements($module);

        return $html;
    }
}
