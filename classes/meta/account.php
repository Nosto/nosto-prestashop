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
 * Meta data class for account related information needed when creating new accounts.
 */
class NostoTaggingMetaAccount implements NostoAccountMetaDataInterface
{
	/**
	 * @var string the store name.
	 */
	protected $title;

	/**
	 * @var string the account name.
	 */
	protected $name;

	/**
	 * @var string the store front end url.
	 */
	protected $front_page_url;

	/**
	 * @var string the store currency ISO (ISO 4217) code.
	 */
	protected $currency_code;

	/**
	 * @var string the store language ISO (ISO 639-1) code.
	 */
	protected $language_code;

	/**
	 * @var string the owner language ISO (ISO 639-1) code.
	 */
	protected $owner_language_code;

	/**
	 * @var NostoTaggingMetaAccountOwner the account owner meta model.
	 */
	protected $owner;

	/**
	 * @var NostoTaggingMetaAccountBilling the billing meta model.
	 */
	protected $billing;

	/**
	 * @var string the API token used to identify an account creation.
	 */
	protected $sign_up_api_token = 'JRtgvoZLMl4NPqO9XWhRdvxkTMtN82ITTJij8U7necieJPCvjtZjm5C4fpNrYJ81';

	/**
	 * Loads the meta data for the context and given language.
	 *
	 * @param Context $context the context to use as data source.
	 * @param int $id_lang the language to use as data source.
	 */
	public function loadData($context, $id_lang)
	{
		$language = new Language($id_lang);
		if (!Validate::isLoadedObject($language))
			return;

		if (!Validate::isLoadedObject($context->language))
			$context->language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		if (!Validate::isLoadedObject($context->currency))
			$context->currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
		if (!Validate::isLoadedObject($context->country))
			$context->country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));

		$this->title = Configuration::get('PS_SHOP_NAME');
		$this->name = Tools::substr(sha1(rand()), 0, 8);
		$this->front_page_url = $this->getContextShopUrl($context, $language);
		$this->currency_code = $context->currency->iso_code;
		$this->language_code = $context->language->iso_code;
		$this->owner_language_code = $language->iso_code;
		$this->owner = new NostoTaggingMetaAccountOwner();
		$this->owner->loadData($context);
		$this->billing = new NostoTaggingMetaAccountBilling();
		$this->billing->loadData($context);
	}

	/**
	 * Sets the store title.
	 *
	 * @param string $title the store title.
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * The shops name for which the account is to be created for.
	 *
	 * @return string the name.
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * Sets the account name.
	 *
	 * @param string $name the account name.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * The name of the account to create.
	 * This has to follow the pattern of
	 * "[platform name]-[8 character lowercase alpha numeric string]".
	 *
	 * @return string the account name.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * The name of the platform the account is used on.
	 * A list of valid platform names is issued by Nosto.
	 *
	 * @return string the platform names.
	 */
	public function getPlatform()
	{
		return 'prestashop';
	}

	/**
	 * Sets the store front page url.
	 *
	 * @param string $url the front page url.
	 */
	public function setFrontPageUrl($url)
	{
		$this->front_page_url = $url;
	}

	/**
	 * Absolute url to the front page of the shop for which the account is
	 * created for.
	 *
	 * @return string the url.
	 */
	public function getFrontPageUrl()
	{
		return $this->front_page_url;
	}

	/**
	 * Sets the store currency ISO (ISO 4217) code.
	 *
	 * @param string $code the currency ISO code.
	 */
	public function setCurrencyCode($code)
	{
		$this->currency_code = $code;
	}

	/**
	 * The 3-letter ISO code (ISO 4217) for the currency used by the shop for
	 * which the account is created for.
	 *
	 * @return string the currency ISO code.
	 */
	public function getCurrencyCode()
	{
		return $this->currency_code;
	}

	/**
	 * Sets the store language ISO (ISO 639-1) code.
	 *
	 * @param string $language_code the language ISO code.
	 */
	public function setLanguageCode($language_code)
	{
		$this->language_code = $language_code;
	}

	/**
	 * The 2-letter ISO code (ISO 639-1) for the language used by the shop for
	 * which the account is created for.
	 *
	 * @return string the language ISO code.
	 */
	public function getLanguageCode()
	{
		return $this->language_code;
	}

	/**
	 * Sets the owner language ISO (ISO 639-1) code.
	 *
	 * @param string $language_code the language ISO code.
	 */
	public function setOwnerLanguageCode($language_code)
	{
		$this->owner_language_code = $language_code;
	}

	/**
	 * The 2-letter ISO code (ISO 639-1) for the language of the account owner
	 * who is creating the account.
	 *
	 * @return string the language ISO code.
	 */
	public function getOwnerLanguageCode()
	{
		return $this->owner_language_code;
	}

	/**
	 * Meta data model for the account owner who is creating the account.
	 *
	 * @return NostoTaggingMetaAccountOwner the meta data model.
	 */
	public function getOwner()
	{
		return $this->owner;
	}

	/**
	 * Meta data model for the account billing details.
	 *
	 * @return NostoTaggingMetaAccountBilling the meta data model.
	 */
	public function getBillingDetails()
	{
		return $this->billing;
	}

	/**
	 * The API token used to identify an account creation.
	 * This token is platform specific and issued by Nosto.
	 *
	 * @return string the API token.
	 */
	public function getSignUpApiToken()
	{
		return $this->sign_up_api_token;
	}

	/**
	 * Returns the current shop's url from the context and language.
	 *
	 * @param Context $context the context.
	 * @param Language $language the language.
	 * @return string the absolute url.
	 */
	protected function getContextShopUrl($context, $language)
	{
		$shop = $context->shop;
		$ssl = Configuration::get('PS_SSL_ENABLED');
		$rewrite = (int)Configuration::get('PS_REWRITING_SETTINGS', null, null, $shop->id);
		$multi_lang = (Language::countActiveLanguages($shop->id) > 1);
		// Backward compatibility
		if (_PS_VERSION_ < '1.5')
			$base = ($ssl ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_).__PS_BASE_URI__;
		else
			$base = ($ssl ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain).$shop->getBaseURI();
		$lang = '';
		if ($multi_lang)
		{
			if ($rewrite)
				$lang = $language->iso_code.'/';
			else
				$lang = '?id_lang='.$language->id;
		}
		return $base.$lang;
	}
}
