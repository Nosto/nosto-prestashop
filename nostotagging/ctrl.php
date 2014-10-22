<?php

// This script is meant to run outside of prestashop, so if the prestashop version is already defined, we do nothing.
if (!defined('_PS_VERSION_'))
{
	require_once(dirname(__FILE__).'/../../config/config.inc.php');
	// The ModuleFrontController class won't be defined in prestashop 1.4, so define it.
	if (_PS_VERSION_ < '1.5')
		require_once(dirname(__FILE__).'/../../modules/nostotagging/controllers/front/ModuleFrontController.php');
	$controller= Tools::getValue('controller');
	require_once(dirname(__FILE__).'/../../modules/nostotagging/controllers/front/'.$controller.'.php');
	ControllerFactory::getController('NostoTagging'.$controller.'ModuleFrontController')->run();
}
