<?php

/**
 * Helper class for managing the link between Prestashop shopping carts and Nosto users.
 * This link is used to create server side order confirmations through the Nosto REST API.
 */
class NostoTaggingHelperCustomer
{
	const TABLE_NAME = 'nostotagging_customer_link';
	const COOKIE_NAME = '2c_cId';

	/**
	 * Returns the reference table name.
	 *
	 * @return string
	 */
	public static function getTableName()
	{
		return _DB_PREFIX_.self::TABLE_NAME;
	}

	/**
	 * Creates the reference table in db if it does not exist.
	 *
	 * @return bool
	 */
	public static function createTable()
	{
		$table = self::getTableName();
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (
			`id_cart` INT(10) UNSIGNED NOT NULL,
			`id_nosto_customer` VARCHAR(255) NOT NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NULL,
			PRIMARY KEY (`id_cart`, `id_nosto_customer`)
		) ENGINE '._MYSQL_ENGINE_;
		return Db::getInstance()->execute($sql);
	}

	/**
	 * Drops the reference table from db if it exists.
	 *
	 * @return bool
	 */
	public static function dropTable()
	{
		$table = self::getTableName();
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'.$table.'`');
	}

	/**
	 * Updates the current customers Nosto ID in the reference table.
	 *
	 * @return bool true if updated correctly and false otherwise.
	 */
	public static function updateNostoId()
	{
		$context = Context::getContext();
		if (empty($context->cart->id))
			return false;

		$id_nosto_customer = self::readCookieValue();
		if (empty($id_nosto_customer))
			return false;

		$table = self::getTableName();
		$id_cart = (int)$context->cart->id;
		$id_nosto_customer = pSQL($id_nosto_customer);
		$where = '`id_cart` = '.$id_cart.' AND `id_nosto_customer` = "'.$id_nosto_customer.'"';
		$existing_link = Db::getInstance()->getRow('SELECT * FROM `'.$table.'` WHERE '.$where);
		if (empty($existing_link))
		{
			$data = array(
				'id_cart' => $id_cart,
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
	 * Returns the customers Nosto ID.
	 *
	 * @param Order $order the order to get the customer from.
	 * @return bool|string the customers Nosto ID or false if not found.
	 */
	public static function getNostoId(Order $order)
	{
		$table = self::getTableName();
		$id_cart = (int)$order->id_cart;
		$sql = 'SELECT `id_nosto_customer` FROM `'.$table.'` WHERE `id_cart` = '.$id_cart.' ORDER BY `date_add` ASC';
		return Db::getInstance()->getValue($sql);
	}

	/**
	 * Reads the Nosto cookie value and returns it.
	 *
	 * @return null the cookie value, or null if not set.
	 */
	protected static function readCookieValue()
	{
		// We use the $_COOKIE global directly here, instead of the Prestashop cookie class, as we are accessing a
		// nosto cookie that have been set by the JavaScript loaded from nosto.com. We read it to keep a mapping of
		// the Nosto user ID and the Prestashop user ID so we can identify which user actually completed an order.
		// We do this for tracking whether or not to send abandoned cart emails.
		return isset($_COOKIE[self::COOKIE_NAME])
			? $_COOKIE[self::COOKIE_NAME]
			: null;
	}
}
