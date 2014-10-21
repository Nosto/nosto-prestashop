<?php
if (!defined('_PS_VERSION_'))
	exit;

/**
 * Upgrades the module to version 1.3.0.
 *
 * Move global config variables to language specific ones.
 * Removes unused config variables.
 * Registers new hooks.
 *
 * @param NostoTagging $object
 * @return bool
 */
function upgrade_module_1_3_0($object)
{
	// Move global configs to language specific ones.
	$default_lang_id = (int)Configuration::get('PS_LANG_DEFAULT');
	if (!empty($default_lang_id))
	{
		$account_name = Configuration::get('NOSTOTAGGING_ACCOUNT_NAME');
		if (!empty($account_name))
		{
			NostoTaggingAccount::setName($account_name, $default_lang_id);
			Configuration::set('NOSTOTAGGING_ACCOUNT_NAME', '');
		}
		$sso_token = Configuration::get('NOSTOTAGGING_SSO_TOKEN');
		if (!empty($sso_token))
		{
			NostoTaggingApiToken::set('sso', $sso_token, $default_lang_id);
			Configuration::set('NOSTOTAGGING_SSO_TOKEN', '');
		}
	}

	Configuration::deleteByName('NOSTOTAGGING_DEFAULT_ELEMENTS');

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

	return true;
}
