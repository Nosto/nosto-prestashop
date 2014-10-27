<?php

/*
 * This is a backwards compatibility script for running module front controllers in Prestashop 1.4.
 * The script is meant to run outside of Prestashop, so if _PS_VERSION_ is already defined, we do nothing.
 */
if (!defined('_PS_VERSION_'))
{
	$ps_dir = dirname(__FILE__).'/../..';
	require_once($ps_dir.'/config/config.inc.php');
	/*
	 * The "ModuleFrontController" class won't be defined in prestashop 1.4, so define it.
	 */
	if (_PS_VERSION_ < '1.5')
		require_once($ps_dir.'/modules/nostotagging/backward_compatibility/ModuleFrontController.php');
	$controller = strtolower((string)Tools::getValue('controller'));
	if (!empty($controller))
	{
		require_once($ps_dir.'/modules/nostotagging/controllers/front/'.$controller.'.php');
		ControllerFactory::getController('NostoTagging'.ucfirst($controller).'ModuleFrontController')->run();
	}
}
