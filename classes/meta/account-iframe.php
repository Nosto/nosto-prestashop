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
 * Meta data class for account iframe related information needed when showing the admin iframe on module settings page.
 */
class NostoTaggingMetaAccountIframe implements NostoAccountMetaIframeInterface
{
	/**
	 * @var NostoLanguageCode the language ISO (ISO 639-1) code for oauth server locale.
	 */
	protected $language;

	/**
	 * @var NostoLanguageCode the language ISO (ISO 639-1) for the store view scope.
	 */
	protected $shop_language;

	/**
	 * @var string unique ID that identifies the Magento installation.
	 */
	protected $unique_id;

	/**
	 * @var string the version number of the Nosto module/extension running on the e-commerce installation.
	 */
	protected $module_version;

	/**
	 * @var string preview url for the product page in the active store scope.
	 */
	protected $preview_url_product;

	/**
	 * @var string preview url for the category page in the active store scope.
	 */
	protected $preview_url_category;

	/**
	 * @var string preview url for the search page in the active store scope.
	 */
	protected $preview_url_search;

	/**
	 * @var string preview url for the cart page in the active store scope.
	 */
	protected $preview_url_cart;

	/**
	 * @var string preview url for the front page in the active store scope.
	 */
	protected $preview_url_front;

	/**
	 * @var string the name of the shop context where nosto is installed or is about to be installed.
	 */
	protected $shop_name;

	/**
	 * Loads the meta-data from plugin context.
	 *
	 * @param NostoTagging $plugin the plugin.
	 * @param int $id_lang the language ID of the shop for which to get the meta-data.
	 */
	public function loadData(NostoTagging $plugin, $id_lang)
	{
		$shop_language = new Language($id_lang);
		if (!Validate::isLoadedObject($shop_language))
			return;

		/** @var NostoTaggingHelperUrl $url_helper */
		$url_helper = Nosto::helper('nosto_tagging/url');
		$context = $plugin->getContext();

		$this->language = new NostoLanguageCode($context->language->iso_code);
		$this->shop_language = new NostoLanguageCode($shop_language->iso_code);
		$this->unique_id = $plugin->getUniqueInstallationId();
		$this->module_version = $plugin->version;
		$this->preview_url_product = $url_helper->getPreviewUrlProduct(null, $id_lang);
		$this->preview_url_category = $url_helper->getPreviewUrlCategory(null, $id_lang);
		$this->preview_url_search = $url_helper->getPreviewUrlSearch($id_lang);
		$this->preview_url_cart = $url_helper->getPreviewUrlCart($id_lang);
		$this->preview_url_front = $url_helper->getPreviewUrlHome($id_lang);
		$this->shop_name = $shop_language->name;
	}

	/**
	 * The 2-letter ISO code (ISO 639-1) for the language of the user who is
	 * loading the config iframe.
	 *
	 * @return NostoLanguageCode the language code.
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * The 2-letter ISO code (ISO 639-1) for the language of the shop the
	 * account belongs to.
	 *
	 * @return NostoLanguageCode the language code.
	 */
	public function getShopLanguage()
	{
		return $this->shop_language;
	}

	/**
	 * Unique identifier for the e-commerce installation.
	 * This identifier is used to link accounts together that are created on
	 * the same installation.
	 *
	 * @return string the identifier.
	 */
	public function getUniqueId()
	{
		return $this->unique_id;
	}

	/**
	 * The version number of the platform the e-commerce installation is
	 * running on.
	 *
	 * @return string the platform version.
	 */
	public function getVersionPlatform()
	{
		return _PS_VERSION_;
	}

	/**
	 * The version number of the Nosto module/extension running on the
	 * e-commerce installation.
	 *
	 * @return string the module version.
	 */
	public function getVersionModule()
	{
		return $this->module_version;
	}

	/**
	 * An absolute URL for any product page in the shop the account is linked
	 * to, with the nostodebug GET parameter enabled.
	 * e.g. http://myshop.com/products/product123?nostodebug=true
	 * This is used in the config iframe to allow the user to quickly preview
	 * the recommendations on the given page.
	 *
	 * @return string the url.
	 */
	public function getPreviewUrlProduct()
	{
		return $this->preview_url_product;
	}

	/**
	 * An absolute URL for any category page in the shop the account is linked
	 * to, with the nostodebug GET parameter enabled.
	 * e.g. http://myshop.com/products/category123?nostodebug=true
	 * This is used in the config iframe to allow the user to quickly preview
	 * the recommendations on the given page.
	 *
	 * @return string the url.
	 */
	public function getPreviewUrlCategory()
	{
		return $this->preview_url_category;
	}

	/**
	 * An absolute URL for the search page in the shop the account is linked
	 * to, with the nostodebug GET parameter enabled.
	 * e.g. http://myshop.com/search?query=red?nostodebug=true
	 * This is used in the config iframe to allow the user to quickly preview
	 * the recommendations on the given page.
	 *
	 * @return string the url.
	 */
	public function getPreviewUrlSearch()
	{
		return $this->preview_url_search;
	}

	/**
	 * An absolute URL for the shopping cart page in the shop the account is
	 * linked to, with the nostodebug GET parameter enabled.
	 * e.g. http://myshop.com/cart?nostodebug=true
	 * This is used in the config iframe to allow the user to quickly preview
	 * the recommendations on the given page.
	 *
	 * @return string the url.
	 */
	public function getPreviewUrlCart()
	{
		return $this->preview_url_cart;
	}

	/**
	 * An absolute URL for the front page in the shop the account is linked to,
	 * with the nostodebug GET parameter enabled.
	 * e.g. http://myshop.com?nostodebug=true
	 * This is used in the config iframe to allow the user to quickly preview
	 * the recommendations on the given page.
	 *
	 * @return string the url.
	 */
	public function getPreviewUrlFront()
	{
		return $this->preview_url_front;
	}

	/**
	 * Returns the name of the shop context where nosto is installed or about to be installed.
	 *
	 * @return string the name.
	 */
	public function getShopName()
	{
		return $this->shop_name;
	}
}
