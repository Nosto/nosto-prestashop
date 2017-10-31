<?php
/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\Request\Http\HttpRequest as NostoSDKHttpRequest;

class NostoHelperUrl
{
    const PS_REWRITING_SETTINGS = 'PS_REWRITING_SETTINGS';
    const ID_LANG = 'id_lang';
    const ID_PRODUCT = "id_product";
    const PS_SSL_ENABLED = 'PS_SSL_ENABLED';
    const PS_MULTISHOP_FEATURE_ACTIVE = 'PS_MULTISHOP_FEATURE_ACTIVE';

    /**
     * Returns a preview url to a product page.
     *
     * @return string the url.
     */
    public static function getPreviewUrlProduct()
    {
        try {
            $idLang = NostoHelperContext::getLanguageId();

            $row = Product::getProducts($idLang, 0, 1, self::ID_PRODUCT, "ASC", false, true);
            $productId = isset($row[self::ID_PRODUCT]) ? (int)$row[self::ID_PRODUCT] : 0;

            $product = new Product($productId, $idLang, NostoHelperContext::getShopId());
            if (!Validate::isLoadedObject($product)) {
                return '';
            }

            $params = array('nostodebug' => 'true');
            return self::getProductUrl($product, $params);
        } catch (Exception $e) {
            NostoHelperLogger::error($e, "Unable to build the product page preview URL");
            return '';
        }
    }

    /**
     * Returns a preview url to a category page.
     *
     * @return string the url.
     *
     * @suppress PhanTypeMismatchArgument
     */
    public static function getPreviewUrlCategory()
    {
        try {
            $idLang = NostoHelperContext::getLanguageId();

            $rows = Category::getHomeCategories($idLang, true);
            $row = $rows[0];
            $categoryId = isset($row['id_category']) ? (int)$row['id_category'] : 0;

            $category = new Category($categoryId, $idLang);
            if (!Validate::isLoadedObject($category)) {
                return '';
            }

            $params = array('nostodebug' => 'true');
            return self::getCategoryUrl($category, $params);
        } catch (Exception $e) {
            NostoHelperLogger::error($e, "Unable to build the category page preview URL");
            return '';
        }
    }

    /**
     * Returns a preview url to the search page.
     *
     * @return string the url.
     */
    public static function getPreviewUrlSearch()
    {
        try {
            $params = array(
                'controller' => 'search',
                'search_query' => 'nosto',
                'nostodebug' => 'true',
            );
            return self::getPageUrl('search.php', $params);
        } catch (Exception $e) {
            NostoHelperLogger::error($e, "Unable to build the search page preview URL");
            return '';
        }
    }

    /**
     * Returns a preview url to cart page.
     *
     * @return string the url.
     */
    public static function getPreviewUrlCart()
    {
        try {
            $params = array('nostodebug' => 'true');
            return self::getPageUrl('order.php', $params);
        } catch (Exception $e) {
            NostoHelperLogger::error($e, "Unable to build the cart page preview URL");
            return '';
        }
    }

    /**
     * Returns a preview url to the home page.
     *
     * @return string the url.
     */
    public static function getPreviewUrlHome()
    {
        try {
            $params = array('nostodebug' => 'true');
            return self::getPageUrl('index.php', $params);
        } catch (Exception $e) {
            NostoHelperLogger::error($e, "Unable to build the home page preview URL");
            return '';
        }
    }

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
     * Returns the base url for given shop.
     *
     * @return string the base url.
     */
    private static function getBaseUrl()
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
}
