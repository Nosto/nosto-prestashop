<?php

/**
 * Helper class for managing API tokens for the Nosto REST API.
 */
class NostoTaggingApiToken
{
	// API tokens are stored dynamically, with this as the config key base.
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
	 * @param bool $global
	 * @param int $language_id
	 * @return bool
	 */
	public static function set($name, $value, $global = false, $language_id = 0)
	{
		return NostoTaggingConfig::write(self::createConfigKey($name), $value, $global, $language_id);
	}

	/**
	 * Saves API tokens in the config by given language.
	 *
	 * @param array $tokens list of tokens to save, indexed by token name, e.g. "api_sso".
	 * @param int $language_id the ID of the language model to save the tokens for.
	 */
	public static function saveTokens($tokens, $language_id = 0)
	{
		foreach (self::$api_token_names as $token_name)
		{
			$key = 'api_'.$token_name;
			if (isset($tokens[$key]))
				self::set($token_name, $tokens[$key], false, $language_id);
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
