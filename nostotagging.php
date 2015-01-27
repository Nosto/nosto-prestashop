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

if (!defined('_PS_VERSION_'))
	exit;

/*
 * Only try to load class files if we can resolve the __FILE__ global to the current file.
 * We need to do this as this module file is parsed with eval() on the modules page, and eval() messes up the __FILE__.
 */
if ((basename(__FILE__) === 'nostotagging.php'))
{
	$module_dir = dirname(__FILE__);
	require_once($module_dir.'/classes/nostotagging-block.php');
	require_once($module_dir.'/classes/nostotagging-cart.php');
	require_once($module_dir.'/classes/nostotagging-category.php');
	require_once($module_dir.'/classes/nostotagging-customer.php');
	require_once($module_dir.'/classes/nostotagging-order.php');
	require_once($module_dir.'/classes/nostotagging-product.php');
	require_once($module_dir.'/classes/nostotagging-brand.php');
	require_once($module_dir.'/classes/nostotagging-account.php');
	require_once($module_dir.'/classes/nostotagging-formatter.php');
	require_once($module_dir.'/classes/nostotagging-logger.php');
	require_once($module_dir.'/classes/nostotagging-http-request.php');
	require_once($module_dir.'/classes/nostotagging-http-response.php');
	require_once($module_dir.'/classes/nostotagging-cipher.php');
	require_once($module_dir.'/classes/nostotagging-oauth2-client.php');
	require_once($module_dir.'/classes/nostotagging-oauth2-token.php');
	require_once($module_dir.'/classes/nostotagging-config.php');
	require_once($module_dir.'/classes/nostotagging-api-request.php');
	require_once($module_dir.'/classes/nostotagging-api-token.php');
	require_once($module_dir.'/classes/nostotagging-customer-link.php');
	require_once($module_dir.'/classes/nostotagging-preview-link.php');
	require_once($module_dir.'/classes/nostotagging-admin-tab.php');
}

/**
 * NostoTagging module that integrates Nosto marketing automation service.
 *
 * @property Context $context
 */
class NostoTagging extends Module
{
	const NOSTOTAGGING_SERVER_ADDRESS = 'connect.nosto.com';
	const NOSTOTAGGING_IFRAME_URI = '/hub/prestashop/{m}';

	/**
	 * Custom hooks to add for this module.
	 *
	 * @var array
	 */
	protected $custom_hooks = array(
		array(
			'name' => 'displayCategoryTop',
			'title' => 'Category top',
			'description' => 'Add new blocks above the category product list',
		),
		array(
			'name' => 'displayCategoryFooter',
			'title' => 'Category footer',
			'description' => 'Add new blocks below the category product list',
		),
		array(
			'name' => 'displaySearchTop',
			'title' => 'Search top',
			'description' => 'Add new blocks above the search result list.',
		),
		array(
			'name' => 'displaySearchFooter',
			'title' => 'Search footer',
			'description' => 'Add new blocks below the search result list.',
		),
	);

	/**
	 * Constructor.
	 *
	 * Defines module attributes.
	 */
	public function __construct()
	{
		$this->name = 'nostotagging';
		$this->tab = 'advertising_marketing';
		$this->version = '2.0.1';
		$this->author = 'Nosto';
		$this->need_instance = 1;
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Personalization for PrestaShop');
		$this->description = $this->l('Increase your conversion rate and average order value by delivering your customers personalized product recommendations throughout their shopping journey.');

		// Backward compatibility
		if (_PS_VERSION_ < '1.5')
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

		if (!$this->checkConfigState())
			$this->warning = $this->l('A Nosto account is not set up for each shop and language.');
	}

	/**
	 * Installs the module.
	 *
	 * Initializes config, adds custom hooks and registers used hooks.
	 * The hook names for PS 1.4 are used here as all superior versions have an hook alias table which they use as a
	 * lookup to check which PS 1.4 names correspond to the newer names.
	 *
	 * @return bool
	 */
	public function install()
	{
		if (parent::install()
			&& NostoTaggingCustomerLink::createTable()
			&& NostoTaggingAdminTab::install()
			&& $this->initHooks()
			&& $this->registerHook('displayCategoryTop')
			&& $this->registerHook('displayCategoryFooter')
			&& $this->registerHook('displaySearchTop')
			&& $this->registerHook('displaySearchFooter')
			&& $this->registerHook('header')
			&& $this->registerHook('top')
			&& $this->registerHook('footer')
			&& $this->registerHook('productfooter')
			&& $this->registerHook('shoppingCart')
			&& $this->registerHook('orderConfirmation')
			&& $this->registerHook('paymentConfirm')
			&& $this->registerHook('paymentTop')
			&& $this->registerHook('home'))
		{
			if (_PS_VERSION_ < '1.5')
			{
				// For PS 1.4 we need to register some additional hooks for the product re-crawl.
				return $this->registerHook('updateproduct')
					&& $this->registerHook('deleteproduct')
					&& $this->registerHook('updateQuantity');
			}
			else
			{
				// And for PS >= 1.5 we register the object update hook for the product re-crawl as we can get better
				// precision using that then the separate hooks like in PS 1.4.
				return $this->registerHook('actionObjectUpdateAfter');
			}
		}
		return false;
	}

	/**
	 * Uninstalls the module.
	 *
	 * Removes used config values. No need to un-register any hooks,
	 * as that is handled by the parent class.
	 *
	 * @return bool
	 */
	public function uninstall()
	{
		return parent::uninstall()
			&& NostoTaggingConfig::purge()
			&& NostoTaggingCustomerLink::dropTable()
			&& NostoTaggingAdminTab::uninstall();
	}

	/**
	 * Renders the module administration form.
	 * Also handles the form submit action.
	 *
	 * @return string The HTML to output.
	 */
	public function getContent()
	{
		// Always update the url to the module admin page when we access it.
		// This can then later be used by the oauth2 controller to redirect the user back.
		NostoTaggingConfig::write(NostoTaggingConfig::ADMIN_URL, $this->getAdminUrl());

		$output = '';

		$field_has_account = $this->name.'_has_account';
		$field_account_name = $this->name.'_account_name';
		$field_account_email = $this->name.'_account_email';
		$field_account_authorized = $this->name.'_account_authorized';
		$field_languages = $this->name.'_languages';
		$field_current_language = $this->name.'_current_language';

		$languages = Language::getLanguages(true, $this->context->shop->id);
		$account_email = $this->context->employee->email;

		if ($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			$language_id = (int)Tools::getValue($field_current_language);
			foreach ($languages as $language)
				if ($language['id_lang'] == $language_id)
					$current_language = $language;

			if (empty($current_language['id_lang']))
				$output .= $this->displayError($this->l('Language cannot be empty.'));
			if (_PS_VERSION_ >= '1.5' && Shop::getContext() !== Shop::CONTEXT_SHOP)
				$output .= $this->displayError($this->l('Please choose a shop to configure Nosto for.'));
			elseif (Tools::isSubmit('submit_nostotagging_new_account'))
			{
				$account_email = (string)Tools::getValue($field_account_email);
				if (empty($account_email))
					$output .= $this->displayError($this->l('Email cannot be empty.'));
				elseif (!Validate::isEmail($account_email))
					$output .= $this->displayError($this->l('Email is not a valid email address.'));
				elseif (!NostoTaggingAccount::create($this->context, $language_id, $account_email))
					$output .= $this->displayError($this->l('Account could not be automatically created. Please visit nosto.com to create a new account.'));
				else
					$output .= $this->displayConfirmation($this->l('Account created. Please check your email and follow the instructions to set a password for your new account within three days.'));
			}
			elseif (Tools::isSubmit('submit_nostotagging_authorize_account'))
			{
				$params = array('language_id' => $language_id);
				$client = new NostoTaggingOAuth2Client();
				$client->setRedirectUrl(urlencode($this->getOAuth2ControllerUrl($params)));
				$client->setScopes(NostoTaggingApiToken::$api_token_names);
				Tools::redirect($client->getAuthorizationUrl(), '');
				die();
			}
			elseif (Tools::isSubmit('submit_nostotagging_reset_account'))
				NostoTaggingAccount::delete($language_id);
		}
		else
		{
			$language_id = (int)Tools::getValue('language_id', 0);
			if (($error_message = Tools::getValue('oauth_error')) !== false)
				$output .= $this->displayError($this->l($error_message));
			if (($success_message = Tools::getValue('oauth_success')) !== false)
				$output .= $this->displayConfirmation($this->l($success_message));
			if (_PS_VERSION_ >= '1.5' && Shop::getContext() !== Shop::CONTEXT_SHOP)
				$output .= $this->displayError($this->l('Please choose a shop to configure Nosto for.'));
		}

		// Choose current language if it has not been set.
		if (!isset($current_language))
		{
			foreach ($languages as $language)
				if ($language['id_lang'] == $language_id)
					$current_language = $language;

			if (!isset($current_language))
			{
				if (isset($languages[0]))
				{
					$current_language = $languages[0];
					$language_id = (int)$current_language['id_lang'];
				}
				else
					$current_language = array('id_lang' => 0, 'name' => '', 'iso_code' => '');
			}
		}

		$this->context->smarty->assign(array(
			'nostotagging_form_action' => $this->getAdminUrl(),
			$field_has_account => NostoTaggingAccount::exists($language_id),
			$field_account_name => NostoTaggingAccount::getName($language_id),
			$field_account_email => $account_email,
			$field_account_authorized => NostoTaggingAccount::isConnectedToNosto($language_id),
			$field_languages => $languages,
			$field_current_language => $current_language,
			// Hack a few translations for the view as PS 1.4 does not support sprintf syntax in smarty "l" function.
			'translations' => array(
				'nostotagging_installed_heading' => sprintf(
					$this->l('You have added Nosto to your %s shop'),
					$current_language['name']
				),
				'nostotagging_installed_account_name' => sprintf(
					$this->l('Your account ID is %s'),
					NostoTaggingAccount::getName($language_id)
				),
				'nostotagging_not_installed_heading' => sprintf(
					$this->l('Add Nosto to your %s shop'),
					$current_language['name']
				),
			)
		));

		// Try to login employee to Nosto in order to get a url to the internal setting pages,
		// which are then shown in an iframe on the module config page.
		$url = $this->doSSOLogin($language_id);
		if (!empty($url) && NostoTaggingAccount::isConnectedToNosto($language_id))
			$this->context->smarty->assign(array(
				'iframe_url' => $url.'?r='.urlencode(NostoTaggingHttpRequest::buildUri(
						self::NOSTOTAGGING_IFRAME_URI.'?'.http_build_query(array(
							'lang' => $this->context->language->iso_code,
							'ps_version' => _PS_VERSION_,
							'nt_version' => $this->version,
							'product_pu' => NostoTaggingPreviewLink::getProductPageUrl(null, $language_id),
							'category_pu' => NostoTaggingPreviewLink::getCategoryPageUrl(null, $language_id),
							'search_pu' => NostoTaggingPreviewLink::getSearchPageUrl($language_id),
							'cart_pu' => NostoTaggingPreviewLink::getCartPageUrl($language_id),
							'front_pu' => NostoTaggingPreviewLink::getHomePageUrl($language_id),
							'shop_lang' => $current_language['iso_code'],
							'unique_id' => sha1($this->name._COOKIE_KEY_), // unique PS installation ID.
						)),
						array(
							'{m}' => NostoTaggingAccount::getName($language_id)
						)
				))
			));

		$stylesheets = '<link rel="stylesheet" href="'.$this->_path.'css/tw-bs-v3.1.1.css">';
		$stylesheets .= '<link rel="stylesheet" href="'.$this->_path.'css/nostotagging-admin-config.css">';
		$scripts = '<script type="text/javascript" src="'.$this->_path.'js/iframeresizer.min.js"></script>';
		$scripts .= '<script type="text/javascript" src="'.$this->_path.'js/nostotagging-admin-config.js"></script>';
		$output .= $this->display(__FILE__, 'views/templates/admin/config-bootstrap.tpl');

		return $stylesheets.$scripts.$output;
	}

	/**
	 * Handle data exchanged with Nosto.
	 *
	 * @param NostoTaggingOAuth2Token $token the authorization token that let's the application act on the users behalf.
	 * @param int $language_id the ID of the language object to store the exchanged data for.
	 * @return bool true on success and false on failure.
	 */
	public function exchangeDataWithNosto(NostoTaggingOAuth2Token $token, $language_id = 0)
	{
		if (empty($token->access_token))
		{
			NostoTaggingLogger::log(
				__CLASS__.'::'.__FUNCTION__.' - No access token found when trying to exchange data with Nosto.',
				NostoTaggingLogger::LOG_SEVERITY_ERROR,
				500
			);
			return false;
		}

		$request = new NostoTaggingHttpRequest();
		// The request is currently not made according the the OAuth2 spec with the access token in the
		// Authorization header. This is due to the authentication server not implementing the full OAuth2 spec yet.
		$request->setUrl(NostoTaggingOAuth2Client::$base_url.'/exchange');
		$request->setQueryParams(array('access_token' => $token->access_token));
		$response = $request->get();
		$result = $response->getJsonResult(true);

		if ($response->getCode() !== 200)
		{
			NostoTaggingLogger::log(
				__CLASS__.'::'.__FUNCTION__.' - Failed to exchange data with Nosto.',
				NostoTaggingLogger::LOG_SEVERITY_ERROR,
				$response->getCode()
			);
			return false;
		}

		if (empty($result))
		{
			NostoTaggingLogger::log(
				__CLASS__.'::'.__FUNCTION__.' - Received invalid data from Nosto.',
				NostoTaggingLogger::LOG_SEVERITY_ERROR,
				$response->getCode()
			);
			return false;
		}

		NostoTaggingAccount::setName($token->merchant_name, $language_id);
		NostoTaggingApiToken::saveTokens($result, $language_id, 'api_');

		return NostoTaggingAccount::isConnectedToNosto($language_id);
	}

	/**
	 * Hook for adding content to the <head> section of the HTML pages.
	 *
	 * Adds the Nosto embed script.
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayHeader()
	{
		$server_address = self::NOSTOTAGGING_SERVER_ADDRESS;
		$account_name = NostoTaggingAccount::getName($this->context->language->id);

		$this->smarty->assign(array(
			'server_address' => $server_address,
			'account_name' => $account_name,
		));

		$this->context->controller->addJS($this->_path.'js/nostotagging-auto-slots.js');

		return $this->display(__FILE__, 'views/templates/hook/header_embed-script.tpl');
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookDisplayHeader()
	 * @return string The HTML to output
	 */
	public function hookHeader()
	{
		return $this->hookDisplayHeader();
	}

	/**
	 * Hook for adding content to the top of every page.
	 *
	 * Adds customer and cart tagging.
	 * Adds nosto elements.
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayTop()
	{
		$html = '';
		$html .= $this->getCustomerTagging();
		$html .= $this->getCartTagging();

		if ($this->isController('category'))
		{
			// The "getCategory" method is available from Prestashop 1.5.6.0 upwards.
			if (method_exists($this->context->controller, 'getCategory'))
				$category = $this->context->controller->getCategory();
			else
				$category = new Category((int)Tools::getValue('id_category'), $this->context->language->id);
			$html .= $this->getCategoryTagging($category);
		}
		elseif ($this->isController('manufacturer'))
		{
			// The "getManufacturer" method is available from Prestashop 1.5.6.0 upwards.
			if (method_exists($this->context->controller, 'getManufacturer'))
				$manufacturer = $this->context->controller->getManufacturer();
			else
				$manufacturer = new Manufacturer((int)Tools::getValue('id_manufacturer'), $this->context->language->id);
			$html .= $this->getBrandTagging($manufacturer);
		}

		$html .= $this->display(__FILE__, 'views/templates/hook/top_nosto-elements.tpl');
		$html .= $this->getHiddenRecommendationElements();

		return $html;
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookDisplayTop()
	 * @return string The HTML to output
	 */
	public function hookTop()
	{
		return $this->hookDisplayTop();
	}

	/**
	 * Hook for adding content to the footer of every page.
	 *
	 * Adds nosto elements.
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayFooter()
	{
		return $this->display(__FILE__, 'views/templates/hook/footer_nosto-elements.tpl');
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookDisplayFooter()
	 * @return string The HTML to output
	 */
	public function hookFooter()
	{
		return $this->hookDisplayFooter();
	}

	/**
	 * Hook for adding content to the left column of every page.
	 *
	 * Adds nosto elements.
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayLeftColumn()
	{
		return $this->display(__FILE__, 'views/templates/hook/left-column_nosto-elements.tpl');
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookDisplayLeftColumn()
	 * @return string The HTML to output
	 */
	public function hookLeftColumn()
	{
		return $this->hookDisplayLeftColumn();
	}

	/**
	 * Hook for adding content to the right column of every page.
	 *
	 * Adds nosto elements.
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayRightColumn()
	{
		return $this->display(__FILE__, 'views/templates/hook/right-column_nosto-elements.tpl');
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookDisplayRightColumn()
	 * @return string The HTML to output
	 */
	public function hookRightColumn()
	{
		return $this->hookDisplayRightColumn();
	}

	/**
	 * Hook for adding content below the product description on the product page.
	 *
	 * Adds product tagging.
	 * Adds nosto elements.
	 *
	 * @param array $params
	 * @return string The HTML to output
	 */
	public function hookDisplayFooterProduct(Array $params)
	{
		$html = '';

		$product = isset($params['product']) ? $params['product'] : null;
		$category = isset($params['category']) ? $params['category'] : null;
		$html .= $this->getProductTagging($product, $category);

		$html .= $this->display(__FILE__, 'views/templates/hook/footer-product_nosto-elements.tpl');

		return $html;
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookDisplayFooterProduct()
	 * @param array $params
	 * @return string The HTML to output
	 */
	public function hookProductFooter(Array $params)
	{
		return $this->hookDisplayFooterProduct($params);
	}

	/**
	 * Hook for adding content below the product list on the shopping cart page.
	 *
	 * Adds nosto elements.
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayShoppingCartFooter()
	{
		// Update the link between nosto users and prestashop customers.
		NostoTaggingCustomerLink::updateLink($this);

		return $this->display(__FILE__, 'views/templates/hook/shopping-cart-footer_nosto-elements.tpl');
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookDisplayShoppingCartFooter()
	 * @return string The HTML to output
	 */
	public function hookShoppingCart()
	{
		return $this->hookDisplayShoppingCartFooter();
	}

	/**
	 * Hook for adding content on the order confirmation page.
	 *
	 * Adds completed order tagging.
	 * Adds nosto elements.
	 *
	 * @param array $params
	 * @return string The HTML to output
	 */
	public function hookDisplayOrderConfirmation(Array $params)
	{
		$html = '';

		$order = isset($params['objOrder']) ? $params['objOrder'] : null;
		$html .= $this->getOrderTagging($order);

		return $html;
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookDisplayOrderConfirmation()
	 * @param array $params
	 * @return string The HTML to output
	 */
	public function hookOrderConfirmation(Array $params)
	{
		return $this->hookDisplayOrderConfirmation($params);
	}

	/**
	 * Hook for adding content to category page above the product list.
	 *
	 * Adds nosto elements.
	 *
	 * Please note that in order for this hook to be executed, it will have to be added to the theme category.tpl file.
	 *
	 * - Theme category.tpl: add the below line to the top of the file
	 *   {hook h='displayCategoryTop'}
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayCategoryTop()
	{
		return $this->display(__FILE__, 'views/templates/hook/category-top_nosto-elements.tpl');
	}

	/**
	 * Hook for adding content to category page below the product list.
	 *
	 * Adds nosto elements.
	 *
	 * Please note that in order for this hook to be executed, it will have to be added to the theme category.tpl file.
	 *
	 * - Theme category.tpl: add the below line to the end of the file
	 *   {hook h='displayCategoryFooter'}
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayCategoryFooter()
	{
		return $this->display(__FILE__, 'views/templates/hook/category-footer_nosto-elements.tpl');
	}

	/**
	 * Hook for adding content to search page above the search result list.
	 *
	 * Adds nosto elements.
	 *
	 * Please note that in order for this hook to be executed, it will have to be added to the theme search.tpl file.
	 *
	 * - Theme search.tpl: add the below line to the top of the file
	 *   {hook h='displaySearchTop'}
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplaySearchTop()
	{
		return $this->display(__FILE__, 'views/templates/hook/search-top_nosto-elements.tpl');
	}

	/**
	 * Hook for adding content to search page below the search result list.
	 *
	 * Adds nosto elements.
	 *
	 * Please note that in order for this hook to be executed, it will have to be added to the theme search.tpl file.
	 *
	 * - Theme search.tpl: add the below line to the end of the file
	 *   {hook h='displaySearchFooter'}
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplaySearchFooter()
	{
		return $this->display(__FILE__, 'views/templates/hook/search-footer_nosto-elements.tpl');
	}

	/**
	 * Hook for updating the customer link table with the Prestashop customer id and the Nosto customer id.
	 */
	public function hookDisplayPaymentTop()
	{
		NostoTaggingCustomerLink::updateLink($this);
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookDisplayPaymentTop()
	 */
	public function hookPaymentTop()
	{
		$this->hookDisplayPaymentTop();
	}

	/**
	 * Hook for sending order tagging information to Nosto via their API.
	 *
	 * This is a fallback for the regular order tagging on the "order confirmation page", as there are cases when
	 * the customer does not get redirected back to the shop after the payment is completed.
	 *
	 * @param array $params
	 */
	public function hookActionPaymentConfirmation(Array $params)
	{
		if (isset($params['id_order']))
		{
			$order = new Order($params['id_order']);
			// PS 1.4 does not have "id_shop_group" and "id_shop" properties in the order object.
			$id_shop_group = isset($order->id_shop_group) ? $order->id_shop_group : null;
			$id_shop = isset($order->id_shop) ? $order->id_shop : null;
			$nosto_order = $this->getOrderData($order);
			// This is done out of context, so we need to specify the exact parameters to get the correct account.
			$account_name = NostoTaggingAccount::getName($order->id_lang, $id_shop_group, $id_shop);
			if (!empty($nosto_order) && !empty($account_name))
			{
				$id_nosto_customer = NostoTaggingCustomerLink::getNostoCustomerId($order);
				if (!empty($id_nosto_customer))
				{
					$path = NostoTaggingApiRequest::PATH_ORDER_TAGGING;
					$replace_params = array('{m}' => $account_name, '{cid}' => $id_nosto_customer);
				}
				else
				{
					$path = NostoTaggingApiRequest::PATH_UNMATCHED_ORDER_TAGGING;
					$replace_params = array('{m}' => $account_name);

					$module_name = $order->module;
					$module = Module::getInstanceByName($module_name);
					if ($module !== false && isset($module->version))
						$module_version = $module->version;
					else
						$module_version = 'unknown';

					$nosto_order->payment_provider = $module_name.' ['.$module_version.']';
				}

				$request = new NostoTaggingApiRequest();
				$request->setPath($path);
				$request->setContentType('application/json');
				$request->setReplaceParams($replace_params);
				$response = $request->post(Tools::jsonEncode($nosto_order));

				if ($response->getCode() !== 200)
					NostoTaggingLogger::log(
						__CLASS__.'::'.__FUNCTION__.' - Order was not sent to Nosto',
						NostoTaggingLogger::LOG_SEVERITY_ERROR,
						$response->getCode(),
						'Order',
						(int)$params['id_order']
					);
			}
		}
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookActionPaymentConfirmation()
	 * @param array $params
	 */
	public function hookPaymentConfirm(Array $params)
	{
		$this->hookActionPaymentConfirmation($params);
	}

	/**
	 * Hook for adding content to the home page.
	 *
	 * Adds nosto elements.
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayHome()
	{
		return $this->display(__FILE__, 'views/templates/hook/home_nosto-elements.tpl');
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookDisplayHome()
	 * @return string The HTML to output
	 */
	public function hookHome()
	{
		return $this->hookDisplayHome();
	}

	/**
	 * Hook that is fired after a object is updated in the db.
	 *
	 * @param array $params
	 */
	public function hookActionObjectUpdateAfter(Array $params)
	{
		if (isset($params['object']))
		{
			$object = $params['object'];
			if ($object instanceof Product)
			{
				// Send a request to Nosto to re-crawl this product for every language that has a token set.
				foreach (Language::getLanguages() as $language)
				{
					$token = NostoTaggingApiToken::get('products', (int)$language['id_lang']);
					if (empty($token))
						continue;

					$request = new NostoTaggingApiRequest();
					$request->setPath(NostoTaggingApiRequest::PATH_PRODUCT_RE_CRAWL);
					$request->setContentType('application/json');
					$request->setAuthBasic('', $token);
					$response = $request->post(Tools::jsonEncode(array('product_ids' => array($object->id))));

					if ($response->getCode() !== 200)
						NostoTaggingLogger::log(
							__CLASS__.'::'.__FUNCTION__.' - Failed to send re-crawl instruction to Nosto.',
							NostoTaggingLogger::LOG_SEVERITY_ERROR,
							$response->getCode(),
							get_class($object),
							(int)$object->id
						);
				}
			}
		}
	}

	/**
	 * Hook called when a product is update with a new picture, right after said update. (Prestashop 1.4).
	 *
	 * @see NostoTagging::hookActionObjectUpdateAfter
	 * @param array $params
	 */
	public function hookUpdateProduct(Array $params)
	{
		if (isset($params['product']))
			$this->hookActionObjectUpdateAfter(array('object' => $params['product']));
	}

	/**
	 * Hook called when a product is deleted, right before said deletion (Prestashop 1.4).
	 *
	 * @see NostoTagging::hookActionObjectUpdateAfter
	 * @param array $params
	 */
	public function hookDeleteProduct(Array $params)
	{
		if (isset($params['product']))
			$this->hookActionObjectUpdateAfter(array('object' => $params['product']));
	}

	/**
	 * Hook called during an the validation of an order, the status of which being something other than
	 * "canceled" or "Payment error", for each of the order's items (Prestashop 1.4).
	 *
	 * @see NostoTagging::hookActionObjectUpdateAfter
	 * @param array $params
	 */
	public function hookUpdateQuantity(Array $params)
	{
		if (isset($params['product']))
			$this->hookActionObjectUpdateAfter(array('object' => $params['product']));
	}

	/**
	 * Returns the url to the oauth2 controller.
	 *
	 * @param array $params optional GET params.
	 * @return string the url.
	 */
	public function getOAuth2ControllerUrl(Array $params = array())
	{
		// Backward compatibility
		if (_PS_VERSION_ < '1.5')
		{
			$ssl = Configuration::get('PS_SSL_ENABLED');
			$base = ($ssl ? _PS_BASE_URL_SSL_ : _PS_BASE_URL_);
			$params['id_lang'] = (int)$this->context->language->id;
			$params['module'] = $this->name;
			$params['controller'] = 'oauth2';
			return $base.$this->_path.'ctrl.php?'.http_build_query($params);
		}
		$link = new Link();
		return $link->getModuleLink($this->name, 'oauth2', $params);
	}

	/**
	 * Returns the current context.
	 * @return Context
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Returns hidden nosto recommendation elements for the current controller.
	 * These are used as a fallback for showing recommendations if the appropriate hooks are not present in the theme.
	 * The hidden elements are put into place and shown in the shop with JavaScript.
	 *
	 * @return string the html.
	 */
	protected function getHiddenRecommendationElements()
	{
		$prepend = '';
		$append = '';

		if ($this->isController('index'))
		{
			// The home page.
			$append .= $this->display(__FILE__, 'views/templates/hook/home_hidden-nosto-elements.tpl');
		}
		elseif ($this->isController('product'))
		{
			// The product page.
			$append .= $this->display(__FILE__, 'views/templates/hook/footer-product_hidden-nosto-elements.tpl');
		}
		elseif ($this->isController('order'))
		{
			// The cart page.
			$append .= $this->display(__FILE__, 'views/templates/hook/shopping-cart-footer_hidden-nosto-elements.tpl');
		}
		elseif ($this->isController('category') || $this->isController('manufacturer'))
		{
			// The category/manufacturer page.
			$append .= $this->display(__FILE__, 'views/templates/hook/category-footer_hidden-nosto-elements.tpl');
		}
		elseif ($this->isController('search'))
		{
			// The search page.
			$prepend .= $this->display(__FILE__, 'views/templates/hook/search-top_hidden-nosto-elements.tpl');
			$append .= $this->display(__FILE__, 'views/templates/hook/search-footer_hidden-nosto-elements.tpl');
		}
		else
		{
			// If the current page is not one of the ones we want to show recommendations on, just return empty.
			return '';
		}

		$this->smarty->assign(array(
			'hidden_nosto_elements_prepend' => $prepend,
			'hidden_nosto_elements_append' => $append,
		));

		return $this->display(__FILE__, 'views/templates/hook/hidden-nosto-elements.tpl');
	}

	/**
	 * Checks if a Nosto account is set up and connected for each shop and language combo.
	 *
	 * @return bool true if all shops have an account configured for every language.
	 */
	protected function checkConfigState()
	{
		foreach (Shop::getShops() as $shop)
		{
			foreach (LanguageCore::getLanguages(true, $shop['id_shop']) as $language)
			{
				if (isset($shop['id_shop_group'], $shop['id_shop']))
					if (!NostoTaggingAccount::isConnectedToNosto($language['id_lang'], $shop['id_shop_group'], $shop['id_shop'])
						|| !NostoTaggingAccount::isConnectedToNosto($language['id_lang']))
						return false;
			}
		}
		return true;
	}

	/**
	 * Checks if the given controller is the current one.
	 *
	 * @param string $name the controller name
	 * @return bool true if the given name is the same as the controllers php_self variable, false otherwise.
	 */
	protected function isController($name)
	{
		if (_PS_VERSION_ >= '1.5')
		{
			// For prestashop 1.5 and 1.6 we can in most cases access the current controllers php_self property.
			if (!empty($this->context->controller->php_self))
				return $this->context->controller->php_self === $name;

			// But some prestashop 1.5 controllers are missing the php_self property.
			if (($controller = Tools::getValue('controller')) !== false)
				return $controller === $name;
		}
		else
		{
			// For 1.4 we need to parse the current script name, as it uses different scripts per page.
			// 1.4 does have a php_self property in the running controller, but there is no way to access the
			// controller from modules.
			$script_name = isset($_SERVER['SCRIPT_NAME']) ? $_SERVER['SCRIPT_NAME'] : '';
			return basename($script_name) === ($name.'.php');
		}

		// Fallback when controller cannot be recognised.
		return false;
	}

	/**
	 * Tries to login the employee to Nosto with an SSO token.
	 *
	 * Notes that if the current employee is not the one that owns the Nosto account, then the SSO login will fail.
	 *
	 * @param int $language_id the ID of the language object for which to do the SSO login.
	 * @return string|false the login url or false on failure.
	 */
	protected function doSSOLogin($language_id = 0)
	{
		$sso_token = NostoTaggingApiToken::get('sso', $language_id);
		if (empty($sso_token))
			return false;

		$employee = $this->context->employee;

		$request = new NostoTaggingApiRequest();
		$request->setPath(NostoTaggingApiRequest::PATH_SSO_AUTH);
		$request->setReplaceParams(array('{email}' => $employee->email));
		$request->setContentType('application/json');
		$request->setAuthBasic('', $sso_token);
		$response = $request->post(Tools::jsonEncode(array(
			'first_name' => $employee->firstname,
			'last_name' => $employee->lastname
		)));

		if ($response->getCode() !== 200)
		{
			NostoTaggingLogger::log(
				__CLASS__.'::'.__FUNCTION__.' - Unable to login employee to Nosto with SSO token.',
				NostoTaggingLogger::LOG_SEVERITY_ERROR,
				$response->getCode()
			);
			return false;
		}

		$result = $response->getJsonResult();
		if (empty($result->login_url))
		{
			NostoTaggingLogger::log(
				__CLASS__.'::'.__FUNCTION__.' - No "login_url" returned when logging in employee to Nosto.',
				NostoTaggingLogger::LOG_SEVERITY_ERROR,
				$response->getCode()
			);
			return false;
		}

		return $result->login_url;
	}

	/**
	 * Returns the admin url.
	 * Note the url is parsed from the current url, so this can only work if called when on the admin page.
	 *
	 * @return string the url.
	 */
	protected function getAdminUrl()
	{
		$current_url = Tools::getHttpHost(true).(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
		$parsed_url = NostoTaggingHttpRequest::parseUrl($current_url);
		$parsed_query_string = NostoTaggingHttpRequest::parseQueryString($parsed_url['query']);
		$valid_params = array(
			'controller',
			'token',
			'configure',
			'tab_module',
			'module_name',
			'tab',
		);
		$query_params = array();
		foreach ($valid_params as $valid_param)
			if (isset($parsed_query_string[$valid_param]))
				$query_params[$valid_param] = $parsed_query_string[$valid_param];
		$parsed_url['query'] = http_build_query($query_params);
		return NostoTaggingHttpRequest::buildUrl($parsed_url);
	}

	/**
	 * Adds custom hooks used by this module.
	 *
	 * Run on module install.
	 *
	 * @return bool
	 */
	protected function initHooks()
	{
		if (!empty($this->custom_hooks))
		{
			foreach ($this->custom_hooks as $hook)
			{
				$callback = array('Hook', (method_exists('Hook', 'getIdByName')) ? 'getIdByName' : 'get');
				$id_hook = call_user_func($callback, $hook['name']);
				if (empty($id_hook))
				{
					$new_hook = new Hook();
					$new_hook->name = pSQL($hook['name']);
					$new_hook->title = pSQL($hook['title']);
					$new_hook->description = pSQL($hook['description']);
					$new_hook->add();
					$id_hook = $new_hook->id;
					if (!$id_hook)
						return false;
				}
			}
		}

		return true;
	}

	/**
	 * Render meta-data (tagging) for the logged in customer.
	 *
	 * @return string The rendered HTML
	 */
	protected function getCustomerTagging()
	{
		$nosto_customer = new NostoTaggingCustomer($this->context, $this->context->customer);
		if (!$nosto_customer->validate())
			return '';

		$this->smarty->assign(array(
			'nosto_customer' => $nosto_customer,
		));

		return $this->display(__FILE__, 'views/templates/hook/top_customer-tagging.tpl');
	}

	/**
	 * Render meta-data (tagging) for the shopping cart.
	 *
	 * @return string The rendered HTML
	 */
	protected function getCartTagging()
	{
		$nosto_cart = new NostoTaggingCart($this->context, $this->context->cart);
		if (!$nosto_cart->validate())
			return '';

		$this->smarty->assign(array(
			'nosto_cart' => $nosto_cart,
		));

		return $this->display(__FILE__, 'views/templates/hook/top_cart-tagging.tpl');
	}

	/**
	 * Render meta-data (tagging) for a product.
	 *
	 * @param Product $product
	 * @param Category $category
	 * @return string The rendered HTML
	 */
	protected function getProductTagging(Product $product, Category $category = null)
	{
		$nosto_product = $this->getProductData($product);
		if (empty($nosto_product))
			return '';

		$params = array('nosto_product' => $nosto_product);

		if (Validate::isLoadedObject($category))
		{
			$nosto_category = new NostoTaggingCategory($this->context, $category);
			if ($nosto_category->validate())
				$params['nosto_category'] = $nosto_category;
		}

		$this->smarty->assign($params);
		return $this->display(__FILE__, 'views/templates/hook/footer-product_product-tagging.tpl');
	}

	/**
	 * Returns data about the product in a format that can be sent to Nosto.
	 *
	 * @param Product $product
	 * @return false|NostoTaggingProduct the product data array or false.
	 */
	public function getProductData(Product $product)
	{
		$nosto_product = new NostoTaggingProduct($this->context, $product);
		if (!$nosto_product->validate())
			return false;

		return $nosto_product;
	}

	/**
	 * Render meta-data (tagging) for a completed order.
	 *
	 * @param Order $order
	 * @return string The rendered HTML
	 */
	protected function getOrderTagging(Order $order)
	{
		$nosto_order = $this->getOrderData($order);
		if (empty($nosto_order))
			return '';

		$this->smarty->assign(array(
			'nosto_order' => $nosto_order,
		));

		return $this->display(__FILE__, 'views/templates/hook/order-confirmation_order-tagging.tpl');
	}

	/**
	 * Returns data about the order in a format that can be sent to Nosto.
	 *
	 * @param Order $order
	 * @return false|NostoTaggingOrder the order data array or false.
	 */
	public function getOrderData(Order $order)
	{
		$nosto_order = new NostoTaggingOrder($this->context, $order);
		if (!$nosto_order->validate())
			return false;

		return $nosto_order;
	}

	/**
	 * Render meta-data (tagging) for a category.
	 *
	 * @param Category $category
	 * @return string The rendered HTML
	 */
	protected function getCategoryTagging(Category $category)
	{
		$nosto_category = new NostoTaggingCategory($this->context, $category);
		if (!$nosto_category->validate())
			return '';

		$this->smarty->assign(array(
			'nosto_category' => $nosto_category,
		));

		return $this->display(__FILE__, 'views/templates/hook/category-footer_category-tagging.tpl');
	}

	/**
	 * Render meta-data (tagging) for a manufacturer.
	 *
	 * @param Manufacturer $manufacturer
	 * @return string The rendered HTML
	 */
	protected function getBrandTagging($manufacturer)
	{
		$nosto_brand = new NostoTaggingBrand($this->context, $manufacturer);
		if (!$nosto_brand->validate())
			return '';

		$this->smarty->assign(array(
			'nosto_brand' => $nosto_brand,
		));

		return $this->display(__FILE__, 'views/templates/hook/manufacturer-footer_brand-tagging.tpl');
	}
}
