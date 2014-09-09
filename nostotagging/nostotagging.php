<?php
if (!defined('_PS_VERSION_'))
	exit;

/**
 * NostoTagging module that integrates Nosto marketing automation service.
 */
class NostoTagging extends Module
{
	const NOSTOTAGGING_CONFIG_KEY_SERVER_ADDRESS = 'NOSTOTAGGING_SERVER_ADDRESS';
	const NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME = 'NOSTOTAGGING_ACCOUNT_NAME';
	const NOSTOTAGGING_CONFIG_KEY_USE_DEFAULT_NOSTO_ELEMENTS = 'NOSTOTAGGING_DEFAULT_ELEMENTS';
	const NOSTOTAGGING_DEFAULT_SERVER_ADDRESS = 'connect.nosto.com';
	const NOSTOTAGGING_PRODUCT_IN_STOCK = 'InStock';
	const NOSTOTAGGING_PRODUCT_OUT_OF_STOCK = 'OutOfStock';
	const NOSTOTAGGING_CUSTOMER_ID_COOKIE = '2c.cId';
	const NOSTOTAGGING_CUSTOMER_LINK_TABLE = 'nostotagging_customer_link';
	const NOSTOTAGGING_API_ORDER_TAGGING_URL = ''; // todo: add url
	const NOSTOTAGGING_API_ORDER_TAGGING_TOKEN = ''; // todo: add token

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
		$this->version = '1.0.0';
		$this->author = 'Nosto Solutions Ltd';
		$this->need_instance = 0;
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Nosto Tagging');
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
		require_once(dirname(__FILE__).'/nostotagging-top-sellers-page.php');

		return parent::install()
			&& $this->initConfig()
			&& NostoTaggingTopSellersPage::addPage()
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
			&& $this->registerHook('displayPaymentTop');
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
		require_once(dirname(__FILE__).'/nostotagging-top-sellers-page.php');

		return parent::uninstall()
			&& NostoTaggingTopSellersPage::deletePage()
			&& $this->removeCustomerLinkTable()
			&& $this->deleteConfig();
	}

	/**
	 * Enables the module.
	 *
	 * @param bool $force_all Enable module for all shops
	 * @return bool
	 */
	public function enable($force_all = false)
	{
		require_once(dirname(__FILE__).'/nostotagging-top-sellers-page.php');

		if (!parent::enable($force_all))
			return false;

		NostoTaggingTopSellersPage::enablePage();

		return true;
	}

	/**
	 * Disables the module.
	 *
	 * @param bool $force_all Disable module for all shops
	 */
	public function disable($force_all = false)
	{
		require_once(dirname(__FILE__).'/nostotagging-top-sellers-page.php');

		parent::disable($force_all);

		NostoTaggingTopSellersPage::disablePage();
	}

	/**
	 * Renders the module administration form.
	 * Also handles the form submit action.
	 *
	 * @return string The HTML to output.
	 */
	public function getContent()
	{
		$output = '';

		if (Tools::isSubmit('submit'.$this->name))
		{
			$server_address = (string)Tools::getValue($this->name.'_server_address');
			$account_name = (string)Tools::getValue($this->name.'_account_name');
			$default_nosto_elements = (int)Tools::getValue($this->name.'_use_defaults');

			if (!Validate::isUrl($server_address))
				$output .= $this->displayError($this->l('Server address is not a valid URL.'));

			if (preg_match('@^https?://@i', $server_address))
				$output .= $this->displayError($this->l('Server address cannot contain the protocol (http or https).'));

			if (empty($account_name))
				$output .= $this->displayError($this->l('Account name cannot be empty.'));

			if ($default_nosto_elements !== 0 && $default_nosto_elements !== 1)
				$output .= $this->displayError($this->l('Use default nosto elements setting is invalid.'));

			if (empty($output))
			{
				$this->setServerAddress($server_address);
				$this->setAccountName($account_name);
				$this->setUseDefaultNostoElements($default_nosto_elements);
				$output .= $this->displayConfirmation($this->l('Configuration saved'));
			}
		}

		return $output.$this->displayForm();
	}

	/**
	 * Renders the module administration form.
	 *
	 * @return string The HTML to output.
	 */
	public function displayForm()
	{
		$field_server_address = $this->name.'_server_address';
		$field_account_name = $this->name.'_account_name';
		$field_use_defaults = $this->name.'_use_defaults';

		$fields_form = array(
			array(
				'form' => array(
					'legend' => array(
						'title' => $this->l('General Settings'),
						'icon' => 'icon-cogs'
					),
					'input' => array(
						array(
							'type' => 'text',
							'label' => $this->l('Server address'),
							'name' => $field_server_address,
							'desc' => $this->l('The server address for the Nosto marketing automation service.'),
							'size' => 40,
							'required' => true,
							'class' => 'fixed-width-xxl',
						),
						array(
							'type' => 'text',
							'label' => $this->l('Account name'),
							'name' => $field_account_name,
							'desc' => $this->l('Your Nosto marketing automation service account name.'),
							'size' => 40,
							'required' => true,
							'class' => 'fixed-width-xxl',
						),
						array(
							'type' => (substr(_PS_VERSION_, 0, 3) === '1.5') ? 'radio' : 'switch',
							'label' => $this->l('Use default nosto elements'),
							'name' => $field_use_defaults,
							'desc' => $this->l('Use default nosto elements for showing product recommendations.'),
							'values' => array(
								array(
									'id' => $this->name.'_defaults_on',
									'value' => 1,
									'label' => $this->l('Enabled'),
								),
								array(
									'id' => $this->name.'_defaults_off',
									'value' => 0,
									'label' => $this->l('Disabled'),
								),
							),
							'is_bool' => true,
							'class' => 't',
							'required' => true,
						),
					),
					'submit' => array(
						'title' => $this->l('Save'),
						'name' => 'submit'.$this->name,
						'class' => 'button btn btn-default pull-right',
					),
				),
			),
		);

		$helper = new HelperForm();

		$helper->module = $this;
		$helper->name_controller = $this->name;
		$helper->token = Tools::getAdminTokenLite('AdminModules');
		$helper->currentIndex = AdminController::$currentIndex.'&configure='.$this->name;

		$helper->title = $this->displayName;
		$helper->submit_action = '';

		$helper->fields_value = array(
			$field_server_address => (string)Tools::getValue($field_server_address, $this->getServerAddress()),
			$field_account_name => (string)Tools::getValue($field_account_name, $this->getAccountName()),
			$field_use_defaults => (int)Tools::getValue($field_use_defaults, $this->getUseDefaultNostoElements()),
		);

		return $helper->generateForm($fields_form);
	}

	/**
	 * Getter for the Nosto server address.
	 *
	 * @return string
	 */
	public function getServerAddress()
	{
		return (string)Configuration::get(self::NOSTOTAGGING_CONFIG_KEY_SERVER_ADDRESS);
	}

	/**
	 * Setter for the Nosto server address.
	 *
	 * @param string $server_address
	 * @param bool $global
	 * @return bool
	 */
	public function setServerAddress($server_address, $global = false)
	{
		$callback = array(
			'Configuration',
			$global ? 'updateGlobalValue' : 'updateValue'
		);

		return call_user_func($callback, self::NOSTOTAGGING_CONFIG_KEY_SERVER_ADDRESS, (string)$server_address);
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
		$callback = array(
			'Configuration',
			$global ? 'updateGlobalValue' : 'updateValue'
		);

		return call_user_func($callback, self::NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME, (string)$account_name);
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
		$callback = array(
			'Configuration',
			$global ? 'updateGlobalValue' : 'updateValue'
		);

		return call_user_func($callback, self::NOSTOTAGGING_CONFIG_KEY_USE_DEFAULT_NOSTO_ELEMENTS, (int)$value);
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
		$server_address = $this->getServerAddress();
		$account_name = $this->getAccountName();

		if (empty($server_address) || empty($account_name))
			return '';

		$this->smarty->assign(array(
			'server_address' => $server_address,
			'account_name' => $account_name,
		));

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

		return $this->display(__FILE__, 'footer_nosto-elements.tpl');
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

		/** @var $product Product */
		$product = isset($params['product']) ? $params['product'] : null;
		$html .= $this->getProductTagging($product);

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

		/** @var $order Order */
		$order = isset($params['objOrder']) ? $params['objOrder'] : null;
		/** @var $currency Currency */
		$currency = isset($params['currencyObj']) ? $params['currencyObj'] : null;
		$html .= $this->getOrderTagging($order, $currency);

		return $html;
	}

	/**
	 * Hook for adding content to category page above the product list.
	 *
	 * Adds nosto elements.
	 *
	 * Please note that in order for this hook to be executed, it will have to be added both the category controller
	 * and the theme catalog.tpl file.
	 *
	 * - CategoryController::initContent()
	 *   $this->context->smarty->assign(array(
	 *       'HOOK_CATEGORY_TOP' => Hook::exec('displayCategoryTop', array('category' => $this->category))
	 *   ));
	 *
	 * - Theme catalog.tpl
	 *   {if isset($HOOK_CATEGORY_TOP) && $HOOK_CATEGORY_TOP}{$HOOK_CATEGORY_TOP}{/if}
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
	 * Adds category tagging.
	 * Adds nosto elements.
	 *
	 * Please note that in order for this hook to be executed, it will have to be added both the category controller
	 * and the theme catalog.tpl file.
	 *
	 * - CategoryController::initContent()
	 *   $this->context->smarty->assign(array(
	 *       'HOOK_CATEGORY_FOOTER' => Hook::exec('displayCategoryFooter', array('category' => $this->category))
	 *   ));
	 *
	 * - Theme catalog.tpl
	 *   {if isset($HOOK_CATEGORY_FOOTER) && $HOOK_CATEGORY_FOOTER}{$HOOK_CATEGORY_FOOTER}{/if}
	 *
	 * @param array $params
	 * @return string The HTML to output
	 */
	public function hookDisplayCategoryFooter(Array $params)
	{
		$html = '';

		/** @var $category Category */
		$category = isset($params['category']) ? $params['category'] : null;
		$html .= $this->getCategoryTagging($category);

		if ($this->getUseDefaultNostoElements())
			$html .= $this->display(__FILE__, 'category-footer_nosto-elements.tpl');

		return $html;
	}

	/**
	 * Hook for adding content to search page above the search result list.
	 *
	 * Adds nosto elements.
	 *
	 * Please note that in order for this hook to be executed, it will have to be added both the search controller
	 * and the theme search.tpl file.
	 *
	 * - SearchController::initContent()
	 *   $this->context->smarty->assign(array(
	 *       'HOOK_SEARCH_TOP' => Hook::exec('displaySearchTop')
	 *   ));
	 *
	 * - Theme search.tpl
	 *   {if isset($HOOK_SEARCH_TOP) && $HOOK_SEARCH_TOP}{$HOOK_SEARCH_TOP}{/if}
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
	 * Please note that in order for this hook to be executed, it will have to be added both the search controller
	 * and the theme search.tpl file.
	 *
	 * - SearchController::initContent()
	 *   $this->context->smarty->assign(array(
	 *       'HOOK_SEARCH_FOOTER' => Hook::exec('displaySearchFooter')
	 *   ));
	 *
	 * - Theme search.tpl
	 *   {if isset($HOOK_SEARCH_FOOTER) && $HOOK_SEARCH_FOOTER}{$HOOK_SEARCH_FOOTER}{/if}
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
//        var_dump($this->context->customer->id, $_COOKIE[self::NOSTOTAGGING_CUSTOMER_ID_COOKIE]);

		if (isset($this->context->customer->id, $_COOKIE[self::NOSTOTAGGING_CUSTOMER_ID_COOKIE]))
		{
			$table = _DB_PREFIX_.self::NOSTOTAGGING_CUSTOMER_LINK_TABLE;
			$data = array(
				'id_customer' => (int)$this->context->customer->id,
				'id_nosto_customer' => pSQL($_COOKIE[self::NOSTOTAGGING_CUSTOMER_ID_COOKIE]),
			);
			Db::getInstance()->insert($table, $data, false /*$null_values*/, true /*$use_cache*/, Db::REPLACE);
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
//		var_dump('actionPaymentConfirmation', $params);

		if (isset($params['order_id']))
		{
			$order = new Order($params['order_id']);
			$currency = new Currency($order->id_currency);
			$nosto_order = $this->getOrderData($order, $currency);
			$nosto_customer_id = $this->getNostoCustomerId();
			if (!empty($nosto_order) && !empty($nosto_customer_id))
			{
				$options = array(
					'http' => array(
						'method' => 'POST',
						'header' => 'Content-type: application/json',
						'content' => json_encode($nosto_order),
					)
				);
				$context = stream_context_create($options);
				$result = file_get_contents(self::NOSTOTAGGING_API_ORDER_TAGGING_URL, false, $context);

//				var_dump($result);
			}
		}
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
		$sql = 'SELECT `id_nosto_customer` FROM `'.$table.'` WHERE `id_customer` = '.(int)$this->context->customer->id;
		return Db::getInstance()->getValue($sql);
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
			$db = Db::getInstance();
			foreach ($this->custom_hooks as $hook)
			{
				$query = 'SELECT `name`
                          FROM `'._DB_PREFIX_.'hook`
                          WHERE `name` = "'.$db->escape($hook['name']).'"';

				if (!$db->getRow($query))
					if (!$db->insert('hook', $hook))
						return false;
			}
		}

		return true;
	}

	/**
	 * Adds initial config values for Nosto server address and account name.
	 *
	 * @return bool
	 */
	protected function initConfig()
	{
		return ($this->setServerAddress(self::NOSTOTAGGING_DEFAULT_SERVER_ADDRESS, true)
			&& $this->setAccountName('', true)
			&& $this->setUseDefaultNostoElements(1, true));
	}

	/**
	 * Deletes all config entries created by the module.
	 *
	 * @return bool
	 */
	protected function deleteConfig()
	{
		return (Configuration::deleteByName(self::NOSTOTAGGING_CONFIG_KEY_SERVER_ADDRESS)
			&& Configuration::deleteByName(self::NOSTOTAGGING_CONFIG_KEY_ACCOUNT_NAME)
			&& Configuration::deleteByName(self::NOSTOTAGGING_CONFIG_KEY_USE_DEFAULT_NOSTO_ELEMENTS));
	}

	/**
	 * Creates the customer link table to be able to link between the Prestashop customer and the Nosto customer.
	 *
	 * @return bool
	 */
	protected function createCustomerLinkTable()
	{
		$table = _DB_PREFIX_.self::NOSTOTAGGING_CUSTOMER_LINK_TABLE;
		$sql = 'CREATE TABLE IF NOT EXISTS `'.$table.'` (
            `id_customer` INT(10) UNSIGNED NOT NULL PRIMARY KEY,
            `id_nosto_customer` VARCHAR(255) NOT NULL
        ) ENGINE InnoDB';
		return Db::getInstance()->Execute($sql);
	}

	/**
	 * Removes the customer link table.
	 *
	 * @return bool
	 */
	protected function removeCustomerLinkTable()
	{
		$table = _DB_PREFIX_.self::NOSTOTAGGING_CUSTOMER_LINK_TABLE;
		$sql = 'DROP TABLE IF EXISTS `'.$table.'`';
		return Db::getInstance()->Execute($sql);
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
	 * @return string The rendered HTML
	 */
	protected function getProductTagging(Product $product)
	{
		if (!($product instanceof Product) || !Validate::isLoadedObject($product))
			return '';

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

		$nosto_product['categories'] = array();
		foreach ($product->getCategories() as $category_id)
		{
			$category = $this->buildCategoryString($category_id);
			if (!empty($category))
				$nosto_product['categories'][] = (string)$category;
		}

		$nosto_product['description'] = (string)$product->description_short;
		$nosto_product['list_price'] = $this->formatPrice($product->getPriceWithoutReduct(false, null));
		$nosto_product['brand'] = (string)$product->manufacturer_name;
		$nosto_product['date_published'] = $this->formatDate($product->date_add);

		$this->smarty->assign(array(
			'nosto_product' => $nosto_product,
		));

		return $this->display(__FILE__, 'footer-product_product-tagging.tpl');
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
	protected function getOrderData(Order $order, Currency $currency)
	{
		if (!($order instanceof Order) || !Validate::isLoadedObject($order)
			|| !($currency instanceof Currency) || !Validate::isLoadedObject($currency))
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
		$cart = $this->context->cart->getCartByOrderId($order->id);
		if ($cart instanceof Cart)
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
		$nosto_order['customer'] = array(
			'firstname' => $customer->firstname,
			'lastname' => $customer->lastname,
			'email' => $customer->email,
		);
		$nosto_order['purchased_items'] = array();

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
