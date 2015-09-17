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

/**
 * Admin controller used for handling module configuration page requests.
 */
class AdminNostoConfigController
{
	/**
	 * @var NostoTagging the module instance.
	 */
	private $module;

	/**
	 * @var Context the module context.
	 */
	private $context;

	/**
	 * @var array list of all available languages for context.
	 */
	private $languages = array();

	/**
	 * @var array the currency selected language to configure nosto for.
	 */
	private $language = array('id_lang' => 0, 'name' => '', 'iso_code' => '');

	/**
	 * @var string the redirect URL to call after POST requests.
	 */
	private $redirect_url;

	/**
	 * Constructor.
	 * Initializes required member variables.
	 *
	 * @param NostoTagging $module
	 * @param string $redirect_url
	 */
	public function __construct(NostoTagging $module, $redirect_url)
	{
		$this->module = $module;
		$this->context = $module->getContext();
		$this->languages = Language::getLanguages(true, $this->context->shop->id);
		$this->redirect_url = $redirect_url;
	}

	/**
	 * Runs the controller action.
	 */
	public function runAction()
	{
		$this->beforeAction();

		if ($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			if (Tools::isSubmit('submit_nostotagging_new_account'))
				$this->actionNewAccount();
			elseif (Tools::isSubmit('submit_nostotagging_authorize_account'))
				$this->actionConnectAccount();
			elseif (Tools::isSubmit('submit_nostotagging_reset_account'))
				$this->actionDeleteAccount();
			elseif (Tools::isSubmit('submit_nostotagging_update_account'))
				$this->actionUpdateAccount();
			elseif (Tools::isSubmit('submit_nostotagging_update_exchange_rates'))
				$this->actionUpdateExchangeRates();
			elseif (Tools::isSubmit('submit_nostotagging_advanced_settings'))
				$this->actionUpdateAdvancedSettings();

			$this->redirect();
		}

		$this->afterAction();
	}

	/**
	 * Returns the language the module config is shown for.
	 *
	 * @return array the language.
	 */
	public function getLanguage()
	{
		return $this->language;
	}

	/**
	 * POST action for creating a new Nosto account.
	 */
	protected function actionNewAccount()
	{
		/** @var NostoTaggingHelperFlashMessage $helper_flash */
		$helper_flash = Nosto::helper('nosto_tagging/flash_message');
		/** @var NostoTaggingHelperAccount $account_helper */
		$account_helper = Nosto::helper('nosto_tagging/account');

		$account_email = (string)Tools::getValue('nostotagging_account_email');
		if (empty($account_email))
			$helper_flash->add('error', $this->module->l('Email cannot be empty.'));
		elseif (!Validate::isEmail($account_email))
			$helper_flash->add('error', $this->module->l('Email is not a valid email address.'));

		try
		{
			$meta = new NostoTaggingMetaAccount();
			$meta->loadData($this->context, $this->language['id_lang']);
			$meta->getOwner()->setEmail($account_email);
			$service = new NostoServiceAccount();
			$account = $service->create($meta);

			if ($account_helper->save($account, $this->language['id_lang']))
			{
				$account_helper->updateCurrencyExchangeRates($account, $this->context);
				$helper_flash->add('success', $this->module->l('Account created. Please check your email and follow the instructions to set a password for your new account within three days.'));
			}
		}
		catch (NostoException $e)
		{
			/** @var NostoTaggingHelperLogger $logger */
			$logger = Nosto::helper('nosto_tagging/logger');
			$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode());
			$helper_flash->add('error', $this->module->l('Account could not be automatically created. Please visit nosto.com to create a new account.'));
		}
	}

	/**
	 * POST action for connecting an existing Nosto account.
	 */
	protected function actionConnectAccount()
	{
		/** @var NostoTaggingHelperAccount $helper_account */
		$helper_account = Nosto::helper('nosto_tagging/account');
		$account = $helper_account->find($this->language['id_lang']);

		$meta = new NostoTaggingMetaOauth();
		$meta->setModuleName($this->module->name);
		$meta->setModulePath($this->module->getPath());
		$meta->loadData($this->context, $this->language['id_lang'], $account);

		$client = new NostoOAuthClient($meta);
		$this->redirect_url = $client->getAuthorizationUrl();
	}

	/**
	 * POST action for deleting a Nosto account.
	 */
	protected function actionDeleteAccount()
	{
		/** @var NostoTaggingHelperAccount $helper_account */
		$helper_account = Nosto::helper('nosto_tagging/account');

		$account = $helper_account->find($this->language['id_lang']);
		$helper_account->delete($account, $this->language['id_lang']);
	}

	/**
	 * POST action for updating a Nosto account.
	 */
	protected function actionUpdateAccount()
	{
		/** @var NostoTaggingHelperAccount $helper_account */
		$helper_account = Nosto::helper('nosto_tagging/account');
		/** @var NostoTaggingHelperFlashMessage $helper_flash */
		$helper_flash = Nosto::helper('nosto_tagging/flash_message');

		$account = $helper_account->find($this->language['id_lang']);
		if (!is_null($account) && $helper_account->updateAccount($account, $this->context, $this->language['id_lang']))
			$helper_flash->add('success', $this->module->l('The account has been updated.'));
		else
			$helper_flash->add('error', $this->module->l('There was an error updating the account. See logs for more information.'));
	}

	/**
	 * POST action for updating a Nosto accounts currency exchange rates.
	 */
	protected function actionUpdateExchangeRates()
	{
		/** @var NostoTaggingHelperAccount $helper_account */
		$helper_account = Nosto::helper('nosto_tagging/account');
		/** @var NostoTaggingHelperFlashMessage $helper_flash */
		$helper_flash = Nosto::helper('nosto_tagging/flash_message');

		$account = $helper_account->find($this->language['id_lang']);
		if (!is_null($account) && $helper_account->updateCurrencyExchangeRates($account, $this->context))
			$helper_flash->add('success', $this->module->l('The exchange rates have been updated.'));
		else
			$helper_flash->add('error', $this->module->l('There was an error updating the exchange rates. See logs for more information.'));
	}

	/**
	 * POST action for updating the advanced account settings.
	 */
	protected function actionUpdateAdvancedSettings()
	{
		/** @var NostoTaggingHelperConfig $helper_config */
		$helper_config = Nosto::helper('nosto_tagging/config');
		/** @var NostoTaggingHelperFlashMessage $helper_flash */
		$helper_flash = Nosto::helper('nosto_tagging/flash_message');

		$advanced_settings = array(
			NostoTaggingHelperConfig::MULTI_CURRENCY_METHOD => Tools::getValue('nostotagging_multi_currency_method', ''),
			NostoTaggingHelperConfig::USE_DIRECT_INCLUDE => (int)Tools::getValue('nostotagging_use_direct_include', 0)
		);
		if ($helper_config->saveSettings($advanced_settings, $this->language['id_lang']))
			$helper_flash->add('success', $this->module->l('The settings have been saved.'));
		else
			$helper_flash->add('error', $this->module->l('There was an error saving the settings.'));
	}

	/**
	 * Callback ran before every action.
	 */
	protected function beforeAction()
	{
		if ($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			/** @var NostoTaggingHelperFlashMessage $helper_flash */
			$helper_flash = Nosto::helper('nosto_tagging/flash_message');
			$id_lang = (int)Tools::getValue('nostotagging_current_language');
			$language = $this->requireLanguage($id_lang);

			// The POST must be done under a shop context.
			if (_PS_VERSION_ >= '1.5' && Shop::getContext() !== Shop::CONTEXT_SHOP)
				$this->redirect();
			elseif ($language['id_lang'] != $id_lang)
			{
				$helper_flash->add('error', $this->module->l('Language cannot be empty.'));
				$this->redirect();
			}
		}
	}

	/**
	 * Callback ran after every action.
	 */
	protected function afterAction()
	{
		if (empty($this->language['id_lang']))
		{
			$id_lang = (int)Tools::getValue('language_id', 0);
			$this->requireLanguage($id_lang);
		}
	}

	/**
	 * Redirects to configured URL.
	 */
	protected function redirect()
	{
		Tools::redirect(NostoHttpRequest::replaceQueryParamInUrl('language_id', $this->language['id_lang'],
				$this->redirect_url), '');
		die;
	}

	/**
	 * Requires that a language is defined for the controller.
	 *
	 * @param int $id_lang the language ID.
	 * @return array the language.
	 */
	protected function requireLanguage($id_lang)
	{
		foreach ($this->languages as $language)
			if ($language['id_lang'] == $id_lang)
			{
				$this->language = $language;
				break;
			}

		if (empty($this->language['id_lang']) && isset($this->languages[0]))
			$this->language = $this->languages[0];

		return $this->language;
	}
}
