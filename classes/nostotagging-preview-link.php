<?php
/**
 * 2013-2014 Nosto Solutions Ltd
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
 * @copyright 2013-2014 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for generating preview links for the front end slots.
 */
class NostoTaggingPreviewLink
{
	const SEARCH_PAGE_QUERY = 'controller=search&search_query=nosto';

	/**
	 * Returns a preview url to a product page.
	 *
	 * @param int|null $id_product optional product ID if a specific product is required.
	 * @param int|null $id_lang optional language ID if a specific language is needed.
	 * @return string the url.
	 */
	public static function getProductPageUrl($id_product = null, $id_lang = null)
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
			$url = NostoTaggingHttpRequest::replaceQueryParamInUrl('nostodebug', 'true', $url);
			return $url;
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
	public static function getCategoryPageUrl($id_category = null, $id_lang = null)
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
			$url = NostoTaggingHttpRequest::replaceQueryParamInUrl('nostodebug', 'true', $url);
			return $url;
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
	public static function getSearchPageUrl($id_lang = null)
	{
		try
		{
			if (!$id_lang)
				$id_lang = Context::getContext()->language->id;
			$link = new Link();
			$url = $link->getPageLink('search.php', true, $id_lang).'?'.self::SEARCH_PAGE_QUERY;
			$url = NostoTaggingHttpRequest::replaceQueryParamInUrl('nostodebug', 'true', $url);
			return $url;
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
	public static function getCartPageUrl($id_lang = null)
	{
		try
		{
			if (!$id_lang)
				$id_lang = Context::getContext()->language->id;
			$link = new Link();
			$url = $link->getPageLink('order.php', true, $id_lang);
			$url = NostoTaggingHttpRequest::replaceQueryParamInUrl('nostodebug', 'true', $url);
			return $url;
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
	public static function getHomePageUrl($id_lang = null)
	{
		try
		{
			if (!$id_lang)
				$id_lang = Context::getContext()->language->id;
			$link = new Link();
			$url = $link->getPageLink('index.php', true, $id_lang);
			$url = NostoTaggingHttpRequest::replaceQueryParamInUrl('nostodebug', 'true', $url);
			return $url;
		}
		catch (Exception $e)
		{
			// Return empty on failure
			return '';
		}
	}
}
