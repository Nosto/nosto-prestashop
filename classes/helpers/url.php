<?php

class NostoTaggingHelperUrl
{
	const SEARCH_PAGE_QUERY = 'controller=search&search_query=nosto';

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
			return self::addQueryParams($url, $id_lang);
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
			return self::addQueryParams($url, $id_lang);
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
			return self::addQueryParams($url, $id_lang);
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
			return self::addQueryParams($url, $id_lang);
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
			return self::addQueryParams($url, $id_lang);
		}
		catch (Exception $e)
		{
			// Return empty on failure
			return '';
		}
	}

	/**
	 * Adds any additional query params to the preview url, namely the "nostodebug" flag.
	 * Also adds the id_lang param if url rewriting is not on as it seems that it is left out in some cases.
	 *
	 * @param string $url the preview url to add the query param to.
	 * @param int $id_lang the language ID for which the url is created.
	 * @return string the preview url with added params.
	 */
	protected function addQueryParams($url, $id_lang)
	{
		// If url rewriting is of, then make sure the id_lang is set.
		if ((int)Configuration::get('PS_REWRITING_SETTINGS') === 0)
			$url = NostoHttpRequest::replaceQueryParamInUrl('id_lang', $id_lang, $url);
		// Always add the "nostodebug" flag.
		$url = NostoHttpRequest::replaceQueryParamInUrl('nostodebug', 'true', $url);
		return $url;
	}
}
