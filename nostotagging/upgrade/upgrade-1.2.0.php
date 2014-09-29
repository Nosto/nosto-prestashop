<?php
if (!defined('_PS_VERSION_'))
	exit;

/**
 * Upgrades the module to version 1.2.0.
 *
 * Removes unused config variables.
 *
 * @param NostoTagging $object
 * @return bool
 */
function upgrade_module_1_2_0($object)
{
	return Configuration::deleteByName('NOSTOTAGGING_DEFAULT_ELEMENTS')
		&& Configuration::deleteByName('NOSTOTAGGING_INJECT_SLOTS');
}