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

use \Nosto\NostoException as NostoSDKException;
use \Nosto\Request\Api\Token as NostoSDKAPIToken;
use \Nosto\OAuth as NostoSDKOAuth;

class NostoOAuth extends NostoSDKOAuth
{
    /**
     * Loads meta data from the given context and language.
     *
     * @param Context $context the context to use as data source.
     * @param int $idLang the language to use as data source.
     * @param $moduleName
     * @return NostoOAuth|null
     */
    public static function loadData($context, $idLang, $moduleName)
    {
        $language = new Language($idLang);
        if (!Validate::isLoadedObject($language)) {
            return null;
        }

        $idLang = (int)$context->language->id;
        $idShop = (int)$context->shop->id;

        $nostoOAuth = new NostoOAuth();

        try {
            $nostoOAuth->setScopes(NostoSDKAPIToken::getApiTokenNames());

            $redirectUrl = NostoHelperUrl::getModuleUrl(
                $moduleName,
                'oauth2',
                $idLang,
                $idShop,
                array('language_id' => (int)$language->id)
            );

            $nostoOAuth->setClientId('prestashop');
            $nostoOAuth->setClientSecret('prestashop');
            $nostoOAuth->setRedirectUrl($redirectUrl);
            $nostoOAuth->setLanguageIsoCode($language->iso_code);
        } catch (NostoSDKException $e) {
            NostoHelperLogger::error($e);
        }

        return $nostoOAuth;
    }
}
