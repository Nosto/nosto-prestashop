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
	 * @param int $id_lang the ID of the language object to create the account for.
	 * @param string|null $email address to use when signing up (default is current employee's email).
	 * @return bool
	 */
	public static function create($context, $id_lang, $email = null)
	{
		$language = new Language($id_lang);
		if (!Validate::isLoadedObject($language))
			return false;

		if (!Validate::isLoadedObject($context->language))
			$context->language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
		if (!Validate::isLoadedObject($context->currency))
			$context->currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
		if (!Validate::isLoadedObject($context->country))
			$context->country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));

		$api_tokens = array();
		foreach (NostoTaggingApiToken::$api_token_names as $token_name)
			$api_tokens[] = 'api_'.$token_name;

		$params = array(
			'title' => Configuration::get('PS_SHOP_NAME'),
			'name' => Tools::substr(sha1(rand()), 0, 8),
			'platform' => self::PLATFORM_NAME,
			'front_page_url' => self::getContextShopUrl($context, $language),
			'currency_code' => $context->currency->iso_code,
			'language_code' => $context->language->iso_code,
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
		$request->setReplaceParams(array('{lang}' => $language->iso_code));
		$request->setContentType('application/json');
		$request->setAuthBasic('', NostoTaggingApiRequest::TOKEN_SIGN_UP);
		$response = $request->post(Tools::jsonEncode($params));

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
		self::setName($account_name, $id_lang);
		NostoTaggingApiToken::saveTokens($result, $id_lang, '', '_token');

		return true;
	}

	/**
	 * Deletes a nosto account for the given language and notifies nosto that account has been deleted.
	 *
	 * @param int $id_lang the ID of the language model to delete the account for.
	 * @param null|int $id_shop_group the ID of the shop context.
	 * @param null|int $id_shop the ID of the shop.
	 */
	public static function delete($id_lang, $id_shop_group = null, $id_shop = null)
	{
		$account_name = self::getName($id_lang, $id_shop_group, $id_shop);
		if (empty($account_name))
			return;
		$token = NostoTaggingApiToken::get('', $id_lang, $id_shop_group, $id_shop); // todo
		if (NostoTaggingConfig::deleteAllFromContext($id_lang, $id_shop_group, $id_shop) && !empty($token))
		{
			$request = new NostoTaggingApiRequest();
			$request->setPath(NostoTaggingApiRequest::PATH_ACCOUNT_DELETED);
			$request->setContentType('application/json');
			$request->setAuthBasic('', $token);
			$response = $request->post(json_encode(array('account_id' => $account_name)));

			if ($response->getCode() !== 200)
				NostoTaggingLogger::log(
					__CLASS__.'::'.__FUNCTION__.' - Failed to notify Nosto about deleted account.',
					NostoTaggingLogger::LOG_SEVERITY_ERROR,
					$response->getCode()
				);
		}
	}

	/**
	 * Deletes all nosto accounts from the system and notifies nosto that accounts are deleted.
	 * @return bool
	 */
	public static function deleteAll()
	{
		foreach (Shop::getShops() as $shop)
		{
			$id_shop = isset($shop['id_shop']) ? $shop['id_shop'] : null;
			foreach (Language::getLanguages(true, $id_shop) as $language)
			{
				$id_shop_group = isset($shop['id_shop_group']) ? $shop['id_shop_group'] : null;
				self::delete($language['id_lang'], $id_shop_group, $id_shop);
			}
		}
		return true;
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