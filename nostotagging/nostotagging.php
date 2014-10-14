<?php
if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/classes/nostotagging-logger.php');
require_once(dirname(__FILE__).'/classes/nostotagging-http-request.php');
require_once(dirname(__FILE__).'/classes/nostotagging-http-response.php');
require_once(dirname(__FILE__).'/classes/nostotagging-cipher.php');
require_once(dirname(__FILE__).'/classes/nostotagging-oauth2-client.php');
require_once(dirname(__FILE__).'/classes/nostotagging-oauth2-token.php');

/**
 * NostoTagging module that integrates Nosto marketing automation service.
 */
class NostoTagging extends Module
{
	const NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME = 'NOSTOTAGGING_ACCOUNT_NAME';
	const NOSTOTAGGING_CONFIG_KEY_USE_DEFAULT_NOSTO_ELEMENTS = 'NOSTOTAGGING_DEFAULT_ELEMENTS';
	const NOSTOTAGGING_CONFIG_KEY_SSO_TOKEN = 'NOSTOTAGGING_SSO_TOKEN';
	const NOSTOTAGGING_CONFIG_ADMIN_URL = 'NOSTOTAGGING_ADMIN_URL';
	const NOSTOTAGGING_SERVER_ADDRESS = 'connect.nosto.com';
	const NOSTOTAGGING_PRODUCT_IN_STOCK = 'InStock';
	const NOSTOTAGGING_PRODUCT_OUT_OF_STOCK = 'OutOfStock';
	const NOSTOTAGGING_CUSTOMER_ID_COOKIE = '2c_cId';
	const NOSTOTAGGING_CUSTOMER_LINK_TABLE = 'nostotagging_customer_link';
	const NOSTOTAGGING_API_BASE_URL = 'https://api.nosto.com';
	const NOSTOTAGGING_API_ORDER_TAGGING_PATH = '/visits/order/confirm/{m}/{cid}';
	const NOSTOTAGGING_API_UNMATCHED_ORDER_TAGGING_PATH = '/visits/order/unmatched/{m}';
	const NOSTOTAGGING_API_SIGNUP_PATH = '/accounts/create';
	const NOSTOTAGGING_API_SIGNUP_TOKEN = 'JRtgvoZLMl4NPqO9XWhRdvxkTMtN82ITTJij8U7necieJPCvjtZjm5C4fpNrYJ81';
	const NOSTOTAGGING_API_PLATFORM_NAME = 'prestashop';
	const NOSTOTAGGING_API_SSOAUTH_PATH = '/users/{email}';
	const NOSTOTAGGING_IFRAME_URL = '{l}?r=/hub/prestashop/{m}&language={lang}';

	/**
	 * Map of what config data we expect from the Nosto data exchange when authorizing the Nosto account.
	 *
	 * @var array
	 */
	protected static $authorized_data_exchange_config_key_map = array(
		self::NOSTOTAGGING_CONFIG_KEY_SSO_TOKEN => 'api_sso'
	);

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
		$this->version = '1.2.0';
		$this->author = 'Nosto';
		$this->need_instance = 0;
		$this->bootstrap = true;

		parent::__construct();

		if (!$this->hasAccountName())
			$this->warning = $this->l('Account details must be configured before using this module.');

		$this->displayName = $this->l('Personalized Recommendations');
		$this->description = $this->l('Integrates Nosto marketing automation service.');
	}

	/**
	 * Installs the module.
	 *
	 * Initializes config, adds custom hooks and registers used hooks.
	 *
	 * @return bool
	 */
	public function install()
	{
		return parent::install()
			&& $this->initConfig()
			&& $this->createCustomerLinkTable()
			&& $this->initHooks()
			&& $this->registerHook('displayHeader')
			&& $this->registerHook('displayTop')
			&& $this->registerHook('displayFooter')
			&& $this->registerHook('displayLeftColumn')
			&& $this->registerHook('displayRightColumn')
			&& $this->registerHook('displayFooterProduct')
			&& $this->registerHook('displayShoppingCartFooter')
			&& $this->registerHook('displayOrderConfirmation')
			&& $this->registerHook('displayCategoryTop')
			&& $this->registerHook('displayCategoryFooter')
			&& $this->registerHook('displaySearchTop')
			&& $this->registerHook('displaySearchFooter')
			&& $this->registerHook('actionPaymentConfirmation')
			&& $this->registerHook('displayPaymentTop')
			&& $this->registerHook('displayHome');
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
			&& $this->removeCustomerLinkTable()
			&& $this->deleteConfig();
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
		$this->setAdminUrl($this->getCurrentUrl());

		$output = '';

		$field_has_account = $this->name.'_has_account';
		$field_account_email = $this->name.'_account_email';
		$field_use_defaults = $this->name.'_use_defaults';

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$account_email = (string)Tools::getValue($field_account_email);
			$default_elements = (int)Tools::getValue($field_use_defaults);

			if (Tools::isSubmit('submit_nostotagging_new_account'))
			{
				if (empty($account_email))
					$output .= $this->displayError($this->l('Email cannot be empty.'));
				elseif (!Validate::isEmail($account_email))
					$output .= $this->displayError($this->l('Email is not a valid email address.'));
				elseif ($this->createAccount($account_email))
					$output .= $this->displayConfirmation($this->l('Account created.'));
				else
					$output .= $this->displayError($this->l('Account could not be automatically created. Please visit nosto.com to create a new account.'));
			}
			elseif (Tools::isSubmit('submit_nostotagging_general_settings'))
			{
				if ($default_elements !== 0 && $default_elements !== 1)
					$output .= $this->displayError($this->l('Use default nosto elements setting is invalid.'));

				if (empty($output))
				{
					$this->setUseDefaultNostoElements($default_elements);
					$output .= $this->displayConfirmation($this->l('Configuration saved.'));
				}
			}
			elseif(Tools::isSubmit('submit_nostotagging_authorize_account'))
			{
				$client = new NostoTaggingOAuth2Client();
				$client->setRedirectUrl(urlencode($this->getOAuth2ControllerUrl()));
				header('Location: '.$client->getAuthorizationUrl());
				die();
			}
		}
		else
		{
			$account_email = $this->context->employee->email;
			$default_elements = $this->getUseDefaultNostoElements();

			foreach ($this->getAdminFlashMessages('error') as $error_message)
				$output .= $this->displayError($this->l($error_message));
			foreach ($this->getAdminFlashMessages('success') as $success_message)
				$output .= $this->displayConfirmation($this->l($success_message));
		}

		$this->context->controller->addJS($this->_path.'js/nostotagging-admin-config.js');
		$this->context->smarty->assign(array(
			$field_has_account => $this->hasAccountName(),
			$field_account_email => $account_email,
			$field_use_defaults => $default_elements,
			'is_account_authorized' => $this->isAccountConnectedToNosto(),
		));

		if (version_compare(substr(_PS_VERSION_, 0, 3), '1.6', '>='))
		{
			// Try to login employee to Nosto in order to get a url to the internal setting pages,
			// which are then shown in iframes on the module config page.
			$login_url = $this->doSSOLogin();
			if (!empty($login_url) && $this->isAccountConnectedToNosto())
				$this->context->smarty->assign(array(
					'manage_slots_iframe_url' => strtr(self::NOSTOTAGGING_IFRAME_URL, array(
						'{l}' => $login_url,
						'{m}' => $this->getAccountName(),
						'{lang}' => $this->context->language->iso_code
					)),
					'look_and_feel_iframe_url' => strtr(self::NOSTOTAGGING_IFRAME_URL, array(
						'{l}' => $login_url,
						'{m}' => $this->getAccountName(),
						'{lang}' => $this->context->language->iso_code
					))
				));

			$output .= $this->display(__FILE__, 'views/templates/admin/config-bootstrap.tpl');
		}
		else
			$output .= $this->display(__FILE__, 'views/templates/admin/config.tpl');

		return $output;
	}

	/**
	 * Handle data exchanged with Nosto.
	 *
	 * @param NostoTaggingOAuth2Token $token the authorization token that let's the application act on the users behalf.
	 * @return bool true on success and false on failure.
	 */
	public function exchangeDataWithNosto(NostoTaggingOAuth2Token $token)
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
		$response = $request->get(
			NostoTaggingOAuth2Client::NOSTOTAGGING_OAUTH2_CLIENT_BASE_URL.'/exchange',
			array(), // headers
			array(
				'access_token' => $token->access_token
			)
		);
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

		$this->setAccountName($token->merchant_name);

		foreach (self::$authorized_data_exchange_config_key_map as $config_key => $data_key)
			if (isset($result[$data_key]))
				$this->setConfigValue($config_key, (string)$result[$data_key]);

		return $this->isAccountConnectedToNosto();
	}

	/**
	 * Returns the current shop's url from the context.
	 *
	 * @return string the absolute url.
	 */
	public function getContextShopUrl()
	{
		$shop = $this->context->shop;
		$uri = (!empty($shop->domain_ssl) ? $shop->domain_ssl : $shop->domain).__PS_BASE_URI__;
		return (Configuration::get('PS_SSL_ENABLED') ? 'https://' : 'http://').$uri;
	}

	/**
	 * Checks if the account name is set.
	 *
	 * @return bool
	 */
	public function hasAccountName()
	{
		$account = $this->getAccountName();
		return !empty($account);
	}

	/**
	 * Getter for the Nosto account name.
	 *
	 * @return string
	 */
	public function getAccountName()
	{
		return (string)Configuration::get(self::NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME);
	}

	/**
	 * Setter for the Nosto account name.
	 *
	 * @param string $account_name
	 * @param bool $global
	 * @return bool
	 */
	public function setAccountName($account_name, $global = false)
	{
		// The API tokens are tied to the account, so they need to be removed if the account is updated.
		if ($account_name !== $this->getAccountName())
			$this->removeApiTokens($global);

		return $this->setConfigValue(self::NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME, (string)$account_name, $global);
	}

	/**
	 * Getter for the "use default nosto elements" settings.
	 *
	 * @return int
	 */
	public function getUseDefaultNostoElements()
	{
		return (int)Configuration::get(self::NOSTOTAGGING_CONFIG_KEY_USE_DEFAULT_NOSTO_ELEMENTS);
	}

	/**
	 * Setter for the "use default nosto elements" settings.
	 *
	 * @param int $value Either 1 or 0.
	 * @param bool $global
	 * @return bool
	 */
	public function setUseDefaultNostoElements($value, $global = false)
	{
		return $this->setConfigValue(self::NOSTOTAGGING_CONFIG_KEY_USE_DEFAULT_NOSTO_ELEMENTS, (int)$value, $global);
	}

	/**
	 * Checks if there is an SSO token set.
	 */
	public function hasSSOToken()
	{
		$token = $this->getSSOToken();
		return !empty($token);
	}

	/**
	 * Getter for the SSO token.
	 *
	 * @return string the token.
	 */
	public function getSSOToken()
	{
		return (string)Configuration::get(self::NOSTOTAGGING_CONFIG_KEY_SSO_TOKEN);
	}

	/**
	 * Setter for the Nosto SSO token.
	 *
	 * @param string $sso_token
	 * @param bool $global
	 * @return bool
	 */
	public function setSSOToken($sso_token, $global = false)
	{
		return $this->setConfigValue(self::NOSTOTAGGING_CONFIG_KEY_SSO_TOKEN, (string)$sso_token, $global);
	}

	/**
	 * Getter for the admin url.
	 * The url is only returned if the current user is an admin logged in to the back office.
	 *
	 * @return string the url.
	 */
	public function getAdminUrl()
	{
		if ($this->isUserAdmin())
			return (string)Configuration::get(self::NOSTOTAGGING_CONFIG_ADMIN_URL);
		return '';
	}

	/**
	 * Setter for the admin url.
	 *
	 * @param string $admin_url
	 * @param bool $global
	 * @return bool
	 */
	public function setAdminUrl($admin_url, $global = false)
	{
		return $this->setConfigValue(self::NOSTOTAGGING_CONFIG_ADMIN_URL, (string)$admin_url, $global);
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
		$account_name = $this->getAccountName();

		$this->smarty->assign(array(
			'server_address' => $server_address,
			'account_name' => $account_name,
		));

		if ($this->getUseDefaultNostoElements())
			$this->context->controller->addJS($this->_path.'js/nostotagging-auto-slots.js');

		return $this->display(__FILE__, 'header_embed-script.tpl');
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

		if ($this->getUseDefaultNostoElements())
			$html .= $this->display(__FILE__, 'top_nosto-elements.tpl');

		return $html;
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
		if (!$this->getUseDefaultNostoElements())
			return '';

		$html = '';
		$html .= $this->display(__FILE__, 'footer_nosto-elements.tpl');

		if ($this->isController('category'))
		{
			$html .= '<div id="hidden_nosto_elements" style="display: none;">';
			$html .= '<div class="append">';
			$html .= $this->display(__FILE__, 'category-top_nosto-elements.tpl');
			$html .= $this->display(__FILE__, 'category-footer_nosto-elements.tpl');
			$html .= '</div>';
			$html .= '</div>';
		}
		elseif ($this->isController('search'))
		{
			$html .= '<div id="hidden_nosto_elements" style="display: none;">';
			$html .= '<div class="prepend">'.$this->display(__FILE__, 'search-top_nosto-elements.tpl').'</div>';
			$html .= '<div class="append">'.$this->display(__FILE__, 'search-footer_nosto-elements.tpl').'</div>';
			$html .= '</div>';
		}

		return $html;
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
		if (!$this->getUseDefaultNostoElements())
			return '';

		return $this->display(__FILE__, 'left-column_nosto-elements.tpl');
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
		if (!$this->getUseDefaultNostoElements())
			return '';

		return $this->display(__FILE__, 'right-column_nosto-elements.tpl');
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

		if ($this->getUseDefaultNostoElements())
			$html .= $this->display(__FILE__, 'footer-product_nosto-elements.tpl');

		return $html;
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
		if (!$this->getUseDefaultNostoElements())
			return '';

		return $this->display(__FILE__, 'shopping-cart-footer_nosto-elements.tpl');
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
		$currency = isset($params['currencyObj']) ? $params['currencyObj'] : null;
		$html .= $this->getOrderTagging($order, $currency);

		return $html;
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
		if (!$this->getUseDefaultNostoElements())
			return '';

		return $this->display(__FILE__, 'category-top_nosto-elements.tpl');
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
		if (!$this->getUseDefaultNostoElements())
			return '';

		return $this->display(__FILE__, 'category-footer_nosto-elements.tpl');
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
		if (!$this->getUseDefaultNostoElements())
			return '';

		return $this->display(__FILE__, 'search-top_nosto-elements.tpl');
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
		if (!$this->getUseDefaultNostoElements())
			return '';

		return $this->display(__FILE__, 'search-footer_nosto-elements.tpl');
	}

	/**
	 * Hook for updating the customer link table with the Prestashop customer id and the Nosto customer id.
	 */
	public function hookDisplayPaymentTop()
	{
		if (isset($this->context->customer->id, $_COOKIE[self::NOSTOTAGGING_CUSTOMER_ID_COOKIE]))
		{
			$table = _DB_PREFIX_.self::NOSTOTAGGING_CUSTOMER_LINK_TABLE;
			$id_customer = (int)$this->context->customer->id;
			$id_nosto_customer = pSQL($_COOKIE[self::NOSTOTAGGING_CUSTOMER_ID_COOKIE]);
			$where = '`id_customer` = '.$id_customer.' AND `id_nosto_customer` = "'.$id_nosto_customer.'"';
			$existing_link = Db::getInstance()->getRow('SELECT * FROM `'.$table.'` WHERE '.$where);
			if (empty($existing_link))
			{
				$data = array(
					'id_customer' => $id_customer,
					'id_nosto_customer' => $id_nosto_customer,
					'date_add' => date('Y-m-d H:i:s')
				);
				Db::getInstance()->insert($table, $data, false, true, Db::INSERT, false);
			}
			else
			{
				$data = array(
					'date_upd' => date('Y-m-d H:i:s')
				);
				Db::getInstance()->update($table, $data, $where, 0, false, true, false);
			}
		}
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
			$currency = new Currency($order->id_currency);
			$nosto_order = $this->getOrderData($order, $currency);
			$account_name = $this->getAccountName();
			if (!empty($nosto_order) && !empty($account_name))
			{
				$id_nosto_customer = $this->getNostoCustomerId();
				if (!empty($id_nosto_customer))
				{
					$url = self::NOSTOTAGGING_API_BASE_URL.self::NOSTOTAGGING_API_ORDER_TAGGING_PATH;
					$url = strtr($url, array('{m}' => $account_name, '{cid}' => $id_nosto_customer));
				}
				else
				{
					$url = self::NOSTOTAGGING_API_BASE_URL.self::NOSTOTAGGING_API_UNMATCHED_ORDER_TAGGING_PATH;
					$url = strtr($url, array('{m}' => $account_name));
					$module_name = $order->module;
					$module = Module::getInstanceByName($module_name);
					if ($module !== false && isset($module->version))
						$module_version = $module->version;
					else
						$module_version = 'unknown';

					$nosto_order['payment_provider'] = $module_name.' ['.$module_version.']';
				}

				$request = new NostoTaggingHttpRequest();
				$response = $request->post($url, array('Content-type: application/json'), json_encode($nosto_order));
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
	 * Hook for adding content to the home page.
	 *
	 * Adds nosto elements.
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayHome()
	{
		if (!$this->getUseDefaultNostoElements())
			return '';

		return $this->display(__FILE__, 'home_nosto-elements.tpl');
	}

	/**
	 * Returns the url to the oauth2 controller.
	 *
	 * @return string the url.
	 */
	public function getOAuth2ControllerUrl()
	{
		$link = new Link();
		return $link->getModuleLink($this->name, 'oauth2');
	}

	/**
	 * Checks if the current user is logged in the back office.
	 *
	 * @return bool true if user is admin, false otherwise.
	 */
	public function isUserAdmin()
	{
		$cookie = new Cookie('psAdmin');
		return (bool)$cookie->id_employee;
	}

	/**
	 * Puts a "flash" message to the admin cookie that can be shown during the next request.
	 *
	 * @param string $category the message category, e.g. 'error', 'success'.
	 * @param string $message the message to show.
	 */
	public function setAdminFlashMessage($category, $message)
	{
		if ($this->isUserAdmin())
		{
			$cookie = new Cookie('psAdmin');
			if (!empty($cookie->nostotagging))
				$data = json_decode($cookie->nostotagging, true);
			else
				$data = array();
			$data['messages'][$category][] = $message;
			$cookie->nostotagging = json_encode($data);
		}
	}

	/**
	 * Gets flash messages for the admin user with the given category.
	 * The messages are removed from the cookie after they are extracted.
	 *
	 * @param string $category the message category, e.g. 'error', 'success'.
	 * @return array the list of messages.
	 */
	public function getAdminFlashMessages($category)
	{
		$messages = array();
		if ($this->isUserAdmin())
		{
			$cookie = new Cookie('psAdmin');
			if (!empty($cookie->nostotagging))
			{
				$data = json_decode($cookie->nostotagging, true);
				if (!empty($data['messages'][$category]))
				{
					$messages = $data['messages'][$category];
					unset($data['messages'][$category]);
				}
				$cookie->nostotagging = json_encode($data);
			}
		}
		return $messages;
	}

	/**
	 * Removes all API tokens that have been transferred from Nosto.
	 *
	 * @param bool $global if the api keys are to be removed for all shops, or just for the current one.
	 */
	protected function removeApiTokens($global = false)
	{
		foreach (self::$authorized_data_exchange_config_key_map as $config_key => $value)
			$this->setConfigValue($config_key, '', $global);
	}

	/**
	 * Checks if the account has been authorized with Nosto.
	 * This is determined by checking if we have all the data needed for make authorized requests to the Nosto API.
	 *
	 * @return bool true if the account has been authorized, false otherwise.
	 */
	protected function isAccountConnectedToNosto()
	{
		if (!$this->hasAccountName())
			return false;
		foreach (self::$authorized_data_exchange_config_key_map as $config_key => $data_key)
			if (Configuration::get($config_key) === false)
				return false;
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
		return (!empty($this->context->controller->php_self) && $this->context->controller->php_self === $name);
	}

	/**
	 * Tries to login the employee to Nosto with an SSO token.
	 *
	 * Notes that if the current employee is not the one that owns the Nosto account, then the SSO login will fail.
	 *
	 * @return string|false the login url or false on failure.
	 */
	protected function doSSOLogin()
	{
		if (!$this->hasSSOToken())
			return false;

		$employee = $this->context->employee;
		$request = new NostoTaggingHttpRequest();
		$url = self::NOSTOTAGGING_API_BASE_URL.self::NOSTOTAGGING_API_SSOAUTH_PATH;
		$response = $request->post(
			strtr($url, array('{email}' => $employee->email)),
			array(
				'Content-type: application/json',
				'Authorization: Basic '.base64_encode(':'.$this->getSSOToken())
			),
			json_encode(array(
				'first_name' => $employee->firstname,
				'last_name' => $employee->lastname
			))
		);

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
	 * Returns the current requested url.
	 *
	 * @return string the url.
	 */
	protected function getCurrentUrl()
	{
		$host = Tools::getHttpHost(true);
		$request_uri = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
		return $host.$request_uri;
	}

	/**
	 * Registers a new config entry for key => value pair.
	 *
	 * @param string $key the key to store the value by in config.
	 * @param mixed $value the value of the config entry.
	 * @param bool $global
	 * @return bool
	 */
	protected function setConfigValue($key, $value, $global = false)
	{
		$callback = array(
			'Configuration',
			$global ? 'updateGlobalValue' : 'updateValue'
		);
		return call_user_func($callback, (string)$key, $value);
	}

	/**
	 * Returns the Nosto customer id for the current Prestashop customer.
	 *
	 * @return string
	 */
	protected function getNostoCustomerId()
	{
		if (!isset($this->context->customer->id))
			return false;

		$table = _DB_PREFIX_.self::NOSTOTAGGING_CUSTOMER_LINK_TABLE;
		$id_customer = (int)$this->context->customer->id;
		return Db::getInstance()->getValue('SELECT `id_nosto_customer` FROM `'.$table.'` WHERE `id_customer` = '.$id_customer.' ORDER BY `date_add` ASC');
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
				$id_hook = Hook::getIdByName($hook['name']);
				if (!$id_hook)
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
	 * Calls the Nosto account creation API endpoint to create a new account.
	 * It stores the account name and the SSO token to the global configuration.
	 * If a account is already configured, it will be overwritten.
	 *
	 * @param string|null $email address to use when signing up (default is current employee's email).
	 * @return bool
	 */
	protected function createAccount($email = null)
	{
		$params = array(
			'title' => Configuration::get('PS_SHOP_NAME'),
			'name' => substr(sha1(rand()), 0, 8),
			'platform' => self::NOSTOTAGGING_API_PLATFORM_NAME,
			'front_page_url' => $this->getContextShopUrl(),
			'currency_code' => $this->context->currency->iso_code,
			'language_code' => $this->context->language->iso_code,
			'owner' => array(
				'first_name' => $this->context->employee->firstname,
				'last_name' => $this->context->employee->lastname,
				'email' => (!empty($email) ? $email : $this->context->employee->email),
			),
			'billing_details' => array(
				'country' => $this->context->country->iso_code
			)
		);
		$request = new NostoTaggingHttpRequest();
		$url = self::NOSTOTAGGING_API_BASE_URL.self::NOSTOTAGGING_API_SIGNUP_PATH;
		$response = $request->post(
			$url,
			array(
				'Content-type: application/json',
				'Authorization: Basic '.base64_encode(':'.self::NOSTOTAGGING_API_SIGNUP_TOKEN)
			),
			json_encode($params)
		);

		if ($response->getCode() !== 200)
		{
			NostoTaggingLogger::log(
				__CLASS__.'::'.__FUNCTION__.' - Nosto account could not be created',
				NostoTaggingLogger::LOG_SEVERITY_ERROR,
				$response->getCode()

			);
			return false;
		}

		$result = $response->getJsonResult();
		$this->setAccountName(self::NOSTOTAGGING_API_PLATFORM_NAME.'-'.$params['name']);
		if (!empty($result->sso_token))
			$this->setSSOToken($result->sso_token);

		return true;
	}

	/**
	 * Adds initial config values for the module.
	 *
	 * @return bool
	 */
	protected function initConfig()
	{
		$this->setAccountName('', true/* $global */);
		$this->setSSOToken('', true/* $global */);
		$this->setUseDefaultNostoElements(1, true/* $global */);
		return true;
	}

	/**
	 * Deletes config entries created by the module.
	 *
	 * @return bool
	 */
	protected function deleteConfig()
	{
		$config_keys = array(
			self::NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME,
			self::NOSTOTAGGING_CONFIG_KEY_SSO_TOKEN,
			self::NOSTOTAGGING_CONFIG_KEY_USE_DEFAULT_NOSTO_ELEMENTS,
			self::NOSTOTAGGING_CONFIG_ADMIN_URL,
		);
		foreach ($config_keys as $config_key)
			Configuration::deleteByName($config_key);
		return true;
	}

	/**
	 * Creates the customer link table to be able to link between the Prestashop customer and the Nosto customer.
	 * Note: this method cannot be changed or removed as it is explicitly used in upgrade script for 1.1.0.
	 *
	 * @return bool
	 */
	public function createCustomerLinkTable()
	{
		$table = _DB_PREFIX_.self::NOSTOTAGGING_CUSTOMER_LINK_TABLE;
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (
			`id_customer` INT(10) UNSIGNED NOT NULL,
			`id_nosto_customer` VARCHAR(255) NOT NULL,
			`date_add` DATETIME NOT NULL,
			`date_upd` DATETIME NULL,
			PRIMARY KEY (`id_customer`, `id_nosto_customer`)
		) ENGINE '._MYSQL_ENGINE_;
		return Db::getInstance()->execute($sql);
	}

	/**
	 * Removes the customer link table.
	 *
	 * @return bool
	 */
	protected function removeCustomerLinkTable()
	{
		$table = _DB_PREFIX_.self::NOSTOTAGGING_CUSTOMER_LINK_TABLE;
		return Db::getInstance()->execute('DROP TABLE IF EXISTS `'.$table.'`');
	}

	/**
	 * Formats price into Nosto format (e.g. 1000.99).
	 *
	 * @param string|int|float $price
	 * @return string
	 */
	protected function formatPrice($price)
	{
		return number_format((float)$price, 2, '.', '');
	}

	/**
	 * Formats date into Nosto format, i.e. Y-m-d.
	 *
	 * @param string $date
	 * @return string
	 */
	protected function formatDate($date)
	{
		return date('Y-m-d', strtotime((string)$date));
	}

	/**
	 * Builds a tagging string of the given category including all its parent categories.
	 *
	 * @param string|int $category_id
	 * @return string
	 */
	protected function buildCategoryString($category_id)
	{
		$category_list = array();

		$lang_id = (int)$this->context->language->id;
		$category = new Category((int)$category_id, $lang_id);

		if (Validate::isLoadedObject($category) && (int)$category->active === 1)
			foreach ($category->getParentsCategories($lang_id) as $parent_category)
				if (isset($parent_category['name'], $parent_category['active']) && (int)$parent_category['active'] === 1)
					$category_list[] = (string)$parent_category['name'];

		if (empty($category_list))
			return '';

		return DS.implode(DS, array_reverse($category_list));
	}

	/**
	 * Render meta-data (tagging) for the logged in customer.
	 *
	 * @return string The rendered HTML
	 */
	protected function getCustomerTagging()
	{
		if (!isset($this->context->customer) || !$this->context->customer->isLogged())
			return '';

		$this->smarty->assign(array(
			'customer' => $this->context->customer,
		));

		return $this->display(__FILE__, 'top_customer-tagging.tpl');
	}

	/**
	 * Render meta-data (tagging) for the shopping cart.
	 *
	 * @return string The rendered HTML
	 */
	protected function getCartTagging()
	{
		if (!isset($this->context->cart))
			return '';

		$products = (array)$this->context->cart->getProducts();
		if (empty($products))
			return '';

		$currency = $this->context->currency;
		$cart_rules = (array)$this->context->cart->getCartRules(CartRule::FILTER_ACTION_GIFT);

		$gift_products = array();
		foreach ($cart_rules as $cart_rule)
			if ((int)$cart_rule['gift_product'])
			{
				foreach ($products as $key => &$product)
					if (empty($product['gift'])
						&& (int)$product['id_product'] === (int)$cart_rule['gift_product']
						&& (int)$product['id_product_attribute'] === (int)$cart_rule['gift_product_attribute'])
					{
						$product['cart_quantity'] = (int)$product['cart_quantity'];
						$product['cart_quantity']--;

						if (!($product['cart_quantity'] > 0))
							unset($products[$key]);

						$gift_product = $product;
						$gift_product['cart_quantity'] = 1;
						$gift_product['price_wt'] = 0;
						$gift_product['gift'] = true;

						$gift_products[] = $gift_product;

						break; // One gift product per cart rule
					}
				unset($product);
			}

		$items = array_merge($products, $gift_products);

		$nosto_line_items = array();
		foreach ($items as $item)
			$nosto_line_items[] = array(
				'product_id' => (int)$item['id_product'],
				'quantity' => (int)$item['cart_quantity'],
				'name' => (string)$item['name'],
				'unit_price' => $this->formatPrice($item['price_wt']),
				'price_currency_code' => (string)$currency->iso_code,
			);

		$this->smarty->assign(array(
			'nosto_line_items' => $nosto_line_items,
		));

		return $this->display(__FILE__, 'top_cart-tagging.tpl');
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

		if (Validate::isLoadedObject($category))
			$nosto_product['current_category'] = $this->buildCategoryString($category->id);

		$this->smarty->assign(array(
			'nosto_product' => $nosto_product,
		));

		return $this->display(__FILE__, 'footer-product_product-tagging.tpl');
	}

	/**
	 * Returns data about the product in a format that can be sent to Nosto.
	 *
	 * @param Product $product
	 * @return false|array the product data array or false.
	 */
	public function getProductData(Product $product)
	{
		if (!Validate::isLoadedObject($product))
			return false;

		$nosto_product = array();
		$nosto_product['url'] = (string)$product->getLink();
		$nosto_product['product_id'] = (int)$product->id;
		$nosto_product['name'] = (string)$product->name;

		$image_id = $product->getCoverWs();
		if (ctype_digit((string)$image_id))
			$image_url = $this->context->link->getImageLink($product->link_rewrite, $product->id.'-'.$image_id, 'large_default');
		else
			$image_url = '';
		$nosto_product['image_url'] = (string)$image_url;

		$nosto_product['price'] = $this->formatPrice($product->getPrice(true, null));
		$nosto_product['price_currency_code'] = (string)$this->context->currency->iso_code;

		if ($product->checkQty(1))
			$nosto_product['availability'] = self::NOSTOTAGGING_PRODUCT_IN_STOCK;
		else
			$nosto_product['availability'] = self::NOSTOTAGGING_PRODUCT_OUT_OF_STOCK;

		if (($tags = $product->getTags($this->context->language->id)) !== '')
			$nosto_product['tags'] = explode(', ', $tags);

		$nosto_product['categories'] = array();
		foreach ($product->getCategories() as $category_id)
		{
			$category = $this->buildCategoryString($category_id);
			if (!empty($category))
				$nosto_product['categories'][] = (string)$category;
		}

		$nosto_product['description'] = (string)$product->description_short;
		$nosto_product['list_price'] = $this->formatPrice($product->getPriceWithoutReduct(false, null));

		if (!empty($product->manufacturer_name))
			$nosto_product['brand'] = (string)$product->manufacturer_name;

		$nosto_product['date_published'] = $this->formatDate($product->date_add);

		return $nosto_product;
	}

	/**
	 * Render meta-data (tagging) for a completed order.
	 *
	 * @param Order $order
	 * @param Currency $currency
	 * @return string The rendered HTML
	 */
	protected function getOrderTagging(Order $order, Currency $currency)
	{
		$nosto_order = $this->getOrderData($order, $currency);
		if (empty($nosto_order))
			return '';

		$this->smarty->assign(array(
			'nosto_order' => $nosto_order,
		));

		return $this->display(__FILE__, 'order-confirmation_order-tagging.tpl');
	}

	/**
	 * Returns data about the order in a format that can be sent to Nosto.
	 *
	 * @param Order $order
	 * @param Currency $currency
	 * @return false|array the order data array or false.
	 */
	public function getOrderData(Order $order, Currency $currency)
	{
		if (!Validate::isLoadedObject($order) || !Validate::isLoadedObject($currency))
			return false;

		$products = array();
		$total_discounts_tax_incl = 0;
		$total_shipping_tax_incl = 0;
		$total_wrapping_tax_incl = 0;
		$total_gift_tax_incl = 0;

		// One order can be split into multiple orders, so we need to combine their data.
		$order_collection = Order::getByReference($order->reference);
		foreach ($order_collection as $item)
		{
			/** @var $item Order */
			$products = array_merge($products, $item->getProducts());
			$total_discounts_tax_incl = Tools::ps_round($total_discounts_tax_incl + $item->total_discounts_tax_incl, 2);
			$total_shipping_tax_incl = Tools::ps_round($total_shipping_tax_incl + $item->total_shipping_tax_incl, 2);
			$total_wrapping_tax_incl = Tools::ps_round($total_wrapping_tax_incl + $item->total_wrapping_tax_incl, 2);
		}

		// We need the cart rules used for the order to check for gift products and free shipping.
		// The cart is the same even if the order is split into many objects.
		$cart = new Cart($order->id_cart);
		if (Validate::isLoadedObject($cart))
			$cart_rules = (array)$cart->getCartRules();
		else
			$cart_rules = array();

		$gift_products = array();
		foreach ($cart_rules as $cart_rule)
			if ((int)$cart_rule['gift_product'])
			{
				foreach ($products as $key => &$product)
					if (empty($product['gift'])
						&& (int)$product['product_id'] === (int)$cart_rule['gift_product']
						&& (int)$product['product_attribute_id'] === (int)$cart_rule['gift_product_attribute'])
					{
						$product['product_quantity'] = (int)$product['product_quantity'];
						$product['product_quantity']--;

						if (!($product['product_quantity'] > 0))
							unset($products[$key]);

						$total_gift_tax_incl = Tools::ps_round($total_gift_tax_incl + $product['product_price_wt'], 2);

						$gift_product = $product;
						$gift_product['product_quantity'] = 1;
						$gift_product['product_price_wt'] = 0;
						$gift_product['gift'] = true;

						$gift_products[] = $gift_product;

						break; // One gift product per cart rule
					}
				unset($product);
			}

		$items = array_merge($products, $gift_products);

		$customer = $order->getCustomer();
		$nosto_order = array();
		$nosto_order['order_number'] = (string)$order->reference;
		$nosto_order['buyer'] = array(
			'first_name' => $customer->firstname,
			'last_name' => $customer->lastname,
			'email' => $customer->email,
		);
		$nosto_order['purchased_items'] = array();
		$nosto_order['created_at'] = $this->formatDate($order->date_add);

		foreach ($items as $item)
		{
			$p = new Product($item['product_id'], false, $this->context->language->id);
			if (Validate::isLoadedObject($p))
				$nosto_order['purchased_items'][] = array(
					'product_id' => (int)$p->id,
					'quantity' => (int)$item['product_quantity'],
					'name' => (string)$p->name,
					'unit_price' => $this->formatPrice($item['product_price_wt']),
					'price_currency_code' => (string)$currency->iso_code,
				);
		}

		if (empty($nosto_order['purchased_items']))
			return false;

		// Add special items for discounts, shipping and gift wrapping.

		if ($total_discounts_tax_incl > 0)
		{
			// Subtract possible gift product price from total as gifts are tagged with price zero (0).
			$total_discounts_tax_incl = Tools::ps_round($total_discounts_tax_incl - $total_gift_tax_incl, 2);
			if ($total_discounts_tax_incl > 0)
				$nosto_order['purchased_items'][] = array(
					'product_id' => -1,
					'quantity' => 1,
					'name' => 'Discount',
					'unit_price' => $this->formatPrice(-$total_discounts_tax_incl), // Note the negative value.
					'price_currency_code' => (string)$currency->iso_code,
				);
		}

		// Check is free shipping applies to the cart.
		$free_shipping = false;
		foreach ($cart_rules as $cart_rule)
			if ((int)$cart_rule['free_shipping'])
			{
				$free_shipping = true;
				break;
			}

		if (!$free_shipping && $total_shipping_tax_incl > 0)
			$nosto_order['purchased_items'][] = array(
				'product_id' => -1,
				'quantity' => 1,
				'name' => 'Shipping',
				'unit_price' => $this->formatPrice($total_shipping_tax_incl),
				'price_currency_code' => (string)$currency->iso_code,
			);

		if ($total_wrapping_tax_incl > 0)
			$nosto_order['purchased_items'][] = array(
				'product_id' => -1,
				'quantity' => 1,
				'name' => 'Gift Wrapping',
				'unit_price' => $this->formatPrice($total_wrapping_tax_incl),
				'price_currency_code' => (string)$currency->iso_code,
			);

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
		if (!($category instanceof Category) || !Validate::isLoadedObject($category))
			return '';

		$category_string = $this->buildCategoryString($category->id);
		if (empty($category_string))
			return '';

		$this->smarty->assign(array(
			'category' => (string)$category_string,
		));

		return $this->display(__FILE__, 'category-footer_category-tagging.tpl');
	}
}
