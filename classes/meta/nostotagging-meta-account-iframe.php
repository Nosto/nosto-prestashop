<?php

class NostoTaggingMetaAccountIframe implements NostoAccountMetaDataIframeInterface
{
	/**
	 * @var string the admin user first name.
	 */
	protected $first_name;

	/**
	 * @var string the admin user last name.
	 */
	protected $last_name;

	/**
	 * @var    string the admin user email address.
	 */
	protected $email;

	/**
	 * @var string the language ISO (ISO 639-1) code for oauth server locale.
	 */
	protected $language_iso_code;

	/**
	 * @var string the language ISO (ISO 639-1) for the store view scope.
	 */
	protected $language_iso_code_shop;

	/**
	 * @var string unique ID that identifies the Magento installation.
	 */
	protected $unique_id;

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
	 * @param Context $context
	 * @param int $id_lang
	 */
	public function loadData($context, $id_lang)
	{
		$shop_language = new Language($id_lang);
		if (!Validate::isLoadedObject($shop_language))
			return;

		$this->first_name = $context->employee->firstname;
		$this->last_name = $context->employee->lastname;
		$this->email = $context->employee->email;
		$this->language_iso_code = $context->language->iso_code;
		$this->language_iso_code_shop = $shop_language->iso_code;
		$this->unique_id = $this->getUniqueInstallationId(); // todo
		$this->preview_url_product = NostoTaggingPreviewLink::getProductPageUrl(null, $id_lang);
		$this->preview_url_category = NostoTaggingPreviewLink::getCategoryPageUrl(null, $id_lang);
		$this->preview_url_search = NostoTaggingPreviewLink::getSearchPageUrl($id_lang);
		$this->preview_url_cart = NostoTaggingPreviewLink::getCartPageUrl($id_lang);
		$this->preview_url_front = NostoTaggingPreviewLink::getHomePageUrl($id_lang);
	}

	/**
	 * The name of the platform the iframe is used on.
	 * A list of valid platform names is issued by Nosto.
	 *
	 * @return string the platform name.
	 */
	public function getPlatform()
	{
		return 'prestashop';
	}

	/**
	 * Sets the first name of the admin user.
	 *
	 * @param string $firstName the first name.
	 */
	public function setFirstName($firstName)
	{
		$this->first_name = $firstName;
	}

	/**
	 * The first name of the user who is loading the config iframe.
	 *
	 * @return string the first name.
	 */
	public function getFirstName()
	{
		return $this->first_name;
	}

	/**
	 * Sets the last name of the admin user.
	 *
	 * @param string $lastName the last name.
	 */
	public function setLastName($lastName)
	{
		$this->last_name = $lastName;
	}

	/**
	 * The last name of the user who is loading the config iframe.
	 *
	 * @return string the last name.
	 */
	public function getLastName()
	{
		return $this->last_name;
	}

	/**
	 * Sets the email address of the admin user.
	 *
	 * @param string $email the email address.
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * The email address of the user who is loading the config iframe.
	 *
	 * @return string the email address.
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Sets the language ISO code.
	 *
	 * @param string $code the ISO code.
	 */
	public function setLanguageIsoCode($code)
	{
		$this->language_iso_code = $code;
	}

	/**
	 * The 2-letter ISO code (ISO 639-1) for the language of the user who is
	 * loading the config iframe.
	 *
	 * @return string the language ISO code.
	 */
	public function getLanguageIsoCode()
	{
		return $this->language_iso_code;
	}

	/**
	 * The 2-letter ISO code (ISO 639-1) for the language of the shop the
	 * account belongs to.
	 *
	 * @return string the language ISO code.
	 */
	public function getLanguageIsoCodeShop()
	{
		return $this->language_iso_code_shop;
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
		return 'todo'; // todo
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
}
