<?php

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

	protected $module_name;
	protected $module_path;

	/**
	 * @param Context $context
	 * @param int $id_lang
	 */
	public function loadData($context, $id_lang)
	{
		$language = new Language($id_lang);
		if (!Validate::isLoadedObject($language))
			return;

		$params = array('language_id' => $id_lang);

		// Backward compatibility
		if (_PS_VERSION_ < '1.5')
		{
			$ssl = Configuration::get('PS_SSL_ENABLED');
			$base = ($ssl ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_);
			$params['id_lang'] = (int)$context->language->id;
			$params['module'] = $this->module_name;
			$params['controller'] = 'oauth2';
			$this->redirect_url = $base.$this->module_path.'ctrl.php?'.http_build_query($params);
		}
		else
		{
			$link = new Link();
			$this->redirect_url = $link->getModuleLink($this->name, 'oauth2', $params);
		}

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
	 * almost always be the ones defined in NostoApiToken::$tokenNames.
	 *
	 * @return array the scopes.
	 */
	public function getScopes()
	{
		// We want all the available Nosto API tokens.
		return NostoApiToken::$tokenNames;
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

	public function setModuleName($name)
	{
		$this->module_name = $name;
	}

	public function setModulePath($path)
	{
		$this->module_path = $path;
	}
}
