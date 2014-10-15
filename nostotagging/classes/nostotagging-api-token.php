<?php

/**
 * Helper class for managing API tokens for the Nosto REST API.
 */
class NostoTaggingApiToken
{
	// API tokens are stored dynamically, with this as the config key base.
	const NOSTOTAGGING_CONFIG_BASE = 'NOSTOTAGGING_API_TOKEN_';

	/**
	 * Getter for an API token by name.
	 *
	 * @param string $name
	 * @param int $lang_id
	 * @param bool $lang_fallback
	 * @return string
	 */
	public static function get($name, $lang_id = 0, $lang_fallback = true)
	{
		return NostoTaggingConfig::read(self::createConfigKey($name), $lang_id, $lang_fallback);
	}

	/**
	 * Setter for an API token by name.
	 *
	 * @param string $name
	 * @param string $value
	 * @param bool $global
	 * @return bool
	 */
	public static function set($name, $value, $global = false)
	{
		return NostoTaggingConfig::write(self::createConfigKey($name), $value, $global);
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
