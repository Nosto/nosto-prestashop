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

/**
 * Helper class for managing urls.
 */
class NostoTaggingHelperUrl
{
    const DEFAULT_SERVER_ADDRESS = 'connect.nosto.com';
    const DEFAULT_IFRAME_ORIGIN_REGEXP = '(https:\/\/(.*)\.hub\.nosto\.com)|(https:\/\/my\.nosto\.com)';

    /**
     * Returns a preview url to a product page.
     *
     * @param int|null $id_product optional product ID if a specific product is required.
     * @param int|null $id_lang optional language ID if a specific language is needed.
     * @return string the url.
     */
    public function getPreviewUrlProduct($id_product = null, $id_lang = null)
    {
        try {
            if (!$id_product) {
            // Find a product that is active and available for order.
                $sql = '
                    SELECT `id_product`
                    FROM `'.pSQL(_DB_PREFIX_).'product`
                    WHERE `active` = 1
                    AND `available_for_order` = 1
                ';

                $row = Db::getInstance()->getRow($sql);
                $id_product = isset($row['id_product']) ? (int)$row['id_product'] : 0;
            }

            if (is_null($id_lang)) {
                $id_lang = (int)Context::getContext()->language->id;
            }

            $product = new Product($id_product, $id_lang);
            if (!ValidateCore::isLoadedObject($product)) {
                return '';
            }

            $params = array('nostodebug' => 'true');
            return $this->getProductUrl($product, $id_lang, null, $params);
        } catch (Exception $e) {
        // Return empty on failure
            return '';
        }
    }

    /**
     * Returns a preview url to a category page.
     *
     * @param int|null $id_category optional category ID if a specific category is required.
     * @param int|null $id_lang optional language ID if a specific language is needed.
     * @return string the url.
     */
    public function getPreviewUrlCategory($id_category = null, $id_lang = null)
    {
        try {
            if (!$id_category) {
            // Find a category that is active, not the root category and has a parent category.
                $sql = '
                    SELECT `id_category`
                    FROM `'.pSQL(_DB_PREFIX_).'category`
                    WHERE `active` = 1
                    AND `id_parent` > 0
                    AND `is_root_category` = 0
				';
                $row = Db::getInstance()->getRow($sql);
                $id_category = isset($row['id_category']) ? (int)$row['id_category'] : 0;
            }

            if (is_null($id_lang)) {
                $id_lang = (int)Context::getContext()->language->id;
            }

            $category = new Category($id_category, $id_lang);
            if (!ValidateCore::isLoadedObject($category)) {
                return '';
            }

            $params = array('nostodebug' => 'true');
            return $this->getCategoryUrl($category, $id_lang, null, $params);
        } catch (Exception $e) {
        // Return empty on failure
            return '';
        }
    }

    /**
     * Returns a preview url to the search page.
     *
     * @param int|null $id_lang optional language ID if a specific language is needed.
     * @return string the url.
     */
    public function getPreviewUrlSearch($id_lang = null)
    {
        try {
            $params = array(
                'controller' => 'search',
                'search_query' => 'nosto',
                'nostodebug' => 'true',
            );
            return $this->getPageUrl('search.php', $id_lang, null, $params);
        } catch (Exception $e) {
        // Return empty on failure
            return '';
        }
    }

    /**
     * Returns a preview url to cart page.
     *
     * @param int|null $id_lang optional language ID if a specific language is needed.
     * @return string the url.
     */
    public function getPreviewUrlCart($id_lang = null)
    {
        try {
            $params = array('nostodebug' => 'true');
            return $this->getPageUrl('order.php', $id_lang, null, $params);
        } catch (Exception $e) {
        // Return empty on failure
            return '';
        }
    }

    /**
     * Returns a preview url to the home page.
     *
     * @param int|null $id_lang optional language ID if a specific language is needed.
     * @return string the url.
     */
    public function getPreviewUrlHome($id_lang = null)
    {
        try {
            $params = array('nostodebug' => 'true');
            return $this->getPageUrl('index.php', $id_lang, null, $params);
        } catch (Exception $e) {
        // Return empty on failure
            return '';
        }
    }

    /**
     * Get the Nosto server address for the shop frontend JavaScripts.
     *
     * @return string the url.
     */
    public function getServerAddress()
    {
        return Nosto::getEnvVariable('NOSTO_SERVER_URL', self::DEFAULT_SERVER_ADDRESS);
    }

    /**
     * Builds a product page url for the language and shop.
     *
     * We created our own method due to the existing one in `LinkCore` behaving differently across PS versions.
     *
     * @param Product|ProductCore $product
     * @param int|null $id_lang the language ID (falls back on current context if not set).
     * @param int|null $id_shop the shop ID (falls back on current context if not set).
     * @param array $params additional params to add to the url.
     * @return string the product page url.
     */
    public function getProductUrl($product, $id_lang = null, $id_shop = null, array $params = array())
    {
        if (is_null($id_lang)) {
            $id_lang = (int)Context::getContext()->language->id;
        }
        if (is_null($id_shop)) {
            $id_shop = (int)Context::getContext()->shop->id;
        }

        if (version_compare(_PS_VERSION_, '1.5.0.0') === -1 || version_compare(_PS_VERSION_, '1.5.5.0') >= 0) {
            /** @var LinkCore $link */
            $link = NostoTagging::buildLinkClass();
            $url = $link->getProductLink($product, null, null, null, $id_lang, $id_shop);
        } else {
            // For PS versions 1.5.0.0 - 1.5.4.1 we always hard-code the urls to be in non-friendly format and fetch
            // the shops base url ourselves. This is a workaround to all the bugs related to url building in these
            // PS versions.
            $query_params = array(
                'id_product' => (int)$product->id,
                'controller' => 'product',
                'id_lang' => $id_lang,
            );
            $url = $this->getBaseUrl($id_shop).'index.php?'.http_build_query($query_params);
        }

        if ((int)Configuration::get('PS_REWRITING_SETTINGS') === 0) {
            $params['id_lang'] = $id_lang;
        }

        return NostoHttpRequest::replaceQueryParamsInUrl($params, $url);
    }

    /**
     * Builds a category page url for the language and shop.
     *
     * We created our own method due to the existing one in `LinkCore` behaving differently across PS versions.
     *
     * @param Category|CategoryCore $category the category model.
     * @param int|null $id_lang the language ID (falls back on current context if not set).
     * @param int|null $id_shop the shop ID (falls back on current context if not set).
     * @param array $params additional params to add to the url.
     * @return string the category page url.
     */
    public function getCategoryUrl($category, $id_lang = null, $id_shop = null, array $params = array())
    {
        if (is_null($id_lang)) {
            $id_lang = (int)Context::getContext()->language->id;
        }
        if (is_null($id_shop)) {
            $id_shop = (int)Context::getContext()->shop->id;
        }

        if (version_compare(_PS_VERSION_, '1.5.0.0') === -1 || version_compare(_PS_VERSION_, '1.5.5.0') >= 0) {
            /** @var LinkCore $link */
            $link = NostoTagging::buildLinkClass();
            $url = $link->getCategoryLink($category, null, $id_lang, null, $id_shop);
        } else {
            // For PS versions 1.5.0.0 - 1.5.4.1 we always hard-code the urls to be in non-friendly format and fetch
            // the shops base url ourselves. This is a workaround to all the bugs related to url building in these
            // PS versions.
            $query_params = array(
                'id_category' => (int)$category->id,
                'controller' => 'category',
                'id_lang' => $id_lang,
            );
            $url = $this->getBaseUrl($id_shop).'index.php?'.http_build_query($query_params);
        }

        if ((int)Configuration::get('PS_REWRITING_SETTINGS') === 0) {
            $params['id_lang'] = $id_lang;
        }

        return NostoHttpRequest::replaceQueryParamsInUrl($params, $url);
    }

    /**
     * Builds a page url for the language and shop.
     *
     * We created our own method due to the existing one in `LinkCore` behaving differently across PS versions.
     *
     * @param string $controller the controller name.
     * @param int|null $id_lang the language ID (falls back on current context if not set).
     * @param int|null $id_shop the shop ID (falls back on current context if not set).
     * @param array $params additional params to add to the url.
     * @return string the page url.
     */
    public function getPageUrl($controller, $id_lang = null, $id_shop = null, array $params = array())
    {
        if (is_null($id_lang)) {
            $id_lang = (int)Context::getContext()->language->id;
        }
        if (is_null($id_shop)) {
            $id_shop = (int)Context::getContext()->shop->id;
        }

        if (version_compare(_PS_VERSION_, '1.5.0.0') === -1 || version_compare(_PS_VERSION_, '1.5.5.0') >= 0) {
            /** @var LinkCore $link */
            $link = NostoTagging::buildLinkClass();
            $url = $link->getPageLink($controller, true, $id_lang, null, false, $id_shop);
        } else {
            // For PS versions 1.5.0.0 - 1.5.4.1 we always hard-code the urls to be in non-friendly format and fetch
            // the shops base url ourselves. This is a workaround to all the bugs related to url building in these
            // PS versions.
            $query_params = array(
                'controller' => Tools::strReplaceFirst('.php', '', $controller),
                'id_lang' => $id_lang,
            );
            $url = $this->getBaseUrl($id_shop).'index.php?'.http_build_query($query_params);
        }

        if ((int)Configuration::get('PS_REWRITING_SETTINGS') === 0) {
            $params['id_lang'] = $id_lang;
        }

        return NostoHttpRequest::replaceQueryParamsInUrl($params, $url);
    }

    /**
     * Builds a module controller url for the language and shop.
     *
     * We created our own method due to the existing one in `LinkCore` behaving differently across PS versions.
     *
     * @param string $name the name of the module to create an url for.
     * @param string $path the path of the module to create an url for
     * @param string $controller the name of the controller.
     * @param int|null $id_lang the language ID (falls back on current context if not set).
     * @param int|null $id_shop the shop ID (falls back on current context if not set).
     * @param array $params additional params to add to the url.
     * @return string the url.
     */
    public function getModuleUrl($name, $path, $controller, $id_lang = null, $id_shop = null, array $params = array())
    {
        if (is_null($id_lang)) {
            $id_lang = (int)Context::getContext()->language->id;
        }
        if (is_null($id_shop)) {
            $id_shop = (int)Context::getContext()->shop->id;
        }

        $params['module'] = $name;
        $params['controller'] = $controller;

        if (version_compare(_PS_VERSION_, '1.5.0.0') === -1) {
            $params['id_lang'] = $id_lang;
            return $this->getBaseUrl($id_shop).$path.'ctrl.php?'.http_build_query($params);
        } elseif (version_compare(_PS_VERSION_, '1.5.5.0') === -1) {
        // For PS versions 1.5.0.0 - 1.5.4.1 we always hard-code the urls to be in non-friendly format and fetch
            // the shops base url ourselves. This is a workaround to all the bugs related to url building in these
            // PS versions.
            $params['fc'] = 'module';
            $params['module'] = $name;
            $params['controller'] = $controller;
            $params['id_lang'] = $id_lang;
            return $this->getBaseUrl($id_shop).'index.php?'.http_build_query($params);
        } else {
            /** @var LinkCore $link */
            $link = NostoTagging::buildLinkClass();
            return $link->getModuleLink($name, $controller, $params, null, $id_lang, $id_shop);
        }
    }

    /**
     * Returns the base url for given shop.
     *
     * @param null $id_shop the shop ID (falls back on current context if not set).
     * @return string the base url.
     */
    public function getBaseUrl($id_shop = null)
    {
        $ssl = Configuration::get('PS_SSL_ENABLED');

        if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && !is_null($id_shop)) {
            $shop = new Shop($id_shop);
        } else {
            $shop = Context::getContext()->shop;
        }

        /** @var Shop|ShopCore $shop */
        $base = ($ssl ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain);
        return $base.$shop->getBaseURI();
    }

    /**
     * Returns the iframe origin where messages are allowed
     *
     * @return string|false
     */
    public function getIframeOrigin()
    {
        return Nosto::getEnvVariable('NOSTO_IFRAME_ORIGIN_REGEXP', self::DEFAULT_IFRAME_ORIGIN_REGEXP);
    }
}
