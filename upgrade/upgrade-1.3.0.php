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

if (!defined('_PS_VERSION_'))
	exit;

/**
 * Upgrades the module to version 1.3.0.
 *
 * Purges existing nosto configs.
 * Removes unused config variables.
 * Registers new hooks.
 * Un-register left/right column hooks.
 *
 * @param NostoTagging $object
 * @return bool
 */
function upgrade_module_1_3_0($object)
{
	// Purge the nosto configs the plugin have created so far and reload the config.
	$config_table = _DB_PREFIX_.'configuration';
	$config_lang_table = $config_table.'_lang';
	Db::getInstance()->execute('
			DELETE `'.$config_lang_table.'` FROM `'.$config_lang_table.'`
			LEFT JOIN `'.$config_table.'`
			ON `'.$config_lang_table.'`.`id_configuration` = `'.$config_table.'`.`id_configuration`
			WHERE `'.$config_table.'`.`name` LIKE "NOSTOTAGGING_%"'
	);
	Db::getInstance()->execute('
			DELETE FROM `'.$config_table.'`
			WHERE `'.$config_table.'`.`name` LIKE "NOSTOTAGGING_%"'
	);
	Configuration::loadConfiguration();

	// Backward compatibility
	if (_PS_VERSION_ < '1.5')
	{
		$object->registerHook('header');
		$object->registerHook('top');
		$object->registerHook('footer');
		$object->registerHook('productfooter');
		$object->registerHook('shoppingCart');
		$object->registerHook('orderConfirmation');
		$object->registerHook('paymentConfirm');
		$object->registerHook('paymentTop');
		$object->registerHook('home');
		$object->registerHook('updateproduct');
		$object->registerHook('deleteproduct');
		$object->registerHook('updateQuantity');
	}
	else
		$object->registerHook('actionObjectUpdateAfter');

	$object->unregisterHook('displayLeftColumn');
	$object->unregisterHook('displayRightColumn');

	return true;
}
