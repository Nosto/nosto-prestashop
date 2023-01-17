<?php /** @noinspection PhpUnused */

/**
 * 2013-2022 Nosto Solutions Ltd
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
 * @copyright 2013-2022 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\Nosto as NostoSDK;
use Nosto\NostoException;

class NostoHeaderContent
{
    const DEFAULT_SERVER_ADDRESS = 'connect.nosto.com';

    /**
     * Renders the meta and script tagging by checking the version, the language and the URL
     * of the add-to-cart controller
     *
     * @param NostoTagging $module the instance of the module for rendering the template
     * @return string the tagging
     * @throws NostoException
     */
    public static function get(NostoTagging $module)
    {
        $account = NostoHelperAccount::getAccount();
        if ($account === null) {
            return '';
        }

        $serverAddress = NostoSDK::getServerUrl();
        $link = NostoHelperLink::getLink();
        Context::getContext()->smarty->assign(array(
            'server_address' => $serverAddress,
            'account_name' => $account->getName(),
            'nosto_version' => $module->version,
            'nosto_language' => Tools::strtolower(NostoHelperContext::getLanguage()->iso_code),
            'add_to_cart_url' => $link->getPageLink('cart.php'),
            'reload_cart_url' => NostoHelperUrl::getModuleUrl(
                NostoTagging::MODULE_NAME,
                'reloadCart'
            ),
            'static_token' => Tools::getToken(false)
        ));

        $html = $module->render('views/templates/hook/header_meta-tags.tpl');
        $html .= $module->render('views/templates/hook/header_embed-script.tpl');
        $html .= $module->render('views/templates/hook/header_add-to-cart.tpl');
        $html .= $module->render('views/templates/hook/header_prestashop-add-to-cart-event-handler.tpl');

        return $html;
    }
}
