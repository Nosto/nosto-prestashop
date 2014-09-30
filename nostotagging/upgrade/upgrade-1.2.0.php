<?php
if (!defined('_PS_VERSION_'))
	exit;

/**
 * Upgrades the module to version 1.2.0.
 *
 * Removes unused config variables.
 *
 * @return bool
 */
function upgrade_module_1_2_0()
{
	return Configuration::deleteByName('NOSTOTAGGING_INJECT_SLOTS');
}