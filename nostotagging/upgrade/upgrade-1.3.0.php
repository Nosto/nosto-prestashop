<?php
if (!defined('_PS_VERSION_'))
	exit;

/**
 * Upgrades the module to version 1.3.0.
 *
 * Removes unused config variables.
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

	return Configuration::deleteByName('NOSTOTAGGING_DEFAULT_ELEMENTS')
		// Register Prestashop 1.4 hooks.
		&& $object->registerHook('header')
		&& $object->registerHook('top')
		&& $object->registerHook('footer')
		&& $object->registerHook('productfooter')
		&& $object->registerHook('shoppingCart')
		&& $object->registerHook('orderConfirmation')
		&& $object->registerHook('paymentConfirm')
		&& $object->registerHook('paymentTop')
		&& $object->registerHook('home')
		// Register after object update hook (Prestashop >= 1.5).
		&& $object->registerHook('actionObjectUpdateAfter');
}
