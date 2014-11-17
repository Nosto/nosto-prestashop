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
 * Helper class for managing config values.
 */
class NostoTaggingConfig
{
	const ACCOUNT_NAME = 'NOSTOTAGGING_ACCOUNT_NAME';
	const ADMIN_URL = 'NOSTOTAGGING_ADMIN_URL';

	/**
	 * @param string $name
	 * @param int|null $lang_id
	 * @param int|null $id_shop_group
	 * @param int|null $id_shop
	 * @return bool
	 */
	public static function read($name, $lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		return Configuration::get($name, $lang_id, $id_shop_group, $id_shop);
	}

	/**
	 * @param string $name
	 * @param mixed $value
	 * @param null|int $lang_id
	 * @param bool $global
	 * @return bool
	 */
	public static function write($name, $value, $lang_id = null, $global = false)
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
	 * @param string $name
	 * @param int|null $lang_id
	 * @param int|null $id_shop_group
	 * @param int|null $id_shop
	 * @return bool
	 */
	public static function exists($name, $lang_id = null, $id_shop_group = null, $id_shop = null)
	{
		$value = self::read($name, $lang_id, $id_shop_group, $id_shop);
		return ($value !== false && $value !== null && $value !== '');
	}

	/**
	 * Removes all "NOSTOTAGGING_" config entries.
	 */
	public static function purge()
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
	 * @param int|null $language_id the ID of the language object to remove the config entries for.
	 * @return bool
	 */
	public static function deleteAllFromContext($language_id = null)
	{
		if (_PS_VERSION_ >= '1.5')
		{
			$id_shop = (int)Shop::getContextShopID(true);
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

		if (!empty($language_id))
			Db::getInstance()->execute('
				DELETE `'.$config_lang_table.'` FROM `'.$config_lang_table.'`
				INNER JOIN `'.$config_table.'`
				ON `'.$config_lang_table.'`.`id_configuration` = `'.$config_table.'`.`id_configuration`
				WHERE `'.$config_table.'`.`name` LIKE "NOSTOTAGGING_%"
				AND `id_lang` = '.(int)$language_id
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
}
