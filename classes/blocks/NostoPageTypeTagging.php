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
class NostoPageTypeTagging
{
    private static $controllers = array(
        "category" => "category",
        "manufacturer" => "category",
        "search" => "search",
        "product" => "product",
        "order-confirmation" => "order",
        "pagenotfound" => "notfound",
        "404" => "notfound",
        "index" => "front",
        "cart" => "cart"
    );

    /**
     * Renders the page-type tagging by checking the current controller's name against a list
     * of pre-defined page type and controller-name mappings
     *
     * @param NostoTagging $module the instance of the module for rendering the template
     * @return string the tagging
     */
    public static function get(NostoTagging $module)
    {
        if (!Nosto::isContextConnected()) {
            return '';
        }

        Context::getContext()->smarty->assign(array(
            'nosto_page_type' => self::$controllers[NostoHelperController::getControllerName()]
        ));

        return $module->render('views/templates/hook/top_page_type-tagging.tpl');
    }
}
