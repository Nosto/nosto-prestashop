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
 * @copyright 2013-2020 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\NostoException;
use Nosto\Request\Http\HttpRequest as NostoSDKHttpRequest;
use Nosto\Helper\UrlHelper;

class NostoHelperUrl
{
    const PS_REWRITING_SETTINGS = 'PS_REWRITING_SETTINGS';
    const ID_LANG = 'id_lang';
    const ID_PRODUCT = "id_product";
    const PS_SSL_ENABLED = 'PS_SSL_ENABLED';
    const PS_MULTISHOP_FEATURE_ACTIVE = 'PS_MULTISHOP_FEATURE_ACTIVE';

    /**
     * Builds a product page url for the language and shop.
     *
     * We created our own method due to the existing one in `Link` behaving differently across
     * PS versions.
     *
     * @param Product $product
     * @param array $params additional params to add to the url.
     * @param int|null $productAttributeId product attribute id
     * @return string the product page url.
     * @throws PrestaShopException
     * @suppress PhanTypeMismatchArgument
     */
    public static function getProductUrl($product, array $params = array(), $productAttributeId = 0)
    {
        $idLang = NostoHelperContext::getLanguageId();
        $idShop = NostoHelperContext::getShopId();

        $url = NostoHelperLink::getLink()->getProductLink(
            $product,
            null,
            null,
            null,
            $idLang,
            $idShop,
            $productAttributeId,
            false,
            false,
            true
        );
        if ((int)Configuration::get(self::PS_REWRITING_SETTINGS) === 0) {
            $params[self::ID_LANG] = $idLang;
        }

        return NostoSDKHttpRequest::replaceQueryParamsInUrl($params, $url);
    }

    /**
     * Builds a category page url for the language and shop.
     *
     * We created our own method due to the existing one in `Link` behaving differently across
     * PS versions.
     *
     * @param Category|CategoryCore $category the category model.
     * @param array $params additional params to add to the url.
     * @return string the category page url.
     * @suppress PhanTypeMismatchArgument
     */
    public static function getCategoryUrl($category, array $params = array())
    {
        $idLang = NostoHelperContext::getLanguageId();
        $idShop = NostoHelperContext::getShopId();

        $url = NostoHelperLink::getLink()->getCategoryLink($category, null, $idLang, null, $idShop);
        if ((int)Configuration::get(self::PS_REWRITING_SETTINGS) === 0) {
            $params[self::ID_LANG] = $idLang;
        }

        return NostoSDKHttpRequest::replaceQueryParamsInUrl($params, $url);
    }

    /**
     * Builds a page url for the language and shop.
     *
     * We created our own method due to the existing one in `Link` behaving differently across
     * PS versions.
     *
     * @param string $controller the controller name.
     * @param array $params additional params to add to the url.
     * @return string the page url.
     * @suppress PhanTypeMismatchArgument
     */
    public static function getPageUrl($controller, array $params = array())
    {
        $idLang = NostoHelperContext::getLanguageId();
        $idShop = NostoHelperContext::getShopId();

        $url = NostoHelperLink::getLink()->getPageLink($controller, true, $idLang, null, false, $idShop);

        if ((int)Configuration::get(self::PS_REWRITING_SETTINGS) === 0) {
            $params[self::ID_LANG] = $idLang;
        }

        return NostoSDKHttpRequest::replaceQueryParamsInUrl($params, $url);
    }

    /**
     * Builds a module controller url for the language and shop.
     *
     * We created our own method due to the existing one in `Link` behaving differently across
     * PS versions.
     *
     * @param string $name the name of the module to create an url for.
     * @param string $controller the name of the controller.
     * @param array $params additional params to add to the url.
     * @return string the url.
     */
    public static function getModuleUrl($name, $controller, array $params = array())
    {
        $idLang = NostoHelperContext::getLanguageId();
        $idShop = NostoHelperContext::getShopId();

        $params['module'] = $name;
        $params['controller'] = $controller;

        if (version_compare(_PS_VERSION_, '1.5.5.0') === -1) {
            // For PS versions 1.5.0.0 - 1.5.4.1 we always hard-code the urls to be in non-friendly format and fetch
            // the shops base url ourselves. This is a workaround to all the bugs related to url building in these
            // PS versions.
            $params['fc'] = 'module';
            $params['module'] = $name;
            $params['controller'] = $controller;
            $params[self::ID_LANG] = $idLang;
            return self::getBaseUrl() . 'index.php?' . http_build_query($params);
        } else {
            $link = NostoHelperLink::getLink();
            return $link->getModuleLink($name, $controller, $params, null, $idLang, $idShop);
        }
    }

    /**
     * Get the url for the controller
     *
     * @param string $controllerClassName controller class name prefix, without the 'Controller' part
     * @param int $employeeId current logged in employee id
     * @return string controller url
     *
     * @suppress PhanDeprecatedFunction
     */
    public static function getControllerUrl($controllerClassName, $employeeId)
    {
        /** @noinspection PhpDeprecationInspection */
        $tabId = (int)Tab::getIdFromClassName($controllerClassName);
        $token = Tools::getAdminToken($controllerClassName . $tabId . $employeeId);

        return 'index.php?controller=' . $controllerClassName . '&token=' . $token;
    }

    /**
     * Get the full url for admin controller
     *
     * @param string $controller Controller name
     * @param int $langId id of the language
     * @return string url
     * @throws PrestaShopException
     */
    public static function getFullAdminControllerUrl($controller, $langId)
    {
        $baseUrl = NostoHelperUrl::getBaseUrl();
        $params = array(
            'token' => Tools::getAdminTokenLite($controller),
            NostoTagging::MODULE_NAME . '_current_language' => $langId
        );
        return $baseUrl . basename(_PS_ADMIN_DIR_) . '/' . Dispatcher::getInstance()->createUrl($controller, $langId, $params);
    }

    /**
     * Returns the base url for given shop.
     *
     * @return string the base url.
     */
    public static function getBaseUrl()
    {
        $idShop = (int)NostoHelperContext::getShopId();
        $ssl = Configuration::get(self::PS_SSL_ENABLED);

        if (Configuration::get(self::PS_MULTISHOP_FEATURE_ACTIVE) && !is_null($idShop)) {
            $shop = new Shop($idShop);
        } else {
            $shop = NostoHelperContext::getShop();
        }

        $base = ($ssl ? 'https://' . ShopUrl::getMainShopDomainSSL() : 'http://' . ShopUrl::getMainShopDomain());
        return $base . $shop->getBaseURI();
    }


    /**
     * Returns the current shop's url from the context and language.
     *
     * @return string the absolute url.
     * @suppress PhanTypeMismatchArgument
     */
    public static function getContextShopUrl()
    {
        $base = self::getBaseUrl();
        $multiLang = (Language::countActiveLanguages(NostoHelperContext::getShopId()) > 1);
        $lang = '';
        if ($multiLang) {
            $rewrite = (int)Configuration::get(
                NostoHelperUrl::PS_REWRITING_SETTINGS,
                null,
                null,
                NostoHelperContext::getShopId()
            );
            if ($rewrite) {
                $lang = NostoHelperContext::getLanguage()->iso_code . '/';
            } else {
                $lang = '?' . self::ID_LANG . '=' . NostoHelperContext::getLanguageId();
            }
        }

        return $base . $lang;
    }

    /**
     * Returns shop domain
     *
     * @return string
     * @throws NostoException
     */
    public static function getShopDomain()
    {
        return UrlHelper::parseDomain(self::getBaseUrl());
    }
}
