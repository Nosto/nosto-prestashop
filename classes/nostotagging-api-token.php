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
 *  @author Nosto Solutions Ltd <contact@nosto.com>
 *  @copyright  2013-2014 Nosto Solutions Ltd
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for managing API tokens for the Nosto REST API.
 */
class NostoTaggingApiToken
{
	const NOSTOTAGGING_CONFIG_BASE = 'NOSTOTAGGING_API_TOKEN_';

	/**
	 * @var array list of api tokens to request from Nosto, prefixed with "api_" when returned by Nosto.
	 */
	public static $api_token_names = array(
		'sso',
		'products'
	);

	/**
	 * Getter for an API token by name.
	 *
	 * @param string $name
	 * @param null|int $lang_id the ID of the language.
	 * @param null|int $id_shop_group the ID of the shop context.
	 * @param null|int $id_shop the ID of the shop.
	 * @return string
	 */
	public static function get($name, $lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		return NostoTaggingConfig::read(self::createConfigKey($name), $lang_id, $id_shop_group, $id_shop);
	}

	/**
	 * Setter for an API token by name.
	 *
	 * @param string $name
	 * @param string $value
	 * @param null|int $lang_id
	 * @return bool
	 */
	public static function set($name, $value, $lang_id = null)
	{
		return NostoTaggingConfig::write(self::createConfigKey($name), $value, $lang_id);
	}

	/**
	 * Checks if an API token exists.
	 *
	 * @param string $name
	 * @param int|null $lang_id
	 * @param int|null $id_shop_group
	 * @param int|null $id_shop
	 * @return bool
	 */
	public static function exists($name, $lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		return NostoTaggingConfig::exists(self::createConfigKey($name), $lang_id, $id_shop_group, $id_shop);
	}

	/**
	 * Saves API tokens in the config by given language.
	 *
	 * @param array $tokens list of tokens to save, indexed by token name, e.g. "api_sso".
	 * @param null|int $lang_id the ID of the language model to save the tokens for.
	 * @param string $prefix optional prefix to set for the token name when doing lookup in $result.
	 * @param string $postfix optional postfix to set for the token name when doing lookup in $result.
	 */
	public static function saveTokens($tokens, $lang_id = null, $prefix = '', $postfix = '')
	{
		foreach (self::$api_token_names as $token_name)
		{
			$key = $prefix.$token_name.$postfix;
			if (isset($tokens[$key]))
				self::set($token_name, $tokens[$key], $lang_id);
		}
	}

	/**
	 * Builds and returns the config key to store and fetch an api token with.
	 *
	 * @param string $name
	 * @return string
	 */
	protected static function createConfigKey($name)
	{
		return self::NOSTOTAGGING_CONFIG_BASE.strtoupper($name);
	}
}
