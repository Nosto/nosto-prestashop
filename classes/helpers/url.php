<?php
/**
 * 2013-2015 Nosto Solutions Ltd
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
 * @copyright 2013-2015 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for managing urls.
 */
class NostoTaggingHelperUrl
{
	const SEARCH_PAGE_QUERY = 'controller=search&search_query=nosto';
	const DEFAULT_SERVER_ADDRESS = 'connect.nosto.com';

	/**
	 * Returns a preview url to a product page.
	 *
	 * @param int|null $id_product optional product ID if a specific product is required.
	 * @param int|null $id_lang optional language ID if a specific language is needed.
	 * @return string the url.
	 */
	public function getPreviewUrlProduct($id_product = null, $id_lang = null)
	{
		try
		{
			if (!$id_product)
			{
				// Find a product that is active and available for order.
				$sql = <<<EOT
			SELECT `id_product`
			FROM `ps_product`
			WHERE `active` = 1
			AND `available_for_order` = 1
EOT;
				$row = Db::getInstance()->getRow($sql);
				$id_product = isset($row['id_product']) ? (int)$row['id_product'] : 0;
			}

			if (!$id_lang)
				$id_lang = Context::getContext()->language->id;

			$product = new Product($id_product, $id_lang);
			if (!ValidateCore::isLoadedObject($product))
				return '';

			$link = new Link();
			$url = $link->getProductLink($product, null, null, null, $id_lang);
			return $this->addPreviewQueryParams($url, $id_lang);
		}
		catch (Exception $e)
		{
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
		try
		{
			if (!$id_category)
			{
				// Find a category that is active, not the root category and has a parent category.
				$sql = <<<EOT
				SELECT `id_category`
				FROM `ps_category`
				WHERE `active` = 1
				AND `id_parent` > 0
EOT;
				// There is not "is_root_category" in PS 1.4, but in >= 1.5 we want to skip the root.
				if (_PS_VERSION_ >= '1.5')
					$sql .= ' AND `is_root_category` = 0';
				$row = Db::getInstance()->getRow($sql);
				$id_category = isset($row['id_category']) ? (int)$row['id_category'] : 0;
			}

			if (!$id_lang)
				$id_lang = Context::getContext()->language->id;

			$category = new Category($id_category, $id_lang);
			if (!ValidateCore::isLoadedObject($category))
				return '';

			$link = new Link();
			$url = $link->getCategoryLink($category, null, $id_lang);
			return $this->addPreviewQueryParams($url, $id_lang);
		}
		catch (Exception $e)
		{
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
		try
		{
			if (!$id_lang)
				$id_lang = Context::getContext()->language->id;
			$link = new Link();
			$url = $link->getPageLink('search.php', true, $id_lang).'?'.self::SEARCH_PAGE_QUERY;
			return $this->addPreviewQueryParams($url, $id_lang);
		}
		catch (Exception $e)
		{
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
		try
		{
			if (!$id_lang)
				$id_lang = Context::getContext()->language->id;
			$link = new Link();
			$url = $link->getPageLink('order.php', true, $id_lang);
			return $this->addPreviewQueryParams($url, $id_lang);
		}
		catch (Exception $e)
		{
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
		try
		{
			if (!$id_lang)
				$id_lang = Context::getContext()->language->id;
			$link = new Link();
			$url = $link->getPageLink('index.php', true, $id_lang);
			return $this->addPreviewQueryParams($url, $id_lang);
		}
		catch (Exception $e)
		{
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
		return isset($_ENV['NOSTO_SERVER_URL']) ? $_ENV['NOSTO_SERVER_URL'] : self::DEFAULT_SERVER_ADDRESS;
	}

	/**
	 * Builds a module controller url for the language and shop.
	 *
	 * We created our own method due to the existing one in `LinkCore` working differently depending on PS version.
	 *
	 * @param string $module_name the name of the module to create an url for.
	 * @param string $module_path the path of the module to create an url for (PS 1.4 only).
	 * @param string $controller the name of the controller.
	 * @param int|null $id_lang the language ID (falls back on current context if not set).
	 * @param int|null $id_shop the shop ID (falls back on current context if not set).
	 * @param array $params additional params to pass to the controller.
	 * @return string the url.
	 */
	public function getModuleUrl($module_name, $module_path, $controller, $id_lang = null, $id_shop = null, array $params = array())
	{
		if (is_null($id_lang))
			$id_lang = (int)Context::getContext()->language->id;
		if (is_null($id_shop))
			$id_shop = (int)Context::getContext()->shop->id;

		$base = $this->getBaseUrl($id_shop);
		$params['module'] = $module_name;
		$params['controller'] = $controller;

		if (_PS_VERSION_ < '1.5')
		{
			$params['id_lang'] = $id_lang;
			return $base.$module_path.'ctrl.php?'.http_build_query($params);
		}
		else
		{
			$lang = $this->getLangUriPath($id_lang, $id_shop);
			/** @var DispatcherCore $dispatcher */
			$dispatcher = Dispatcher::getInstance();
			$allow_url_rewrite = (int)Configuration::get('PS_REWRITING_SETTINGS');
			return $base.$lang.$dispatcher->createUrl('module', $id_lang, $params, $allow_url_rewrite, '', $id_shop);
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
		if (_PS_VERSION_ < '1.5')
			return ($ssl ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_);

		if (Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE') && !is_null($id_shop))
			$shop = new Shop($id_shop);
		else
			$shop = Context::getContext()->shop;

		$base = ($ssl ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain);
		return $base.$shop->getBaseURI();
	}

	/**
	 * Returns the language part of the url if "friendly urls" are enabled.
	 *
	 * @param int|null $id_lang the language ID (falls back on current context if not set).
	 * @param int|null $id_shop the shop ID (falls back on current context if not set).
	 * @return string the language part of the url or empty string.
	 */
	public function getLangUriPath($id_lang = null, $id_shop = null)
	{
		if (is_null($id_lang))
			$id_lang = (int)Context::getContext()->language->id;
		if (is_null($id_shop))
			$id_shop = (int)Context::getContext()->shop->id;

		$allow_url_rewrite = (int)Configuration::get('PS_REWRITING_SETTINGS', null, null, $id_shop);
		if (!Language::isMultiLanguageActivated($id_shop) || !$allow_url_rewrite)
			return '';

		return Language::getIsoById($id_lang).'/';
	}

	/**
	 * Adds any additional query params to the preview url, namely the "nostodebug" flag.
	 * Also adds the id_lang param if url rewriting is not on as it seems that it is left out in some cases.
	 *
	 * @param string $url the preview url to add the query param to.
	 * @param int $id_lang the language ID for which the url is created.
	 * @return string the preview url with added params.
	 */
	protected function addPreviewQueryParams($url, $id_lang)
	{
		// If url rewriting is of, then make sure the id_lang is set.
		if ((int)Configuration::get('PS_REWRITING_SETTINGS') === 0)
			$url = NostoHttpRequest::replaceQueryParamInUrl('id_lang', $id_lang, $url);
		// Always add the "nostodebug" flag.
		$url = NostoHttpRequest::replaceQueryParamInUrl('nostodebug', 'true', $url);
		return $url;
	}
}
