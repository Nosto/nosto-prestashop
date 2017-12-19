<?php
/**
 * 2013-2017 Nosto Solutions Ltd
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
 * @copyright 2013-2017 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for managing the link between Prestashop shopping carts and Nosto users.
 * This link is used to create server side order confirmations through the Nosto REST API.
 */
class NostoCustomerManager
{
    const TABLE_NAME_CUSTOMER_LINK = 'nostotagging_customer_link';
    const TABLE_NAME_CUSTOMER_REFERENCE = 'nostotagging_customer_reference';

    /**
     * Returns the name of the customer link table name with the database prefix. The value
     * is not stored as constant as the prefix can be customised
     *
     * @return string the prefixed name of the table
     */
    private static function getCustomerLinkTableName()
    {
        return pSQL(_DB_PREFIX_ . self::TABLE_NAME_CUSTOMER_LINK);
    }

    /**
     * Returns the name of the customer reference table name with the database prefix. The value
     * is not stored as constant as the prefix can be customised
     *
     * @return string the prefixed name of the table
     */
    private static function getCustomerReferenceTableName()
    {
        return pSQL(_DB_PREFIX_ . self::TABLE_NAME_CUSTOMER_REFERENCE);
    }

    /**
     * Creates the customer-link table in DB if it does not exist. The customer-link table stores
     * the value of the 2c.cid cookie issued by Nosto for identify API conversions correctly
     *
     * @return bool if the creation of the table was successful
     */
    public static function createCustomerLinkTable()
    {
        $table = self::getCustomerLinkTableName();
        $sql = 'CREATE TABLE IF NOT EXISTS `' . $table . '` (
			`id_cart` INT(10) UNSIGNED NOT NULL,
			`id_nosto_customer` VARCHAR(64) NOT NULL,
			`restore_cart_hash` VARCHAR(64) NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NULL,
			PRIMARY KEY (`id_cart`, `id_nosto_customer`)
		) ENGINE ' . _MYSQL_ENGINE_;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Creates the customer-reference table in DB if it does not exist. The customer-reference table
     * stores a unique reference for each customer used when generating the restore-cart link.
     *
     * @return bool if the creation of the table was successful
     */
    public static function createCustomerReferenceTable()
    {
        $table = self::getCustomerReferenceTableName();
        $sql = 'CREATE TABLE IF NOT EXISTS `' . $table . '` (
			`id_customer` INT(10) UNSIGNED NOT NULL,
			`customer_reference` VARCHAR(32) NOT NULL,
			PRIMARY KEY (`id_customer`)
		) ENGINE ' . _MYSQL_ENGINE_;

        return Db::getInstance()->execute($sql);
    }

    /**
     * Add restore-cart hash column to customer link table.
     *
     * @return bool if the creation of the table was successful
     */
    public static function addRestoreCartHashColumnToCustomerLinkTable()
    {
        $table = self::getCustomerLinkTableName();
        $sql = 'ALTER TABLE ' . $table . ' 
            ADD COLUMN `restore_cart_hash` VARCHAR(64) NULL AFTER `id_nosto_customer`';

        return Db::getInstance()->execute($sql);
    }

    /**
     * Updates the current customers Nosto ID in the reference table.
     *
     * @return bool true if updated correctly and false otherwise.
     */
    public static function updateNostoId()
    {
        $context = Context::getContext();
        if (empty($context->cart->id)) {
            return false;
        }

        $idNostoCustomer = NostoHelperCookie::readNostoCookie();
        if (empty($idNostoCustomer)) {
            return false;
        }

        $table = self::getCustomerLinkTableName();
        $idCart = (int)$context->cart->id;
        $idNostoCustomer = pSQL($idNostoCustomer);
        $restoreCartHash = self::generateRestoreCartHash();
        $where = '`id_cart` = ' . $idCart . ' AND `id_nosto_customer` = "' . $idNostoCustomer . '"';
        $existingLink = Db::getInstance()->getRow('SELECT * FROM `' . $table . '` WHERE ' . $where);
        if (empty($existingLink)) {
            $data = array(
                'id_cart' => $idCart,
                'id_nosto_customer' => $idNostoCustomer,
                'date_add' => date('Y-m-d H:i:s'),
                'restore_cart_hash' => $restoreCartHash
            );
            return Db::getInstance()->insert($table, $data, false, true, Db::INSERT, false);
        } else {
            $data = array(
                'date_upd' => date('Y-m-d H:i:s')
            );

            if (!is_array($existingLink) || !$existingLink['restore_cart_hash']) {
                $data['restore_cart_hash'] = $restoreCartHash;
            }

            return Db::getInstance()->update($table, $data, $where, 0, false, true, false);
        }
    }

    /**
     * Generate unique hash for restore cart
     * Size of it equals to or less than restore_cart_hash column length
     *
     * @return string
     */
    private static function generateRestoreCartHash()
    {
        return hash('sha256', uniqid(NostoTagging::MODULE_NAME));
    }

    /**
     * Returns the Nosto customer id for a given order by using the order's cart identifier
     * as the key
     *
     * @param Order $order the order whose 2c.cid cookie to look up
     * @return string|null the customers Nosto id or false if not found.
     */
    public static function getNostoId(Order $order)
    {
        $table = self::getCustomerLinkTableName();
        $cartId = (int)$order->id_cart;
        $sql = 'SELECT `id_nosto_customer` FROM `' . $table . '` WHERE `id_cart` = ' . $cartId .
            ' ORDER BY `date_add` ASC';

        $result = Db::getInstance()->getValue($sql);
        if (is_string($result)) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Returns the restore cart hash code for a given cart id
     *
     * @param int $cartId cart Id
     * @return string|null the restore cart hash code
     */
    public static function getRestoreCartHash($cartId)
    {
        $table = self::getCustomerLinkTableName();
        $sql = 'SELECT `restore_cart_hash` FROM `' . $table
            . '` WHERE `restore_cart_hash` IS NOT NULL AND `id_cart` = ' . $cartId
            . ' ORDER BY `date_add` ASC';

        $result = Db::getInstance()->getValue($sql);
        if (is_string($result)) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Resolves the cart (quote) by the given hash
     *
     */
    public static function getCartId($restoreCartHash)
    {
        $table = self::getCustomerLinkTableName();
        $sql = 'SELECT `id_cart` FROM `' . $table . '` WHERE `restore_cart_hash` = "' . pSQL($restoreCartHash) . '"' .
            ' ORDER BY `date_add` ASC';

        $result = Db::getInstance()->getValue($sql);
        if (is_string($result)) {
            return $result;
        } else {
            return null;
        }
    }

    /**
     * Returns the customer reference associated with a customer by using the customer's identifier
     * as the key
     *
     * @param Customer $customer the customer whose reference to look up
     * @return bool|string the customers reference or false if not found.
     */
    public static function getCustomerReference(Customer $customer)
    {
        $table = self::getCustomerReferenceTableName();
        $customerId = (int)$customer->id;
        $sql = 'SELECT `customer_reference` FROM `' . $table . '` WHERE `id_customer` = ' . $customerId;

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
    public static function saveCustomerReference(Customer $customer, $reference)
    {
        $table = self::getCustomerReferenceTableName();
        $customerReference = pSQL($reference);
        $customerId = (int)$customer->id;
        $data = array(
            'id_customer' => $customerId,
            'customer_reference' => $customerReference
        );
        $existingId = Db::getInstance()->getRow(
            sprintf(
                'SELECT id_customer FROM `%s` WHERE id_customer = \'%d\'',
                $table,
                $customerId
            )
        );
        if (empty($existingId)) {
            return Db::getInstance()->insert($table, $data, false, true, Db::INSERT, false);
        } else {
            unset($data['id_customer']);
            $where = sprintf(
                'id_customer=\'%d\'',
                $customerId
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
    public static function generateCustomerReference(Customer $customer)
    {
        $hash = md5($customer->id . $customer->email);
        $uuid = uniqid(Tools::substr($hash, 0, 8), true);

        return $uuid;
    }

    /**
     * Drops the customer-link table from db if it exists. There is no corresponding method
     * for the customer-reference table.
     *
     * @return bool if the dropping of the table was successful
     */
    private static function dropCustomerLinkTable()
    {
        $table = self::getCustomerLinkTableName();

        return Db::getInstance()->execute('DROP TABLE IF EXISTS `' . $table . '`');
    }

    /**
     * Creates both the the customer reference table and the customer link table needed by the
     * Nosto plug-in
     *
     * @return bool if the creation of both tables was successful
     */
    public static function createTables()
    {
        return NostoCustomerManager::createCustomerLinkTable() && NostoCustomerManager::createCustomerReferenceTable();
    }

    /**
     * Drop tables created by Nosto plug-in. Note that table customer-reference is not dropped
     * during the uninstall as the merchant may upgrade the plugin and we want to preserve
     * all existing references
     *
     * @return bool if the dropping of the table was successful
     */
    public static function dropTables()
    {
        return self::dropCustomerLinkTable();
    }
}
