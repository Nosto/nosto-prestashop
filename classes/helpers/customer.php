<?php
/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for managing the link between Prestashop shopping carts and Nosto users.
 * This link is used to create server side order confirmations through the Nosto REST API.
 */
class NostoTaggingHelperCustomer
{
    const TABLE_NAME_CUSTOMER_LINK = 'nostotagging_customer_link';
    const TABLE_NAME_CUSTOMER_REFERENCE = 'nostotagging_customer_reference';

    /**
     * Returns the customer link table name.
     *
     * @return string
     */
    public static function getCustomerLinkTableName()
    {
        return pSQL(_DB_PREFIX_.self::TABLE_NAME_CUSTOMER_LINK);
    }

    /**
     * Returns the customer reference table name.
     *
     * @return string
     */
    public static function getCustomerReferenceTableName()
    {
        return pSQL(_DB_PREFIX_.self::TABLE_NAME_CUSTOMER_REFERENCE);
    }

    /**
     * Creates the customer link table in db if it does not exist.
     *
     * @return bool
     */
    public function createCustomerLinkTable()
    {
        $table = self::getCustomerLinkTableName();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (
			`id_cart` INT(10) UNSIGNED NOT NULL,
			`id_nosto_customer` VARCHAR(64) NOT NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NULL,
			PRIMARY KEY (`id_cart`, `id_nosto_customer`)
		) ENGINE '._MYSQL_ENGINE_;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Creates the customer reference table in db if it does not exist.
     *
     * @return bool
     */
    public function createCustomerReferenceTable()
    {
        $table = self::getCustomerReferenceTableName();
        $sql = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (
			`id_customer` INT(10) UNSIGNED NOT NULL,
			`customer_reference` VARCHAR(32) NOT NULL,
			PRIMARY KEY (`id_customer`)
		) ENGINE '._MYSQL_ENGINE_;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Drops the customer link table from db if it exists.
     *
     * @return bool
     */
    public static function dropCustomerLinkTable()
    {
        $table = self::getCustomerLinkTableName();

        return Db::getInstance()->execute('DROP TABLE IF EXISTS `'.$table.'`');
    }

    /**
     * Updates the current customers Nosto ID in the reference table.
     *
     * @return bool true if updated correctly and false otherwise.
     */
    public function updateNostoId()
    {
        $context = Context::getContext();
        if (empty($context->cart->id)) {
            return false;
        }

        $id_nosto_customer = NostoTagging::readNostoCookie();
        if (empty($id_nosto_customer)) {
            return false;
        }

        $table = self::getCustomerLinkTableName();
        $id_cart = (int)$context->cart->id;
        $id_nosto_customer = pSQL($id_nosto_customer);
        $where = '`id_cart` = '.$id_cart.' AND `id_nosto_customer` = "'.$id_nosto_customer.'"';
        $existing_link = Db::getInstance()->getRow('SELECT * FROM `'.$table.'` WHERE '.$where);
        if (empty($existing_link)) {
            $data = array(
                'id_cart' => $id_cart,
                'id_nosto_customer' => $id_nosto_customer,
                'date_add' => date('Y-m-d H:i:s')
            );
                return Db::getInstance()->insert($table, $data, false, true, Db::INSERT, false);
        } else {
            $data = array(
                'date_upd' => date('Y-m-d H:i:s')
            );
            return Db::getInstance()->update($table, $data, $where, 0, false, true, false);
        }
    }

    /**
     * Returns the customers Nosto ID.
     *
     * @param Order $order the order to get the customer from.
     * @return bool|string the customers Nosto ID or false if not found.
     */
    public function getNostoId(Order $order)
    {
        $table = self::getCustomerLinkTableName();
        $id_cart = (int)$order->id_cart;
        $sql = 'SELECT `id_nosto_customer` FROM `'.$table.'` WHERE `id_cart` = '.$id_cart.' ORDER BY `date_add` ASC';

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Returns the customer reference.
     *
     * @param Customer $customer
     * @return bool|string the customer reference
     */
    public function getCustomerReference(Customer $customer)
    {
        $sql = sprintf(
            'SELECT `customer_reference` FROM `%s` WHERE `id_customer` = \'%d\'',
            self::getCustomerReferenceTableName(),
            (int)$customer->id
        );

        return Db::getInstance()->getValue($sql);
    }

    /**
     * Saves customer reference into a db. This method checks if the customer id already
     * exists in the table and updates or inserts accordingly.
     *
     * @param Customer $customer
     * @param $reference
     * @return bool
     */
    public function saveCustomerReference(Customer $customer, $reference)
    {
        $table = self::getCustomerReferenceTableName();
        $customer_reference = pSQL($reference);
        $customer_id = (int)$customer->id;
        $data = array(
            'id_customer' => $customer_id,
            'customer_reference' => $customer_reference
        );
        $existing_id = Db::getInstance()->getRow(
            sprintf(
                'SELECT id_customer FROM `%s` WHERE id_customer = \'%d\'',
                $table,
                $customer_id
            )
        );
        if (empty($existing_id)) {
            return Db::getInstance()->insert($table, $data, false, true, Db::INSERT, false);
        } else {
            unset($data['id_customer']);
            $where = sprintf(
                'id_customer=\'%d\'',
                $customer_id
            );
            return Db::getInstance()->update($table, $data, $where, 0, false, true, false);
        }
    }

    /**
     * Generates new customer reference
     *
     * @param Customer $customer
     * @return string
     */
    public function generateCustomerReference(Customer $customer)
    {
        $hash = md5($customer->id.$customer->email);
        $uuid = uniqid(
            Tools::substr($hash, 0, 8),
            true
        );

        return $uuid;
    }

    /**
     * Creates tables needed for the Nosto plug-in
     *
     * @return bool
     */
    public function createTables()
    {
        $success = true;
        if (!$this->createCustomerLinkTable()) {
            $success = false;
        }
        if (!$success || !$this->createCustomerReferenceTable()) {
            $success = false;
        }

        return $success;
    }

    /**
     * Drop tables created by Nosto plug-in. Note that table
     * nosto_customer_reference is not dropped during the uninstall.
     *
     * @return bool
     */
    public static function dropTables()
    {
        $success = true;
        if (!self::dropCustomerLinkTable()) {
            $success = false;
        }

        return $success;
    }
}
