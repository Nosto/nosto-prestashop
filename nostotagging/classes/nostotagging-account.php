<?php

/**
 * Helper class for managing Nosto accounts.
 */
class NostoTaggingAccount
{
	const PLATFORM_NAME = 'prestashop';

	/**
	 * Calls the Nosto account creation API endpoint to create a new account.
	 * It stores the account name and the SSO token to the global configuration.
	 * If a account is already configured, it will be overwritten.
	 *
	 * @param Context $context the context the account is created for.
	 * @param string|null $email address to use when signing up (default is current employee's email).
	 * @param int|null $language_id the ID of the language object to create the account for (defaults to context lang).
	 * @return bool
	 */
	public static function create($context, $email = null, $language_id = null)
	{
		$language = !empty($language_id) ? new Language($language_id) : $context->language;
		if (!Validate::isLoadedObject($language))
			return false;

		$api_tokens = array();
		foreach (NostoTaggingApiToken::$api_token_names as $token_name)
			$api_tokens[] = 'api_'.$token_name;

		$params = array(
			'title' => Configuration::get('PS_SHOP_NAME'),
			'name' => substr(sha1(rand()), 0, 8),
			'platform' => self::PLATFORM_NAME,
			'front_page_url' => self::getContextShopUrl($context, $language),
			'currency_code' => $context->currency->iso_code,
			'language_code' => $language->iso_code,
			'owner' => array(
				'first_name' => $context->employee->firstname,
				'last_name' => $context->employee->lastname,
				'email' => (!empty($email) ? $email : $context->employee->email),
			),
			'billing_details' => array(
				'country' => $context->country->iso_code
			),
			'api_tokens' => $api_tokens
		);
		$request = new NostoTaggingApiRequest();
		$request->setPath(NostoTaggingApiRequest::PATH_SIGN_UP);
		$request->setContentType('application/json');
		$request->setAuthBasic('', NostoTaggingApiRequest::TOKEN_SIGN_UP);
		$response = $request->post(json_encode($params));

		if ($response->getCode() !== 200)
		{
			NostoTaggingLogger::log(
				__CLASS__.'::'.__FUNCTION__.' - Nosto account could not be created',
				NostoTaggingLogger::LOG_SEVERITY_ERROR,
				$response->getCode()

			);
			return false;
		}

		$result = $response->getJsonResult(true);

		$account_name = self::PLATFORM_NAME.'-'.$params['name'];
		self::setName($account_name, $language_id);
		NostoTaggingApiToken::saveTokens($result, $language_id);

		return true;
	}

	/**
	 * Deletes a nosto account for the given language.
	 *
	 * @param int $language_id the ID of the language model to delete the account for.
	 */
	public static function delete($language_id)
	{
		NostoTaggingConfig::deleteAllFromContext($language_id);
	}

	/**
	 * Returns the account name for given parameters.
	 *
	 * @param null|int $lang_id the ID of the language.
	 * @param null|int $id_shop_group the ID of the shop context.
	 * @param null|int $id_shop the ID of the shop.
	 * @return string|bool|null
	 */
	public static function getName($lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		return NostoTaggingConfig::read(NostoTaggingConfig::ACCOUNT_NAME, $lang_id, $id_shop_group, $id_shop);
	}

	/**
	 * Sets the account name for given parameters.
	 *
	 * @param mixed $value the account name.
	 * @param null|int $lang_id the ID of the language to set the account name for.
	 * @return bool
	 */
	public static function setName($value, $lang_id = null)
	{
		return NostoTaggingConfig::write(NostoTaggingConfig::ACCOUNT_NAME, $value, $lang_id);
	}

	/**
	 * Checks if an account exists for given parameters.
	 *
	 * @param null|int $lang_id the ID of the language.
	 * @param null|int $id_shop_group the ID of the shop context.
	 * @param null|int $id_shop the ID of the shop.
	 * @return string|bool|null
	 */
	public static function exists($lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		return NostoTaggingConfig::exists(NostoTaggingConfig::ACCOUNT_NAME, $lang_id, $id_shop_group, $id_shop);
	}

	/**
	 * Checks if the account has been connected to Nosto.
	 * This is determined by checking if we have all the data needed for make authorized requests to the Nosto API.
	 *
	 * @param null|int $lang_id the ID of the language model to check if the account is connected to nosto with.
	 * @param int|null $id_shop_group
	 * @param int|null $id_shop
	 * @return bool true if the account has been authorized, false otherwise.
	 */
	public static function isConnectedToNosto($lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		if (!self::exists($lang_id))
			return false;
		foreach (NostoTaggingApiToken::$api_token_names as $token_name)
			if (!NostoTaggingApiToken::exists($token_name, $lang_id, $id_shop_group, $id_shop))
				return false;
		return true;
	}

	/**
	 * Returns the current shop's url from the context and language.
	 *
	 * @param Context $context the context.
	 * @param Language $language the language.
	 * @return string the absolute url.
	 */
	public static function getContextShopUrl($context, $language)
	{
		$shop = $context->shop;
		$uri = (!empty($shop->domain_ssl) ? $shop->domain_ssl : $shop->domain).__PS_BASE_URI__;
		return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$uri.'/'.$language->iso_code;
	}
} 