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

if (!defined('_PS_VERSION_'))
	exit;

/**
 * Upgrades the module to version 2.1.0.
 *
 * Updates the customer link table to link the nosto customer id to the PS id_cart instead of id_customer.
 * Un-registers payment confirmation hooks.
 * Registers order status post update hooks.
 *
 * @param NostoTagging $object
 * @return bool
 */
function upgrade_module_2_1_0($object)
{
	// We just drop the table and re-create as it's easier and we don't want the data we loose.
	return NostoTaggingCustomerLink::dropTable()
        && NostoTaggingCustomerLink::createTable()
        && $object->unregisterHook('paymentConfirm')
        && $object->unregisterHook('actionPaymentConfirmation')
        && $object->registerHook('postUpdateOrderStatus');
}
