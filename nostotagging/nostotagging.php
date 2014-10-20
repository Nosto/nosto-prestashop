<?php
if (!defined('_PS_VERSION_'))
	exit;

require_once(dirname(__FILE__).'/classes/nostotagging-block.php');
require_once(dirname(__FILE__).'/classes/nostotagging-cart.php');
require_once(dirname(__FILE__).'/classes/nostotagging-category.php');
require_once(dirname(__FILE__).'/classes/nostotagging-customer.php');
require_once(dirname(__FILE__).'/classes/nostotagging-order.php');
require_once(dirname(__FILE__).'/classes/nostotagging-product.php');

require_once(dirname(__FILE__).'/classes/nostotagging-account.php');
require_once(dirname(__FILE__).'/classes/nostotagging-formatter.php');
require_once(dirname(__FILE__).'/classes/nostotagging-logger.php');
require_once(dirname(__FILE__).'/classes/nostotagging-http-request.php');
require_once(dirname(__FILE__).'/classes/nostotagging-http-response.php');
require_once(dirname(__FILE__).'/classes/nostotagging-cipher.php');
require_once(dirname(__FILE__).'/classes/nostotagging-oauth2-client.php');
require_once(dirname(__FILE__).'/classes/nostotagging-oauth2-token.php');
require_once(dirname(__FILE__).'/classes/nostotagging-config.php');
require_once(dirname(__FILE__).'/classes/nostotagging-api-request.php');
require_once(dirname(__FILE__).'/classes/nostotagging-api-token.php');
require_once(dirname(__FILE__).'/classes/nostotagging-customer-link.php');

/**
 * NostoTagging module that integrates Nosto marketing automation service.
 *
 * @property Context $context
 */
class NostoTagging extends Module
{
	const NOSTOTAGGING_SERVER_ADDRESS = 'connect.nosto.com';
	const NOSTOTAGGING_IFRAME_URL = '{l}?r=/hub/prestashop/{m}&language={lang}';

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
			&& NostoTaggingCustomerLink::createTable()
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
			&& NostoTaggingConfig::purge()
			&& NostoTaggingCustomerLink::dropTable();
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
		NostoTaggingConfig::write(NostoTaggingConfig::ADMIN_URL, $this->getCurrentUrl());

		$output = '';

		$field_has_account = $this->name.'_has_account';
		$field_account_name = $this->name.'_account_name';
		$field_account_email = $this->name.'_account_email';
		$field_account_authorized = $this->name.'_account_authorized';
		$field_languages = $this->name.'_languages';
		$field_current_language = $this->name.'_current_language';

		$languages = Language::getLanguages();
		$current_language = array('id_lang' => 0, 'name' => '');
		$language_id = 0;
		$account_email = $this->context->employee->email;

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$language_id = (int)Tools::getValue($field_current_language);
			foreach ($languages as $language)
				if ($language['id_lang'] == $language_id)
					$current_language = $language;

			if (empty($current_language['id_lang']))
				$output .= $this->displayError($this->l('Language cannot be empty.'));
			elseif (Tools::isSubmit('submit_nostotagging_new_account'))
			{
				$account_email = (string)Tools::getValue($field_account_email);
				if (empty($account_email))
					$output .= $this->displayError($this->l('Email cannot be empty.'));
				elseif (!Validate::isEmail($account_email))
					$output .= $this->displayError($this->l('Email is not a valid email address.'));
				elseif (!NostoTaggingAccount::create($this->context, $account_email, $language_id))
					$output .= $this->displayError($this->l('Account could not be automatically created. Please visit nosto.com to create a new account.'));
				else
					$output .= $this->displayConfirmation($this->l('Account created.'));
			}
			elseif(Tools::isSubmit('submit_nostotagging_authorize_account'))
			{
				$params = array('language_id' => $language_id);
				$client = new NostoTaggingOAuth2Client();
				$client->setRedirectUrl(urlencode($this->getOAuth2ControllerUrl($params)));
				$client->setScopes(NostoTaggingApiToken::$api_token_names);
				header('Location: '.$client->getAuthorizationUrl());
				die();
			}
			elseif (Tools::isSubmit('submit_nostotagging_reset_account'))
				NostoTaggingAccount::delete($language_id);
		}
		else
		{
			foreach ($this->getAdminFlashMessages('error') as $error_message)
				$output .= $this->displayError($this->l($error_message));
			foreach ($this->getAdminFlashMessages('success') as $success_message)
				$output .= $this->displayConfirmation($this->l($success_message));
		}

		if (empty($language_id) && isset($languages[0]))
		{
			$current_language = $languages[0];
			$language_id = (int)$current_language['id_lang'];
		}

		$this->context->controller->addJS($this->_path.'js/nostotagging-admin-config.js');

		$this->context->smarty->assign(array(
			$field_has_account => NostoTaggingAccount::exists($language_id),
			$field_account_name => NostoTaggingAccount::getName($language_id),
			$field_account_email => $account_email,
			$field_account_authorized => NostoTaggingAccount::isConnectedToNosto($language_id),
			$field_languages => $languages,
			$field_current_language => $current_language,
		));

		if (version_compare(substr(_PS_VERSION_, 0, 3), '1.6', '>='))
		{
			// Try to login employee to Nosto in order to get a url to the internal setting pages,
			// which are then shown in an iframe on the module config page.
			$iframe_url = $this->doSSOLogin($language_id);
			if (!empty($iframe_url) && NostoTaggingAccount::isConnectedToNosto($language_id))
				$this->context->smarty->assign(array(
					'iframe_url' => NostoTaggingHttpRequest::build_uri(self::NOSTOTAGGING_IFRAME_URL, array(
						'{l}' => $iframe_url,
						'{m}' => NostoTaggingAccount::getName($language_id),
						'{lang}' => $this->context->language->iso_code
					)),
				));
		}

		$stylesheets = '<link rel="stylesheet" href="'.$this->_path.'css/tw-bs-v3.1.1.css">';
		$stylesheets .= '<link rel="stylesheet" href="'.$this->_path.'css/nostotagging-admin-config.css">';
		$output .= $this->display(__FILE__, 'views/templates/admin/config-bootstrap.tpl');

		return $stylesheets.$output;
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
		$request->setUrl(NostoTaggingOAuth2Client::BASE_URL.'/exchange');
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

		NostoTaggingAccount::setName($token->merchant_name, false, $language_id);
		NostoTaggingApiToken::saveTokens($result, $language_id);

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
		// Update the link between nosto users and prestashop customers.
		NostoTaggingCustomerLink::updateLink($this);

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
		$html .= $this->getOrderTagging($order);

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
		return $this->display(__FILE__, 'search-footer_nosto-elements.tpl');
	}

	/**
	 * Hook for updating the customer link table with the Prestashop customer id and the Nosto customer id.
	 */
	public function hookDisplayPaymentTop()
	{
		NostoTaggingCustomerLink::updateLink($this);
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
			$nosto_order = $this->getOrderData($order);
			// This is done out of context, so we need to specify the exact parameters to get the correct account.
			$account_name = NostoTaggingAccount::getName($order->id_lang, $order->id_shop_group, $order->id_shop);
			if (!empty($nosto_order) && !empty($account_name))
			{
				$id_nosto_customer = NostoTaggingCustomerLink::getNostoCustomerId($this);
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

					$nosto_order['payment_provider'] = $module_name.' ['.$module_version.']';
				}

				$request = new NostoTaggingApiRequest();
				$request->setPath($path);
				$request->setContentType('application/json');
				$request->setReplaceParams($replace_params);
				$response = $request->post(json_encode($nosto_order));

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
		return $this->display(__FILE__, 'home_nosto-elements.tpl');
	}

	/**
	 * Returns the url to the oauth2 controller.
	 *
	 * @param array $params optional GET params.
	 * @return string the url.
	 */
	public function getOAuth2ControllerUrl(Array $params = array())
	{
		$link = new LinkCore();
		return $link->getModuleLink($this->name, 'oauth2', $params);
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
	 * @param int $language_id the ID of the language object for which to do the SSO login.
	 * @return string|false the login url or false on failure.
	 */
	protected function doSSOLogin($language_id = 0)
	{
		if (($sso_token = NostoTaggingApiToken::get('sso', $language_id)) === false)
			return false;

		$employee = $this->context->employee;

		$request = new NostoTaggingApiRequest();
		$request->setPath(NostoTaggingApiRequest::PATH_SSO_AUTH);
		$request->setReplaceParams(array('{email}' => $employee->email));
		$request->setContentType('application/json');
		$request->setAuthBasic('', $sso_token);
		$response = $request->post(json_encode(array(
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
	 * Adds initial config values for the module.
	 *
	 * @return bool
	 */
	protected function initConfig()
	{
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
			'customer' => $nosto_customer,
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
		$nosto_cart = new NostoTaggingCart($this->context, $this->context->cart);
		if (!$nosto_cart->validate())
			return '';

		$this->smarty->assign(array(
			'nosto_cart' => $nosto_cart,
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

		$params = array('nosto_product' => $nosto_product);

		if (Validate::isLoadedObject($category))
		{
			$nosto_category = new NostoTaggingCategory($this->context, $category);
			if ($nosto_category->validate())
				$params['nosto_category'] = $nosto_category;
		}

		$this->smarty->assign($params);
		return $this->display(__FILE__, 'footer-product_product-tagging.tpl');
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

		return $this->display(__FILE__, 'order-confirmation_order-tagging.tpl');
	}

	/**
	 * Returns data about the order in a format that can be sent to Nosto.
	 *
	 * @param Order $order
	 * @return false|array the order data array or false.
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

		return $this->display(__FILE__, 'category-footer_category-tagging.tpl');
	}
}
