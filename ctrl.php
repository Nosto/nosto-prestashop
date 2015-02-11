<?php
/**
 * 2013-2015 Nosto Solutions Ltd
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
 * @copyright 2013-2015 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/*
 * This is a backwards compatibility script for running module front controllers in all supported Prestashop versions.
 * Version 1.5 and 1.6 does have it's own module controller system, but 1.4 does not. We use this for all versions
 * in order to keep it consistent across versions.
 * The script is meant to run outside of Prestashop, so if _PS_VERSION_ is already defined, we do nothing.
 */
if (!defined('_PS_VERSION_'))
{
	/*
	 * White-list of valid controllers that this script is allowed to run.
	 */
	$controller_white_list = array('oauth2', 'product', 'order');

	/*
	 * If this file is symlinked, then we need to parse the `SCRIPT_FILENAME` to get the path of the PS root dir.
	 * This is because __FILE__ resolves the symlink source path.
	 * We cannot use `/../..` on dirname() of `SCRIPT_FILENAME` as that will also resolve the symlink source path.
	 * So combining as many dirname() calls as needed to step up the tree to the second parent, which should be the
	 * PS root dir, is a solution.
	 */
	if (!empty($_SERVER['SCRIPT_FILENAME']))
		$ps_dir = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])));

	/*
	 * If the PS dir is not set, or we cannot resolve the config file, try to to get the path relative to this file.
	 * This only works if this file is NOT symlinked.
	 */
	if (!isset($ps_dir) || !file_exists($ps_dir.'/config/config.inc.php'))
		$ps_dir = dirname(__FILE__).'/../..';

	$controller_dir = $ps_dir.'/modules/nostotagging/controllers/front';

	require_once($ps_dir.'/config/config.inc.php');

	/*
	 * The "ModuleFrontController" class won't be defined in prestashop 1.4, so define it.
	 */
	if (_PS_VERSION_ < '1.5')
		require_once($controller_dir.'/module.php');

	$controller = Tools::strtolower((string)Tools::getValue('controller'));
	if (!empty($controller) && in_array($controller, $controller_white_list))
	{
		require_once($controller_dir.'/'.$controller.'.php');
		ControllerFactory::getController('NostoTagging'.Tools::ucfirst($controller).'ModuleFrontController')->run();
	}
}
