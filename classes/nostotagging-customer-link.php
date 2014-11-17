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
 * Helper class for managing the link between Prestashop customers and Nosto users.
 * This link is used to create server side order confirmations through the Nosto REST API.
 */
class NostoTaggingCustomerLink
{
	const NOSTOTAGGING_CUSTOMER_LINK_TABLE = 'nostotagging_customer_link';
	const NOSTOTAGGING_CUSTOMER_LINK_COOKIE = '2c_cId';

	/**
	 * Returns the table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return _DB_PREFIX_.self::NOSTOTAGGING_CUSTOMER_LINK_TABLE;
	}

	/**
	 * Creates the customer link table in db if it does not exist.
	 *
	 * @return bool
	 */
	public static function createTable()
	{
		$table = self::getTableName();
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (
			`id_customer` INT(10) UNSIGNED NOT NULL,
			`id_nosto_customer` VARCHAR(255) NOT NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NULL,
			PRIMARY KEY (`id_customer`, `id_nosto_customer`)
		) ENGINE '._MYSQL_ENGINE_;
		return Db::getInstance()->execute($sql);
	}

	/**
	 * Drops the customer link table from db if it exists.
	 *
	 * @return bool
	 */
	public static function dropTable()
	{
		$table = self::getTableName();
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'.$table.'`');
	}

	/**
	 * Updates a customer link in the table.
	 *
	 * @param NostoTagging $module
	 * @return bool
	 */
	public static function updateLink(NostoTagging $module)
	{
		$context = $module->getContext();
		if (empty($context->customer->id))
			return false;

		$id_nosto_customer = self::readCookieValue();
		if (empty($id_nosto_customer))
			return false;

		$table = self::getTableName();
		$id_customer = (int)$context->customer->id;
		$id_nosto_customer = pSQL($id_nosto_customer);
		$where = '`id_customer` = '.$id_customer.' AND `id_nosto_customer` = "'.$id_nosto_customer.'"';
		$existing_link = Db::getInstance()->getRow('SELECT * FROM `'.$table.'` WHERE '.$where);
		if (empty($existing_link))
		{
			$data = array(
				'id_customer' => $id_customer,
				'id_nosto_customer' => $id_nosto_customer,
				'date_add' => date('Y-m-d H:i:s')
			);
			if (_PS_VERSION_ >= '1.5')
				return Db::getInstance()->insert($table, $data, false, true, Db::INSERT, false);
			else
				return Db::getInstance()->autoExecute($table, $data, 'INSERT');
		}
		else
		{
			$data = array(
				'date_upd' => date('Y-m-d H:i:s')
			);
			if (_PS_VERSION_ >= '1.5')
				return Db::getInstance()->update($table, $data, $where, 0, false, true, false);
			else
				return Db::getInstance()->autoExecute($table, $data, 'UPDATE', $where);
		}
	}

	/**
	 * Returns the nosto customer id if one exists in the link table.
	 *
	 * @param NostoTagging $module
	 * @return bool|mixed
	 */
	public static function getNostoCustomerId(NostoTagging $module)
	{
		$context = $module->getContext();
		if (empty($context->customer->id))
			return false;

		$table = self::getTableName();
		$id_customer = (int)$context->customer->id;
		$sql = 'SELECT `id_nosto_customer` FROM `'.$table.'` WHERE `id_customer` = '.$id_customer.' ORDER BY `date_add` ASC';
		return Db::getInstance()->getValue($sql);
	}

	/**
	 * Reads the Nosto cookie value and returns it.
	 *
	 * @return null the cookie value, or null if not set.
	 */
	protected static function readCookieValue()
	{
		// todo: can we use Context::getContext()->cookie??
		return isset($_COOKIE[self::NOSTOTAGGING_CUSTOMER_LINK_COOKIE])
			? $_COOKIE[self::NOSTOTAGGING_CUSTOMER_LINK_COOKIE]
			: null;
	}
}
