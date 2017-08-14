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
    const DEFAULT_SERVER_ADDRESS = 'connect.nosto.com';

    /**
     * Returns hidden nosto recommendation elements for the current controller.
     * These are used as a fallback for showing recommendations if the appropriate hooks are not
     * present in the theme. The hidden elements are put into place and shown in the shop with
     * JavaScript.
     *
     * @param NostoTagging $module
     * @return string the html.
     */
    public static function getHiddenRecommendationElements(NostoTagging $module)
    {
        if (NostoHelperController::isController('index')) {
            return $module->render('views/templates/hook/home_hidden-nosto-elements.tpl');
        } elseif (NostoHelperController::isController('product')) {
            return $module->render('views/templates/hook/footer-product_hidden-nosto-elements.tpl');
        } elseif (NostoHelperController::isController('order') && (int)Tools::getValue('step', 0) === 0
        ) {
            return $module->render('views/templates/hook/shopping-cart-footer_hidden-nosto-elements.tpl');
        } elseif (NostoHelperController::isController('category')
            || NostoHelperController::isController('manufacturer')
        ) {
            return $module->render('views/templates/hook/category-footer_hidden-nosto-elements.tpl');
        } elseif (NostoHelperController::isController('search')) {
            return $module->render('views/templates/hook/search_hidden-nosto-elements.tpl');
        } elseif (NostoHelperController::isController('pagenotfound')
            || NostoHelperController::isController('404')
        ) {
            return $module->render('views/templates/hook/404_hidden_nosto-elements.tpl');
        } elseif (NostoHelperController::isController('order-confirmation')) {
            return $module->render('views/templates/hook/order-confirmation_hidden_nosto-elements.tpl');
        } else {
            // If the current page is not one of the ones we want to show recommendations on, just
            // return empty.
            return '';
        }
    }

    /**
     * Get the Nosto server address for the shop frontend JavaScripts.
     *
     * @return string the url.
     */
    public static function getServerAddress()
    {
        return NostoSDK::getEnvVariable('NOSTO_SERVER_URL', self::DEFAULT_SERVER_ADDRESS);
    }

    /**
     * Renders the meta and script tagging by checking the version, the language and the URL
     * of the add-to-cart controller
     *
     * @param NostoTagging $module the instance of the module for rendering the template
     * @return string the tagging
     */
    public static function get(NostoTagging $module)
    {
        $account = Nosto::getAccount();
        if ($account === null) {
            return '';
        }

        $serverAddress = self::getServerAddress();
        $link = NostoHelperLink::getLink();
        $hiddenElements = self::getHiddenRecommendationElements($module);
        Context::getContext()->smarty->assign(array(
            'server_address' => $serverAddress,
            'account_name' => $account->getName(),
            'nosto_version' => $module->version,
            'nosto_language' => Tools::strtolower(Context::getContext()->language->iso_code),
            'add_to_cart_url' => $link->getPageLink('NostoCart.php'),
            'static_token' => Tools::getToken(false),
            'disable_autoload' => (bool)!empty($hiddenElements)
        ));

        $html = $module->render('views/templates/hook/header_meta-tags.tpl');
        $html .= $module->render('views/templates/hook/header_embed-script.tpl');
        $html .= $module->render('views/templates/hook/header_add-to-cart.tpl');
        $html .= NostoPageTypeTagging::get($module);

        return $html;
    }
}
