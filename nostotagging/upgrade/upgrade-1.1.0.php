<?php
if (!defined('_PS_VERSION_'))
	exit;

/**
 * Upgrades the module to version 1.1.0.
 *
 * Creates 'nostotagging_customer_link' db table.
 * Registers hooks 'actionPaymentConfirmation' and 'displayPaymentTop'.
 *
 * @param NostoTagging $object
 * @return bool
 */
function upgrade_module_1_1_0($object)
{
	return $object->createCustomerLinkTable()
		&& $object->registerHook('actionPaymentConfirmation')
		&& $object->registerHook('displayPaymentTop');
}