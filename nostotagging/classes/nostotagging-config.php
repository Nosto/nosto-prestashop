<?php

/**
 * Helper class for managing config values.
 */
class NostoTaggingConfig
{
	const ACCOUNT_NAME = 'NOSTOTAGGING_ACCOUNT_NAME';
	const USE_DEFAULT_NOSTO_ELEMENTS = 'NOSTOTAGGING_DEFAULT_ELEMENTS';
	const ADMIN_URL = 'NOSTOTAGGING_ADMIN_URL';

	/**
	 * @param string $name
	 * @param int|null $lang_id
	 * @param int|null $id_shop_group
	 * @param int|null $id_shop
	 * @param bool $lang_fallback
	 * @return bool
	 */
	public static function read($name, $lang_id = null, $id_shop_group = null, $id_shop = null, $lang_fallback = true)
	{
		$value = Configuration::get($name, $lang_id, $id_shop_group, $id_shop);
		if ($value === false && $lang_fallback && $lang_id > 0)
			return Configuration::get($name);
		else
			return $value;
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @param bool $global
	 * @return bool
	 */
	public static function write($name, $value, $global = true)
	{
		$callback = array(
			'Configuration',
			$global ? 'updateGlobalValue' : 'updateValue'
		);
		return call_user_func($callback, (string)$name, $value);
	}

	/**
	 * @param string $name
	 * @param int $lang_id
	 * @param bool $lang_fallback
	 * @return bool
	 */
	public static function exists($name, $lang_id = 0, $lang_fallback = true)
	{
		$value = self::read($name, $lang_id, $lang_fallback);
		return ($value !== false);
	}

	/**
	 * Removes all "NOSTOTAGGING" config entries.
	 */
	public static function purge()
	{
		// todo
		/*
		$result = Db::getInstance()->execute('
		DELETE FROM `'._DB_PREFIX_.bqSQL(self::$definition['table']).'_lang`
		WHERE `'.bqSQL(self::$definition['primary']).'` IN (
			SELECT `'.bqSQL(self::$definition['primary']).'`
			FROM `'._DB_PREFIX_.bqSQL(self::$definition['table']).'`
			WHERE `name` = "'.pSQL($key).'"
		)');

		$result2 = Db::getInstance()->execute('
		DELETE FROM `'._DB_PREFIX_.bqSQL(self::$definition['table']).'`
		WHERE `name` = "'.pSQL($key).'"');

		self::$_cache[self::$definition['table']] = null;
		*/
		return true;
	}
}
