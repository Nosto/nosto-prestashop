<?php

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
			return Db::getInstance()->insert($table, $data, false, true, Db::INSERT, false);
		}
		else
		{
			$data = array(
				'date_upd' => date('Y-m-d H:i:s')
			);
			return Db::getInstance()->update($table, $data, $where, 0, false, true, false);
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
		return isset($_COOKIE[self::NOSTOTAGGING_CUSTOMER_LINK_COOKIE])
			? $_COOKIE[self::NOSTOTAGGING_CUSTOMER_LINK_COOKIE]
			: null;
	}
}
