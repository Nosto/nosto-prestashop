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
class NostoHeaderContent
{

    /**
     * @param NostoTagging $module the instance of the module for rendering the template
     * @return string
     */
    public static function get(NostoTagging $module)
    {
        $account = NostoHelperAccount::findByContext(Context::getContext());
        if ($account === null) {
            return '';
        }

        $server_address = NostoHelperUrl::getServerAddress();
        /** @var LinkCore $link */
        $link = NostoHelperLink::getLink();
        $hidden_recommendation_elements = $module->getHiddenRecommendationElements();
        Context::getContext()->smarty->assign(array(
            'server_address' => $server_address,
            'account_name' => $account->getName(),
            'nosto_version' => $module->version,
            'nosto_language' => Tools::strtolower(Context::getContext()->language->iso_code),
            'add_to_cart_url' => $link->getPageLink('cart.php'),
            'static_token' => Tools::getToken(false),
            'disable_autoload' => (bool)!empty($hidden_recommendation_elements)
        ));

        $html = $module->display("NostoTagging.php", 'views/templates/hook/header_meta-tags.tpl');
        $html .= $module->display("NostoTagging.php", 'views/templates/hook/header_embed-script.tpl');
        $html .= $module->display("NostoTagging.php", 'views/templates/hook/header_add-to-cart.tpl');
        $html .= NostoPagetypeTagging::get($module);

        return $html;
    }
}