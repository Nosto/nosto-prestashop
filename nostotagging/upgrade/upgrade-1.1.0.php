<?php
if (!defined('_PS_VERSION_'))
	exit;

/**
 * Upgrades the module to version 1.1.0.
 *
 * Creates 'nostotagging_customer_link' db table.
 * Registers hooks 'actionPaymentConfirmation', 'displayPaymentTop' and 'displayHome'.
 * Sets default value for "inject category and search page recommendations" to 1.
 * Removes unused "NOSTOTAGGING_SERVER_ADDRESS" config variable.
 *
 * @param NostoTagging $object
 * @return bool
 */
function upgrade_module_1_1_0($object)
{
	return $object->createCustomerLinkTable()
		&& $object->registerHook('actionPaymentConfirmation')
		&& $object->registerHook('displayPaymentTop')
		&& $object->registerHook('displayHome')
		&& $object->setInjectSlots(1, true)
		&& Configuration::deleteByName('NOSTOTAGGING_SERVER_ADDRESS')
		&& Configuration::deleteByName('NOSTOTAGGING_TOP_SELLERS_CMS_ID');
}