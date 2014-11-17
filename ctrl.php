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
	$controller = Tools::strtolower((string)Tools::getValue('controller'));
	if (!empty($controller))
	{
		require_once($ps_dir.'/modules/nostotagging/controllers/front/'.$controller.'.php');
		ControllerFactory::getController('NostoTagging'.Tools::ucfirst($controller).'ModuleFrontController')->run();
	}
}
