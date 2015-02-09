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
 * Helper class for managing config values.
 */
class NostoTaggingHelperConfig
{
	const ACCOUNT_NAME = 'NOSTOTAGGING_ACCOUNT_NAME';
	const ADMIN_URL = 'NOSTOTAGGING_ADMIN_URL';
	const INSTALLED_VERSION = 'NOSTOTAGGING_INSTALLED_VERSION';

	const TOKEN_CONFIG_PREFIX = 'NOSTOTAGGING_API_TOKEN_';

	/**
	 * Reads and returns a config entry value.
	 *
	 * @param string $name the name of the config entry in the db.
	 * @param int|null $lang_id the language the config entry is saved for.
	 * @param int|null $id_shop_group the shop group id the config entry is saved for.
	 * @param int|null $id_shop the shop id the config entry is saved for.
	 * @return mixed
	 */
	public function read($name, $lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		return Configuration::get($name, $lang_id, $id_shop_group, $id_shop);
	}

	/**
	 * Writes a config entry value to the db.
	 *
	 * @param string $name the name of the config entry to save.
	 * @param mixed $value the value to save.
	 * @param null|int $lang_id the language id to save it for.
	 * @param bool $global if it should be saved for all shops or in current context.
	 * @return bool true is saved, false otherwise.
	 */
	public function write($name, $value, $lang_id = null, $global = false)
	{
		$callback = array(
			'Configuration',
			($global && method_exists('Configuration', 'updateGlobalValue')) ? 'updateGlobalValue' : 'updateValue'
		);
		// Store this value for given language only if specified.
		if (!is_array($value) && !empty($lang_id))
			$value = array($lang_id => $value);
		return call_user_func($callback, (string)$name, $value);
	}

	/**
	 * Checks if a config entry exists for the given criteria.
	 *
	 * @param string $name the name of the config entry.
	 * @param int|null $lang_id the language id it should be saved for.
	 * @param int|null $id_shop_group the shop group id it should be saved for.
	 * @param int|null $id_shop the shop id it should be saved for.
	 * @return bool true if it exists and false otherwise.
	 */
	public function exists($name, $lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		$value = self::read($name, $lang_id, $id_shop_group, $id_shop);
		return ($value !== false && $value !== null && $value !== '');
	}

	/**
	 * Removes all "NOSTOTAGGING_" config entries.
	 *
	 * @return bool always true.
	 */
	public function purge()
	{
		$config_table = _DB_PREFIX_.'configuration';
		$config_lang_table = $config_table.'_lang';

		Db::getInstance()->execute('
			DELETE `'.$config_lang_table.'` FROM `'.$config_lang_table.'`
			LEFT JOIN `'.$config_table.'`
			ON `'.$config_lang_table.'`.`id_configuration` = `'.$config_table.'`.`id_configuration`
			WHERE `'.$config_table.'`.`name` LIKE "NOSTOTAGGING_%"'
		);
		Db::getInstance()->execute('
			DELETE FROM `'.$config_table.'`
			WHERE `'.$config_table.'`.`name` LIKE "NOSTOTAGGING_%"'
		);

		// Reload the config.
		Configuration::loadConfiguration();

		return true;
	}

	/**
	 * Removes all "NOSTOTAGGING_" config entries for the current context and given language.
	 *
	 * @param int|null $id_lang the ID of the language object to remove the config entries for.
	 * @param null|int $id_shop_group the ID of the shop context.
	 * @param null|int $id_shop the ID of the shop.
	 * @return bool
	 */
	public function deleteAllFromContext($id_lang = null, $id_shop_group = null, $id_shop = null)
	{
		if (_PS_VERSION_ >= '1.5')
		{
			if ($id_shop === null)
				$id_shop = (int)Shop::getContextShopID(true);
			if ($id_shop_group === null)
				$id_shop_group = (int)Shop::getContextShopGroupID(true);

			if ($id_shop)
				$context_restriction = ' AND `id_shop` = '.$id_shop;
			elseif ($id_shop_group)
				$context_restriction = ' AND `id_shop_group` = '.$id_shop_group.' AND (`id_shop` IS NULL OR `id_shop` = 0)';
			else
				$context_restriction = ' AND (`id_shop_group` IS NULL OR `id_shop_group` = 0) AND (`id_shop` IS NULL OR `id_shop` = 0)';
		}
		else
			$context_restriction = '';

		$config_table = _DB_PREFIX_.'configuration';
		$config_lang_table = $config_table.'_lang';

		if (!empty($id_lang))
			Db::getInstance()->execute('
				DELETE `'.$config_lang_table.'` FROM `'.$config_lang_table.'`
				INNER JOIN `'.$config_table.'`
				ON `'.$config_lang_table.'`.`id_configuration` = `'.$config_table.'`.`id_configuration`
				WHERE `'.$config_table.'`.`name` LIKE "NOSTOTAGGING_%"
				AND `id_lang` = '.(int)$id_lang
				.$context_restriction
			);
		// We do not actually delete the main config entries, just set them to NULL, as there might me other language
		// specific entries tied to them. The main entries are not used anyways if there are languages defined.
		/* todo: this breaks PS 1.4, apparently you cannot update a value that is NULL in PS 1.4.
		Db::getInstance()->execute('
			UPDATE `'.$config_table.'`
			SET `value` = NULL
			WHERE `name` LIKE "NOSTOTAGGING_%"'
			.$context_restriction
		);*/

		// Reload the config.
		Configuration::loadConfiguration();

		return true;
	}

	/**
	 * Saves the account name to the config for given language.
	 *
	 * @param string $account_name the account name to save.
	 * @param int $id_lang the language to save the account nam for.
	 * @return bool true if saved correctly, false otherwise.
	 */
	public function saveAccountName($account_name, $id_lang)
	{
		return $this->write(self::ACCOUNT_NAME, $account_name, $id_lang);
	}

	/**
	 * Gets a account name from the config.
	 *
	 * @param int $id_lang the language to get the account for.
	 * @param null|int $id_shop_group the shop group to get the account for (defaults to current context).
	 * @param null|int $id_shop the shop to get the account for (defaults to current context).
	 * @return mixed
	 */
	public function getAccountName($id_lang, $id_shop_group = null, $id_shop = null)
	{
		return $this->read(self::ACCOUNT_NAME, $id_lang, $id_shop_group, $id_shop);
	}

	/**
	 * Save the token to the config for given language.
	 *
	 * @param string $token_name the name of the token.
	 * @param string $token_value the value of the token.
	 * @param int $id_lang the language to save the token for.
	 * @return bool true if saved correctly, false otherwise.
	 */
	public function saveToken($token_name, $token_value, $id_lang)
	{
		return $this->write($this->getTokenConfigKey($token_name), $token_value, $id_lang);
	}

	/**
	 * Gets a token from the config by name.
	 *
	 * @param string $token_name the name of the token to get.
	 * @param int $id_lang the language to get the token for.
	 * @param null|int $id_shop_group the shop group to get the token for (defaults to current context).
	 * @param null|int $id_shop the shop to get the token for (defaults to current context).
	 * @return mixed
	 */
	public function getToken($token_name, $id_lang, $id_shop_group = null, $id_shop = null)
	{
		return $this->read(self::getTokenConfigKey($token_name), $id_lang, $id_shop_group, $id_shop);
	}

	/**
	 * Saves the admin url to the config.
	 *
	 * @param string $url the url.
	 * @return bool true if saved successfully, false otherwise.
	 */
	public function saveAdminUrl($url)
	{
		return $this->write(self::ADMIN_URL, $url);
	}

	/**
	 * Get the admin url from the config.
	 *
	 * @return mixed
	 */
	public function getAdminUrl()
	{
		return $this->read(self::ADMIN_URL);
	}

	/**
	 * Saves the installed module version to the config.
	 * Used for PS <= 1.5.4.0 only.
	 *
	 * @param string $version the version.
	 * @return bool true if saved successfully, false otherwise.
	 */
	public function saveInstalledVersion($version)
	{
		return $this->write(self::INSTALLED_VERSION, $version, null, true);
	}

	/**
	 * Get the installed module version.
	 * Used for PS <= 1.5.4.0 only.
	 *
	 * @return mixed
	 */
	public function getInstalledVersion()
	{
		return $this->read(self::INSTALLED_VERSION);
	}

	/**
	 * Gets the fully qualified config key for a token name.
	 *
	 * @param string $name the name of the token.
	 * @return string the fully qualified config key.
	 */
	protected function getTokenConfigKey($name)
	{
		return self::TOKEN_CONFIG_PREFIX.Tools::strtoupper($name);
	}
}
