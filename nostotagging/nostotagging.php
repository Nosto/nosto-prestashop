<?php
if (!defined('_PS_VERSION_'))
	exit;

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
	const NOSTOTAGGING_SERVER_ADDRESS = 'staging.nosto.com';
	const NOSTOTAGGING_PRODUCT_IN_STOCK = 'InStock';
	const NOSTOTAGGING_PRODUCT_OUT_OF_STOCK = 'OutOfStock';
	const NOSTOTAGGING_PLATFORM_NAME = 'prestashop';
	const NOSTOTAGGING_IFRAME_URL = '{l}?r=/hub/prestashop/{m}&language={lang}';

    /**
     * @var array list of api tokens to request from Nosto, prefixed with "api_" when returned by Nosto.
     */
    public static $api_tokens = array(
        'sso',
        'products'
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
		$language_id = 0;
		$account_email = $this->context->employee->email;

		if ($_SERVER['REQUEST_METHOD'] === 'POST') {
			$language_id = (int)Tools::getValue($field_current_language);
			foreach ($languages as $language)
				if ($language['id_lang'] == $language_id)
					$current_language = $language;

			if (Tools::isSubmit('submit_nostotagging_new_account'))
			{
				$account_email = (string)Tools::getValue($field_account_email);
				if (empty($account_email))
					$output .= $this->displayError($this->l('Email cannot be empty.'));
				elseif (!Validate::isEmail($account_email))
					$output .= $this->displayError($this->l('Email is not a valid email address.'));
				elseif (!$this->createAccount($account_email, $language_id))
					$output .= $this->displayError($this->l('Account could not be automatically created. Please visit nosto.com to create a new account.'));
				else
					$output .= $this->displayConfirmation($this->l('Account created.'));
			}
			elseif(Tools::isSubmit('submit_nostotagging_authorize_account'))
			{
				$params = array();
				if (!empty($language_id))
					$params['language_id'] = $language_id;

				$client = new NostoTaggingOAuth2Client();
				$client->setRedirectUrl(urlencode($this->getOAuth2ControllerUrl($params)));
				$client->setScopes(self::$api_tokens);
				header('Location: '.$client->getAuthorizationUrl());
				die();
			}
			elseif (Tools::isSubmit('submit_nostotagging_reset_account'))
				NostoTaggingConfig::deleteAllFromContext($language_id);
		}
		else
		{
			foreach ($this->getAdminFlashMessages('error') as $error_message)
				$output .= $this->displayError($this->l($error_message));
			foreach ($this->getAdminFlashMessages('success') as $success_message)
				$output .= $this->displayConfirmation($this->l($success_message));
		}

		if (empty($language_id) && isset($languages[0]))
			$current_language = $languages[0];
		if (!isset($current_language))
			$current_language = array('id_lang' => 0, 'name' => '');

		$this->context->controller->addCSS($this->_path.'css/nostotagging-admin-config.css');
		$this->context->controller->addJS($this->_path.'js/nostotagging-admin-config.js');

		$this->context->smarty->assign(array(
			$field_has_account => NostoTaggingConfig::exists(NostoTaggingConfig::ACCOUNT_NAME, $language_id),
			$field_account_name => NostoTaggingConfig::read(NostoTaggingConfig::ACCOUNT_NAME, $language_id),
			$field_account_email => $account_email,
			$field_account_authorized => $this->isAccountConnectedToNosto($language_id),
			$field_languages => $languages,
			$field_current_language => $current_language,
		));

		if (version_compare(substr(_PS_VERSION_, 0, 3), '1.6', '>='))
		{
			// Try to login employee to Nosto in order to get a url to the internal setting pages,
			// which are then shown in an iframe on the module config page.
			$iframe_url = $this->doSSOLogin($language_id);
			if (!empty($iframe_url) && $this->isAccountConnectedToNosto($language_id))
				$this->context->smarty->assign(array(
					'iframe_url' => NostoTaggingHttpRequest::build_uri(self::NOSTOTAGGING_IFRAME_URL, array(
						'{l}' => $iframe_url,
						'{m}' => NostoTaggingConfig::read(NostoTaggingConfig::ACCOUNT_NAME, $language_id),
						'{lang}' => $this->context->language->iso_code
					)),
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

		NostoTaggingConfig::write(NostoTaggingConfig::ACCOUNT_NAME, $token->merchant_name, false, $language_id);
		$this->saveApiTokens($result, $language_id);

		return $this->isAccountConnectedToNosto($language_id);
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
	 * Hook for adding content to the <head> section of the HTML pages.
	 *
	 * Adds the Nosto embed script.
	 *
	 * @return string The HTML to output
	 */
	public function hookDisplayHeader()
	{
		$server_address = self::NOSTOTAGGING_SERVER_ADDRESS;
		$account_name = NostoTaggingConfig::read(NostoTaggingConfig::ACCOUNT_NAME, $this->context->language->id);

		$this->smarty->assign(array(
			'server_address' => $server_address,
			'account_name' => $account_name,
		));

		if (NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
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

		if (NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
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
		if (!NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
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
		if (!NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
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
		if (!NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
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

		if (NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
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

		if (!NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
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
		if (!NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
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
		if (!NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
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
		if (!NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
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
		if (!NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
			return '';

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
			$currency = new Currency($order->id_currency);
			$nosto_order = $this->getOrderData($order, $currency);
			// This is done out of context, so we need to specify the exact parameters to get the correct account.
			$account_name = NostoTaggingConfig::read(NostoTaggingConfig::ACCOUNT_NAME, $order->id_lang, $order->id_shop_group, $order->id_shop);
			if (!empty($nosto_order) && !empty($account_name))
			{
				$id_nosto_customer = $this->getNostoCustomerId();
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
		if (!NostoTaggingConfig::read(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, $this->context->language->id))
			return '';

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
	 * Saves API tokens in the config by given language.
	 *
	 * @param array $tokens list of tokens to save, indexed by token name, e.g. "api_sso".
	 * @param int $language_id the ID of the language model to save the tokens for.
	 */
	protected function saveApiTokens($tokens, $language_id = 0)
	{
		foreach (self::$api_tokens as $token_name)
		{
			$key = 'api_'.$token_name;
			if (isset($tokens[$key]))
				NostoTaggingApiToken::set($token_name, $tokens[$key], false, $language_id);
		}
	}

	/**
	 * Checks if the account has been authorized with Nosto.
	 * This is determined by checking if we have all the data needed for make authorized requests to the Nosto API.
	 *
	 * @param int $language_id the ID of the language model to check if the account is connected to nosto with.
	 * @return bool true if the account has been authorized, false otherwise.
	 */
	protected function isAccountConnectedToNosto($language_id = 0)
	{
		if (!NostoTaggingConfig::exists(NostoTaggingConfig::ACCOUNT_NAME, $language_id))
			return false;
		foreach (self::$api_tokens as $token_name)
		{
			$token = NostoTaggingApiToken::get($token_name, $language_id);
			if ($token === false || $token === null)
				return false;
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
	 * @param int $language_id the ID of the language object to create the account for (defaults to context lang).
	 * @return bool
	 */
	protected function createAccount($email = null, $language_id = 0)
	{
		$language = !empty($language_id) ? new Language($language_id) : $this->context->language;
		if (!Validate::isLoadedObject($language))
			return false;

		$api_tokens = array();
		foreach (self::$api_tokens as $token_name)
			$api_tokens[] = 'api_'.$token_name;

		$params = array(
			'title' => Configuration::get('PS_SHOP_NAME'),
			'name' => substr(sha1(rand()), 0, 8),
			'platform' => self::NOSTOTAGGING_PLATFORM_NAME,
			'front_page_url' => $this->getContextShopUrl(),
			'currency_code' => $this->context->currency->iso_code,
			'language_code' => $language->iso_code,
			'owner' => array(
				'first_name' => $this->context->employee->firstname,
				'last_name' => $this->context->employee->lastname,
				'email' => (!empty($email) ? $email : $this->context->employee->email),
			),
			'billing_details' => array(
				'country' => $this->context->country->iso_code
			),
			'api_tokens' => $api_tokens
		);
		$request = new NostoTaggingApiRequest();
		$request->setPath(NostoTaggingApiRequest::PATH_SIGN_UP);
		$request->setContentType('application/json');
		$request->setAuthBasic('', NostoTaggingApiRequest::TOKEN_SIGN_UP);
		$response = $request->post(json_encode($params));

		if ($response->getCode() !== 200)
		{
			NostoTaggingLogger::log(
				__CLASS__.'::'.__FUNCTION__.' - Nosto account could not be created',
				NostoTaggingLogger::LOG_SEVERITY_ERROR,
				$response->getCode()

			);
			return false;
		}

		$result = $response->getJsonResult(true);

		$account_name = self::NOSTOTAGGING_PLATFORM_NAME.'-'.$params['name'];
		NostoTaggingConfig::write(NostoTaggingConfig::ACCOUNT_NAME, $account_name, false, $language_id);
		$this->saveApiTokens($result, $language_id);

		return true;
	}

	/**
	 * Adds initial config values for the module.
	 *
	 * @return bool
	 */
	protected function initConfig()
	{
		NostoTaggingConfig::write(NostoTaggingConfig::ACCOUNT_NAME, '', true/*global*/);
		NostoTaggingConfig::write(NostoTaggingConfig::USE_DEFAULT_NOSTO_ELEMENTS, 1, true/*global*/);
		return true;
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
