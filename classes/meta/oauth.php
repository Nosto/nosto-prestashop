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
 * Meta data class for oauth related information needed when connecting accounts to Nosto.
 */
class NostoTaggingMetaOauth implements NostoOAuthClientMetaDataInterface
{
	/**
	 * @var string the url where the oauth2 server should redirect after
	 * authorization is done.
	 */
	protected $redirect_url;

	/**
	 * @var string the language ISO code for localization on oauth2 server.
	 */
	protected $language_iso_code;

	/**
	 * @var string the name of the module.
	 */
	protected $module_name;

	/**
	 * @var string the path to the module.
	 */
	protected $module_path;

	/**
	 * Loads meta data from the given context and language.
	 *
	 * @param Context $context the context to use as data source.
	 * @param int $id_lang the language to use as data source.
	 */
	public function loadData($context, $id_lang)
	{
		$language = new Language($id_lang);
		if (!Validate::isLoadedObject($language))
			return;

		$id_lang = (int)$context->language->id;
		$id_shop = (int)$context->shop->id;
		$params = array('language_id' => (int)$language->id);
		/** @var NostoTaggingHelperUrl $url_helper */
		$url_helper = Nosto::helper('nosto_tagging/url');

		$this->redirect_url = $url_helper->getModuleUrl($this->module_name, $this->module_path, 'oauth2', $id_lang, $id_shop, $params);
		$this->language_iso_code = $language->iso_code;
	}

	/**
	 * The OAuth2 client ID.
	 * This will be a platform specific ID that Nosto will issue.
	 *
	 * @return string the client id.
	 */
	public function getClientId()
	{
		return 'prestashop';
	}

	/**
	 * The OAuth2 client secret.
	 * This will be a platform specific secret that Nosto will issue.
	 *
	 * @return string the client secret.
	 */
	public function getClientSecret()
	{
		return 'prestashop';
	}

	/**
	 * The scopes for the OAuth2 request.
	 * These are used to request specific API tokens from Nosto and should
	 * almost always be the ones defined in NostoApiToken::getApiTokenNames().
	 *
	 * @return array the scopes.
	 */
	public function getScopes()
	{
		// We want all the available Nosto API tokens.
		return NostoApiToken::getApiTokenNames();
	}

	/**
	 * The OAuth2 redirect url to where the OAuth2 server should redirect the
	 * user after authorizing the application to act on the users behalf.
	 * This url must by publicly accessible and the domain must match the one
	 * defined for the Nosto account.
	 *
	 * @return string the url.
	 */
	public function getRedirectUrl()
	{
		return $this->redirect_url;
	}

	/**
	 * Sets the redirect url.
	 *
	 * @param string $url the url.
	 */
	public function setRedirectUrl($url)
	{
		$this->redirect_url = $url;
	}

	/**
	 * The 2-letter ISO code (ISO 639-1) for the language the OAuth2 server
	 * uses for UI localization.
	 *
	 * @return string the ISO code.
	 */
	public function getLanguageIsoCode()
	{
		return $this->language_iso_code;
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
	 * Sets the module name.
	 *
	 * @param string $name the module name.
	 */
	public function setModuleName($name)
	{
		$this->module_name = $name;
	}

	/**
	 * Sets the module path.
	 *
	 * @param string $path the module path.
	 */
	public function setModulePath($path)
	{
		$this->module_path = $path;
	}
}
