<?php
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
 * @copyright 2013-2019 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\NostoException as NostoSDKException;

class NostoTaggingRestoreCartModuleFrontController extends ModuleFrontController
{
    const CART_CONTROLLER = 'cart';
    const ID_CART = 'id_cart';
    const QUERY_STRING = 'QUERY_STRING';
    const ACTION = 'action';
    const SHOW = 'show';

    /**
     * @inheritdoc
     */
    public function initContent()
    {
        $redirectUrl = NostoHelperUrl::getContextShopUrl();
        $query = $_SERVER[self::QUERY_STRING];
        $urlParameters = array();
        parse_str($query, $urlParameters);
        unset($urlParameters[NostoCart::HASH_PARAM]);
        $urlParameters = $this->removePrestashopParams($urlParameters);
        $urlParameters[self::ACTION] = self::SHOW;

        if (NostoHelperAccount::getAccount() !== null) {
            if (Validate::isLoadedObject(NostoHelperContext::getCart())
                && NostoHelperContext::getCart()->getProducts()
            ) {
                $redirectUrl = NostoHelperUrl::getPageUrl(self::CART_CONTROLLER, $urlParameters);
            } else {
                $restoreCartHash = Tools::getValue(NostoCart::HASH_PARAM);
                if (!$restoreCartHash) {
                    NostoHelperLogger::error(new NostoSDKException('No hash provided for restore cart'));
                } else {
                    try {
                        $cartId = NostoCustomerManager::getCartId($restoreCartHash);
                        $newCart = new Cart($cartId);
                        //restore the cart only if it had not been ordered yet
                        if (Validate::isLoadedObject($newCart) && !$newCart->orderExists()) {
                            NostoHelperContext::setCookieValue(self::ID_CART, $cartId);
                            $redirectUrl = NostoHelperUrl::getPageUrl(self::CART_CONTROLLER, $urlParameters);
                        }
                    } catch (Exception $e) {
                        NostoHelperLogger::error($e);
                        NostoHelperFlash::add(
                            'error',
                            $this->module->l('Sorry, we could not find your cart')
                        );
                    }
                }
            }
        }
        Tools::redirect($redirectUrl);
    }

    /**
     * Remove the prestashop parameters from the parameters list
     *
     * @param $urlParameters
     * @return array return an array without the prestashop parameters
     */
    private function removePrestashopParams($urlParameters)
    {
        $pretashopParamKeys = array_fill_keys(array('fc', 'module', 'controller', 'id_lang'), null);

        return array_diff_key($urlParameters, $pretashopParamKeys);
    }
}
