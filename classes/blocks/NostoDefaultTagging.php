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
     * Keeps the state of Nosto default tagging
     *
     * @var boolean
     */
    private static $taggingRendered = false;

    /**
     * Render page type tagging
     *
     * @param NostoTagging $module the instance of the module for rendering the template
     * @return string the rendered HTML
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
     * Generates the tagging based on controller
     *
     * @param NostoTagging $module the instance of the module for rendering the template
     * @return string
     */
    private static function generateDefaultTagging(NostoTagging $module)
    {
        if (!NostoHelperAccount::isContextConnected(Context::getContext())) {
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
        $html .= $module->display("NostoTagging.php", 'views/templates/hook/top_nosto-elements.tpl');
        $html .= $module->getHiddenRecommendationElements();

        return $html;
    }
}
