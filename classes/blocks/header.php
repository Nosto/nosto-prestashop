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

class NostoHeaderContent {

    public static function get() {
        $account = NostoTaggingHelperAccount::findByContext(Context::getContext());
        if ($account === null) {
            return '';
        }

        /** @var NostoTaggingHelperUrl $url_helper */
        $url_helper = Nosto::helper('nosto_tagging/url');
        $server_address = $url_helper->getServerAddress();
        /** @var LinkCore $link */
        $link = NostoTaggingHelperLink::getLink();
        $hidden_recommendation_elements = $this->getHiddenRecommendationElements();
        Context::getContext()->smarty->assign(array(
            'server_address' => $server_address,
            'account_name' => $account->getName(),
            'nosto_version' => $this->version,
            'nosto_unique_id' => $this->getUniqueInstallationId(),
            'nosto_language' => Tools::strtolower($this->context->language->iso_code),
            'add_to_cart_url' => $link->getPageLink('cart.php'),
            'static_token' => Tools::getToken(false),
            'disable_autoload' => (bool)!empty($hidden_recommendation_elements)
        ));

        $html = $this->display(__FILE__, 'views/templates/hook/header_meta-tags.tpl');
        $html .= $this->display(__FILE__, 'views/templates/hook/header_embed-script.tpl');
        $html .= $this->display(__FILE__, 'views/templates/hook/header_add-to-cart.tpl');
        $html .= PageTypeTagging::get();

        return $html;
    }
}