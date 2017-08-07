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
    private static $tagging_rendered = false;

    /**
     * Render page type tagging
     *
     * @return string the rendered HTML
     */
    public static function get()
    {
        if (self::$tagging_rendered === false) {
            self::$tagging_rendered = true;
            $html = self::generateDefaultTagging();
        } else {
            $html = '';
        }

        return $html;
    }

    /**
     * Generates the tagging based on controller
     *
     * @return string
     */
    private function generateDefaultTagging()
    {
        if (!NostoTaggingHelperAccount::isContextConnected(Context::getContext())) {
            return '';
        }

        $html = '';
        $html .= $this->display(__FILE__, NostoCustomerTagging::get());
        $html .= $this->display(__FILE__, NostoCartTagging::get());
        $html .= $this->display(__FILE__, NostoVariationTagging::get());
        if (NostoHelperController::isController('category')) {
            $html .= $this->display(__FILE__, NostoCategoryTagging::get());
        } elseif (NostoHelperController::isController('manufacturer')) {
            $html .= $this->display(__FILE__, NostoBrandTagging::get());
        } elseif (NostoHelperController::isController('search')) {
            $html .= $this->display(__FILE__, NostoSearchTagging::get());
        } elseif (NostoHelperController::isController('product')) {
            $html .= $this->display(__FILE__, NostoProductTagging::get());
        } elseif (NostoHelperController::isController('order-confirmation')) {
            $html .= $this->display(__FILE__, OrderTagging::get());
        }
        $html .= $this->display(__FILE__, 'views/templates/hook/top_nosto-elements.tpl');
        $html .= $this->getHiddenRecommendationElements();

        return $html;
    }
}