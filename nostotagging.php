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

/*
 * Only try to load class files if we can resolve the __FILE__ global to the current file.
 * We need to do this as this module file is parsed with eval() on the modules page, and eval() messes up the __FILE__.
 */
if ((basename(__FILE__) === 'nostotagging.php'))
{
	$module_dir = dirname(__FILE__);
	require_once($module_dir.'/libs/nosto/php-sdk/autoload.php');
	require_once($module_dir.'/classes/helpers/account.php');
	require_once($module_dir.'/classes/helpers/admin-tab.php');
	require_once($module_dir.'/classes/helpers/config.php');
	require_once($module_dir.'/classes/helpers/context-factory.php');
	require_once($module_dir.'/classes/helpers/currency.php');
	require_once($module_dir.'/classes/helpers/customer.php');
	require_once($module_dir.'/classes/helpers/flash-message.php');
	require_once($module_dir.'/classes/helpers/logger.php');
	require_once($module_dir.'/classes/helpers/price.php');
	require_once($module_dir.'/classes/helpers/product-service.php');
	require_once($module_dir.'/classes/helpers/updater.php');
	require_once($module_dir.'/classes/helpers/url.php');
	require_once($module_dir.'/classes/meta/account.php');
	require_once($module_dir.'/classes/meta/account-billing.php');
	require_once($module_dir.'/classes/meta/account-iframe.php');
	require_once($module_dir.'/classes/meta/account-owner.php');
	require_once($module_dir.'/classes/meta/account-sso.php');
	require_once($module_dir.'/classes/meta/oauth.php');
	require_once($module_dir.'/classes/models/base.php');
	require_once($module_dir.'/classes/models/line-item.php');
	require_once($module_dir.'/classes/models/cart.php');
	require_once($module_dir.'/classes/models/cart-item.php');
	require_once($module_dir.'/classes/models/category.php');
	require_once($module_dir.'/classes/models/context-snapshot.php');
	require_once($module_dir.'/classes/models/customer.php');
	require_once($module_dir.'/classes/models/order.php');
	require_once($module_dir.'/classes/models/order-buyer.php');
	require_once($module_dir.'/classes/models/order-item.php');
	require_once($module_dir.'/classes/models/order-status.php');
	require_once($module_dir.'/classes/models/product.php');
	require_once($module_dir.'/classes/models/product-variation.php');
	require_once($module_dir.'/classes/models/brand.php');
	require_once($module_dir.'/classes/models/search.php');
	require_once($module_dir.'/controllers/admin/AdminNostoConfigController.php');
}

/**
 * NostoTagging module that integrates Nosto marketing automation service.
 *
 * @property Context $context
 * @property Smarty $smarty
 */
class NostoTagging extends Module
{
	/**
	 * Custom hooks to add for this module.
	 *
	 * @var array
	 */
	protected static $custom_hooks = array(
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
		array(
			'name' => 'actionNostoBrandLoadAfter',
			'title' => 'After load nosto brand',
			'description' => 'Action hook fired after a Nosto brand object has been loaded.',
		),
		array(
			'name' => 'actionNostoCartLoadAfter',
			'title' => 'After load nosto cart',
			'description' => 'Action hook fired after a Nosto cart object has been loaded.',
		),
		array(
			'name' => 'actionNostoCategoryLoadAfter',
			'title' => 'After load nosto category',
			'description' => 'Action hook fired after a Nosto category object has been loaded.',
		),
		array(
			'name' => 'actionNostoCustomerLoadAfter',
			'title' => 'After load nosto customer',
			'description' => 'Action hook fired after a Nosto customer object has been loaded.',
		),
		array(
			'name' => 'actionNostoOrderLoadAfter',
			'title' => 'After load nosto order',
			'description' => 'Action hook fired after a Nosto order object has been loaded.',
		),
		array(
			'name' => 'actionNostoProductLoadAfter',
			'title' => 'After load nosto product',
			'description' => 'Action hook fired after a Nosto product object has been loaded.',
		),
		array(
			'name' => 'actionNostoSearchLoadAfter',
			'title' => 'After load nosto search',
			'description' => 'Action hook fired after a Nosto search object has been loaded.',
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
		$this->version = '2.4.3';
		$this->author = 'Nosto';
		$this->need_instance = 1;
		$this->bootstrap = true;

		parent::__construct();

		$this->displayName = $this->l('Personalization for PrestaShop');
		$this->description = $this->l('Increase your conversion rate and average order value by delivering your customers personalized product recommendations throughout their shopping journey.');

		// Backward compatibility
		if (_PS_VERSION_ < '1.5')
			/** @noinspection PhpIncludeInspection */
			require(_PS_MODULE_DIR_.$this->name.'/backward_compatibility/backward.php');

		// Only try to use class files if we can resolve the __FILE__ global to the current file.
		// We need to do this as this module file is parsed with eval() on the modules page,
		// and eval() messes up the __FILE__ global, which means that class files have not been included.
		if ((basename(__FILE__) === 'nostotagging.php'))
		{
			if (!$this->checkConfigState())
				$this->warning = $this->l('A Nosto account is not set up for each shop and language.');

			// Check for module updates for PS < 1.5.4.0.
			/** @var NostoTaggingHelperUpdater $helper_updater */
			$helper_updater = Nosto::helper('nosto_tagging/updater');
			$helper_updater->checkForUpdates($this);
		}
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
		/** @var NostoTaggingHelperCustomer $helper_customer */
		$helper_customer = Nosto::helper('nosto_tagging/customer');
		/** @var NostoTaggingHelperAdminTab $helper_admin_tab */
		$helper_admin_tab = Nosto::helper('nosto_tagging/admin_tab');
		/** @var NostoTaggingHelperConfig $helper_config */
		$helper_config = Nosto::helper('nosto_tagging/config');

		if (parent::install()
			&& $helper_customer->createTable()
			&& $helper_admin_tab->install()
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
			&& $this->registerHook('postUpdateOrderStatus')
			&& $this->registerHook('paymentTop')
			&& $this->registerHook('home'))
		{
			// For versions 1.4.0.1 - 1.5.3.1 we need to keep track of the currently installed version.
			// This is to enable auto-update of the module by running its upgrade scripts.
			// This config value is updated in the NostoTaggingUpdater helper every time the module is updated.
			if (version_compare(_PS_VERSION_, '1.5.4.0', '<'))
				$helper_config->saveInstalledVersion($this->version);

			if (_PS_VERSION_ < '1.5')
			{
				// For PS 1.4 we need to register some additional hooks for the product create/update/delete.
				return $this->registerHook('updateproduct')
					&& $this->registerHook('deleteproduct')
					&& $this->registerHook('addproduct')
					&& $this->registerHook('updateQuantity');
			}
			else
			{
				// And for PS >= 1.5 we register the object specific hooks for the product create/update/delete.
				// Also register the back office header hook to add some CSS to the entire back office.
				return $this->registerHook('actionObjectUpdateAfter')
					&& $this->registerHook('actionObjectDeleteAfter')
					&& $this->registerHook('actionObjectAddAfter')
					&& $this->registerHook('displayBackOfficeHeader');
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
		/** @var NostoTaggingHelperAccount $helper_account */
		$helper_account = Nosto::helper('nosto_tagging/account');
		/** @var NostoTaggingHelperCustomer $helper_customer */
		$helper_customer = Nosto::helper('nosto_tagging/customer');
		/** @var NostoTaggingHelperAdminTab $helper_admin_tab */
		$helper_admin_tab = Nosto::helper('nosto_tagging/admin_tab');
		/** @var NostoTaggingHelperConfig $helper_config */
		$helper_config = Nosto::helper('nosto_tagging/config');

		return parent::uninstall()
			&& $helper_account->deleteAll()
			&& $helper_config->purge()
			&& $helper_customer->dropTable()
			&& $helper_admin_tab->uninstall();
	}

	/**
	 * Renders the module administration form.
	 * Also handles the form submit action.
	 *
	 * @return string The HTML to output.
	 */
	public function getContent()
	{
		/** @var NostoTaggingHelperAccount $helper_account */
		$helper_account = Nosto::helper('nosto_tagging/account');
		/** @var NostoTaggingHelperConfig $helper_config */
		$helper_config = Nosto::helper('nosto_tagging/config');
		/** @var NostoTaggingHelperFlashMessage $helper_flash */
		$helper_flash = Nosto::helper('nosto_tagging/flash_message');
		/** @var NostoTaggingHelperUrl $helper_url */
		$helper_url = Nosto::helper('nosto_tagging/url');

		// Always update the url to the module admin page when we access it.
		// This can then later be used by the oauth2 controller to redirect the user back.
		$admin_url = $this->getAdminUrl();
		$helper_config->saveAdminUrl($admin_url);

		$ctrl = new AdminNostoConfigController($this, $admin_url);
		$ctrl->runAction();

		$id_shop = $this->context->shop->id;
		$languages = Language::getLanguages(true, $id_shop);
		$language = $ctrl->getLanguage();

		/** @var NostoAccount|null $account */
		$account = $helper_account->find($language['id_lang']);

		$this->context->smarty->assign(array(
			'nostotagging_form_action' => $this->getAdminUrl(),
			'nostotagging_has_account' => (!is_null($account)),
			'nostotagging_account_name' => (!is_null($account)) ? $account->getName() : null,
			'nostotagging_account_email' => $this->context->employee->email,
			'nostotagging_account_authorized' => (!is_null($account)),
			'nostotagging_languages' => $languages,
			'nostotagging_current_language' => $language,
			// Hack a few translations for the view as PS 1.4 does not support sprintf syntax in smarty "l" function.
			'nostotagging_translations' => array(
				'installed_heading' =>
					sprintf($this->l('You have installed Nosto to your %s shop'), $language['name']),
				'installed_subheading' =>
					sprintf($this->l('Your account ID is %s'), (!is_null($account)) ? $account->getName() : ''),
				'not_installed_subheading' =>
					sprintf($this->l('Install Nosto to your %s shop'), $language['name']),
				'exchange_rate_crontab_example' =>
					sprintf($this->l('0 0 * * * curl --silent %s /dev/null 2>&1'),
						$helper_url->getModuleUrl($this->name, $this->_path, 'cronRates', $language['id_lang'],
							$id_shop, array('token' => $this->getCronAccessToken()))),
			),
			'nostotagging_ps_version_class' => 'ps-'.str_replace('.', '', Tools::substr(_PS_VERSION_, 0, 3)),
			'nostotagging_multi_currency_method' => $helper_config->getMultiCurrencyMethod($language['id_lang']),
			'nostotagging_use_direct_include' => (int)$helper_config->getUseDirectInclude($language['id_lang']),
			'nostotagging_currency_formats' => $this->getCurrencyFormatPreviews(),
		));

		// Try to login employee to Nosto in order to get a url to the internal setting pages,
		// which are then shown in an iframe on the module config page.
		$iframe_url = $this->getAuthenticatedIframeUrl($account, $language['id_lang']);
		if (!empty($iframe_url))
			$this->context->smarty->assign(array('iframe_url' => $iframe_url));

		$output = '';
		if (($error_message = Tools::getValue('oauth_error')) !== false)
			$output .= $this->displayError($this->l($error_message));
		if (($success_message = Tools::getValue('oauth_success')) !== false)
			$output .= $this->displayConfirmation($this->l($success_message));

		foreach ($helper_flash->getList('success') as $flash_message)
			$output .= $this->displayConfirmation($flash_message);
		foreach ($helper_flash->getList('error') as $flash_message)
			$output .= $this->displayError($flash_message);

		if (_PS_VERSION_ >= '1.5' && Shop::getContext() !== Shop::CONTEXT_SHOP)
			$output .= $this->displayError($this->l('Please choose a shop to configure Nosto for.'));

		$stylesheets = '<link rel="stylesheet" href="'.$this->_path.'views/css/tw-bs-v3.1.1.css">';
		$stylesheets .= '<link rel="stylesheet" href="'.$this->_path.'views/css/nostotagging-admin-config.css">';
		$scripts = '<script type="text/javascript" src="'.$this->_path.'views/js/iframeresizer.min.js"></script>';
		$scripts .= '<script type="text/javascript" src="'.$this->_path.'views/js/nostotagging-admin-config.js"></script>';
		$output .= $this->display(__FILE__, 'views/templates/admin/config-bootstrap.tpl');

		return $stylesheets.$scripts.$output;
	}

	/**
	 * Returns a unique ID that identifies this PS installation.
	 *
	 * @return string the unique ID.
	 */
	public function getUniqueInstallationId()
	{
		return sha1($this->name._COOKIE_KEY_);
	}

	/**
	 * Returns the access token needed to validate requests to the cron controllers.
	 * The access token is stored in the db config, and will be renewed if the module
	 * is re-installed or the db entry is removed.
	 *
	 * @return string the access token.
	 */
	public function getCronAccessToken()
	{
		/** @var NostoTaggingHelperConfig $helper_config */
		$helper_config = Nosto::helper('nosto_tagging/config');
		$token = $helper_config->getCronAccessToken();
		if (empty($token))
		{
			// Running bin2hex() will make the string length 32 characters.
			$token = bin2hex(phpseclib_Crypt_Random::string(16));
			$helper_config->saveCronAccessToken($token);
		}
		return $token;
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
		/** @var NostoTaggingHelperAccount $helper_account */
		$helper_account = Nosto::helper('nosto_tagging/account');
		/** @var NostoTaggingHelperUrl $helper_url */
		$helper_url = Nosto::helper('nosto_tagging/url');
		/** @var NostoTaggingHelperConfig $helper_config */
		$helper_config = Nosto::helper('nosto_tagging/config');
		/** @var LanguageCore $language */
		$language = $this->context->language;

		$server_address = $helper_url->getServerAddress();
		$account = $helper_account->find($language->id);
		if ($account === null)
			return '';

		/** @var LinkCore $link */
		$link = new Link();
		$this->smarty->assign(array(
			'nosto_server' => $server_address,
			'nosto_account' => $account->getName(),
			'nosto_version' => $this->version,
			'nosto_unique_id' => $this->getUniqueInstallationId(),
			'nosto_language' => Tools::strtolower($language->iso_code),
			'add_to_cart_url' => $link->getPageLink('cart.php'),
			'nosto_use_direct_include' => $helper_config->getUseDirectInclude($language->id)
		));

		$this->context->controller->addJS($this->_path.'views/js/nostotagging-auto-slots.js');

		$html = $this->display(__FILE__, 'views/templates/hook/header_meta-tags.tpl');
		$html .= $this->display(__FILE__, 'views/templates/hook/header_embed-script.tpl');
		$html .= $this->display(__FILE__, 'views/templates/hook/header_add-to-cart.tpl');

		return $html;
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
	 * Hook for adding content to the <head> section of the back office HTML pages.
	 *
	 * Note: PS 1.5+ only.
	 *
	 * Adds Nosto admin tab CSS.
	 */
	public function hookDisplayBackOfficeHeader()
	{
		// In some cases, the controller in the context is actually not an instance of `AdminController`,
		// but of `AdminTab`. This class does not have an `addCss` method.
		// In these cases, we skip adding the CSS which will only cause the logo to be missing for the
		// Nosto menu item in PS >= 1.6.
		$ctrl = $this->context->controller;
		if ($ctrl instanceof AdminController && method_exists($ctrl, 'addCss'))
			$ctrl->addCss($this->_path.'views/css/nostotagging-back-office.css');
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
		if (!$this->accountInContext())
			return '';

		$html = '';
		$html .= $this->getCustomerTagging();
		$html .= $this->getCartTagging();
		$html .= $this->getPriceVariationTagging();

		if ($this->isController('category'))
		{
			// The "getCategory" method is available from Prestashop 1.5.6.0 upwards.
			if (method_exists($this->context->controller, 'getCategory'))
				$category = $this->context->controller->getCategory();
			else
				$category = new Category((int)Tools::getValue('id_category'), $this->context->language->id);

			if (Validate::isLoadedObject($category))
				$html .= $this->getCategoryTagging($category);
		}
		elseif ($this->isController('manufacturer'))
		{
			// The "getManufacturer" method is available from Prestashop 1.5.6.0 upwards.
			if (method_exists($this->context->controller, 'getManufacturer'))
				$manufacturer = $this->context->controller->getManufacturer();
			else
				$manufacturer = new Manufacturer((int)Tools::getValue('id_manufacturer'), $this->context->language->id);

			if (Validate::isLoadedObject($manufacturer))
				$html .= $this->getBrandTagging($manufacturer);
		}
		elseif ($this->isController('search'))
		{
			$search_term = Tools::getValue('search_query', null);
			if (!is_null($search_term))
				$html .= $this->getSearchTagging($search_term);
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
		if (!$this->accountInContext())
			return '';

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
		if (!$this->accountInContext())
			return '';

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
		if (!$this->accountInContext())
			return '';

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
		if (!$this->accountInContext())
			return '';

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
		/** @var NostoTaggingHelperCustomer $helper_customer */
		$helper_customer = Nosto::helper('nosto_tagging/customer');
		$helper_customer->updateNostoId();

		if (!$this->accountInContext())
			return '';

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
		if (!$this->accountInContext())
			return '';

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
		if (!$this->accountInContext())
			return '';

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
		if (!$this->accountInContext())
			return '';

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
		if (!$this->accountInContext())
			return '';

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
		if (!$this->accountInContext())
			return '';

		return $this->display(__FILE__, 'views/templates/hook/search-footer_nosto-elements.tpl');
	}

	/**
	 * Hook for updating the customer link table with the Prestashop customer id and the Nosto customer id.
	 */
	public function hookDisplayPaymentTop()
	{
		/** @var NostoTaggingHelperCustomer $helper_customer */
		$helper_customer = Nosto::helper('nosto_tagging/customer');
		$helper_customer->updateNostoId();
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
	 * Hook for sending order confirmations to Nosto via the API.
	 *
	 * This is a fallback for the regular order tagging on the "order confirmation page", as there are cases when
	 * the customer does not get redirected back to the shop after the payment is completed.
	 *
	 * @param array $params
	 */
	public function hookActionOrderStatusPostUpdate(Array $params)
	{
		if (isset($params['id_order']))
		{
			$id_order = $params['id_order'];
			/** @var Order|OrderCore $order */
			$order = new Order($id_order);
			if (!Validate::isLoadedObject($order))
				return;

			try {
				$nosto_order = new NostoTaggingOrder();
				$nosto_order->loadData($order);
			} catch (NostoException $e) {
				/** @var NostoTaggingHelperLogger $logger */
				$logger = Nosto::helper('nosto_tagging/logger');
				$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode(), 'Order', $id_order);
				return;
			}

			// PS 1.4 does not have "id_shop_group" and "id_shop" properties in the order object.
			$id_shop_group = isset($order->id_shop_group) ? $order->id_shop_group : null;
			$id_shop = isset($order->id_shop) ? $order->id_shop : null;
			// This is done out of context, so we need to specify the exact parameters to get the correct account.
			/** @var NostoTaggingHelperAccount $helper_account */
			$helper_account = Nosto::helper('nosto_tagging/account');
			$account = $helper_account->find($order->id_lang, $id_shop_group, $id_shop);
			if ($account !== null)
			{
				try
				{
					/** @var NostoTaggingHelperCustomer $helper_customer */
					$helper_customer = Nosto::helper('nosto_tagging/customer');
					$customer_id = $helper_customer->getNostoId($order);
					$service = new NostoServiceOrder($account);
					$service->confirm($nosto_order, $customer_id);
				}
				catch (NostoException $e)
				{
					/** @var NostoTaggingHelperLogger $logger */
					$logger = Nosto::helper('nosto_tagging/logger');
					$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode(), 'Order', $id_order);
				}
			}
		}
	}

	/**
	 * Backwards compatibility hook.
	 *
	 * @see NostoTagging::hookActionOrderStatusPostUpdate()
	 * @param array $params
	 */
	public function hookPostUpdateOrderStatus(Array $params)
	{
		$this->hookActionOrderStatusPostUpdate($params);
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
		if (!$this->accountInContext())
			return '';

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
			if ($params['object'] instanceof Product)
			{
				/** @var NostoTaggingHelperProductService $product_service */
				$product_service = Nosto::helper('nosto_tagging/product_service');
				$product_service->update($params['object']);
			}
	}

	/**
	 * Hook that is fired after a object is deleted from the db.
	 *
	 * @param array $params
	 */
	public function hookActionObjectDeleteAfter(Array $params)
	{
		if (isset($params['object']))
			if ($params['object'] instanceof Product)
			{
				/** @var NostoTaggingHelperProductService $product_service */
				$product_service = Nosto::helper('nosto_tagging/product_service');
				$product_service->delete($params['object']);
			}
	}

	/**
	 * Hook that is fired after a object has been created in the db.
	 *
	 * @param array $params
	 */
	public function hookActionObjectAddAfter(Array $params)
	{
		if (isset($params['object']))
			if ($params['object'] instanceof Product)
			{
				/** @var NostoTaggingHelperProductService $product_service */
				$product_service = Nosto::helper('nosto_tagging/product_service');
				$product_service->create($params['object']);
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
	 * @see NostoTagging::hookActionObjectDeleteAfter
	 * @param array $params
	 */
	public function hookDeleteProduct(Array $params)
	{
		if (isset($params['product']))
			$this->hookActionObjectDeleteAfter(array('object' => $params['product']));
	}

	/**
	 * Hook called when a product is added, right after said addition (Prestashop 1.4).
	 *
	 * @see NostoTagging::hookActionObjectAddAfter
	 * @param array $params
	 */
	public function hookAddProduct(Array $params)
	{
		if (isset($params['product']))
			$this->hookActionObjectAddAfter(array('object' => $params['product']));
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
	 * Returns the current context.
	 *
	 * @return Context
	 */
	public function getContext()
	{
		return $this->context;
	}

	/**
	 * Returns the modules path.
	 *
	 * @return string
	 */
	public function getPath()
	{
		return $this->_path;
	}

	/**
	 * Returns a list of currency previews for the the current context.
	 *
	 * Example:
	 *
	 * array("$1,234.56", "1 234,56 €")
	 *
	 * @return array the previews.
	 */
	protected function getCurrencyFormatPreviews()
	{
		$previews = array();

		/** @var NostoTaggingHelperCurrency $helper_currency */
		$helper_currency = Nosto::helper('nosto_tagging/currency');
		$currencies = $helper_currency->getCurrencies($this->context);
		foreach ($currencies as $currency)
			$previews[] = Tools::displayPrice('1234.56', $currency);

		return $previews;
	}

	/**
	 * Try to login the employee to Nosto in order to get a url to the internal setting pages, which are then shown in
	 * an iframe on the module config page.
	 *
	 * @param NostoAccount|null $account the Nosto account, or null if none exist.
	 * @param int $id_lang the language.
	 * @return bool|string an authenticated iframe URL or false.
	 */
	protected function getAuthenticatedIframeUrl(NostoAccount $account = null, $id_lang)
	{
		if (is_null($account))
			return false;

		$iframe_url = false;
		/** @var Employee|EmployeeCore $employee */
		$employee = $this->context->employee;

		try
		{
			$meta_sso = new NostoTaggingMetaAccountSso();
			$meta_sso->loadData($employee);
			$meta_iframe = new NostoTaggingMetaAccountIframe();
			$meta_iframe->loadData($this, $id_lang);
			/** @var NostoHelperIframe $iframe_helper */
			$iframe_helper = Nosto::helper('iframe');
			$iframe_url = $iframe_helper->getUrl($meta_sso, $meta_iframe, $account);
		}
		catch (NostoException $e)
		{
			/** @var NostoTaggingHelperLogger $logger */
			$logger = Nosto::helper('nosto_tagging/logger');
			$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode(), 'Employee', $employee->id);
		}

		return $iframe_url;
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
		// The home page.
		if ($this->isController('index'))
			return $this->display(__FILE__, 'views/templates/hook/home_hidden-nosto-elements.tpl');
		// The product page.
		elseif ($this->isController('product'))
			return $this->display(__FILE__, 'views/templates/hook/product_hidden-nosto-elements.tpl');
		// The cart summary page.
		elseif ($this->isController('order') && (int)Tools::getValue('step', 0) === 0)
			return $this->display(__FILE__, 'views/templates/hook/cart_hidden-nosto-elements.tpl');
		// The category/manufacturer page.
		elseif ($this->isController('category') || $this->isController('manufacturer'))
			return $this->display(__FILE__, 'views/templates/hook/category_hidden-nosto-elements.tpl');
		// The search page.
		elseif ($this->isController('search'))
			return $this->display(__FILE__, 'views/templates/hook/search_hidden-nosto-elements.tpl');
		// If the current page is not one of the ones we want to show recommendations on, just return empty.
		else
			return '';
	}

	/**
	 * Checks if a Nosto account is set up and connected for each shop and language combo.
	 *
	 * @return bool true if all shops have an account configured for every language.
	 */
	protected function checkConfigState()
	{
		/** @var NostoTaggingHelperAccount $helper_account */
		$helper_account = Nosto::helper('nosto_tagging/account');

		foreach (Shop::getShops() as $shop)
		{
			$id_shop = isset($shop['id_shop']) ? $shop['id_shop'] : null;
			foreach (Language::getLanguages(true, $id_shop) as $language)
			{
				$id_shop_group = isset($shop['id_shop_group']) ? $shop['id_shop_group'] : null;
				if (!$helper_account->exists($language['id_lang'], $id_shop_group, $id_shop))
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
	 * Returns the admin url.
	 * Note the url is parsed from the current url, so this can only work if called when on the admin page.
	 *
	 * @return string the url.
	 */
	protected function getAdminUrl()
	{
		$current_url = Tools::getHttpHost(true).(isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
		$parsed_url = NostoHttpRequest::parseUrl($current_url);
		$parsed_query_string = NostoHttpRequest::parseQueryString($parsed_url['query']);
		$valid_params = array('controller', 'token', 'configure', 'tab_module', 'module_name', 'tab',);
		$query_params = array();
		foreach ($valid_params as $valid_param)
			if (isset($parsed_query_string[$valid_param]))
				$query_params[$valid_param] = $parsed_query_string[$valid_param];
		$parsed_url['query'] = http_build_query($query_params);
		return NostoHttpRequest::buildUrl($parsed_url);
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
		if (!empty(self::$custom_hooks))
		{
			foreach (self::$custom_hooks as $hook)
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
	 * Checks if there is a Nosto account configured for the current context.
	 *
	 * @return bool true if account exists, false otherwise.
	 */
	protected function accountInContext()
	{
		/** @var NostoTaggingHelperAccount $helper_account */
		$helper_account = Nosto::helper('nosto_tagging/account');
		/** @var LanguageCore $language */
		$language = $this->context->language;
		return $helper_account->exists($language->id);
	}

	/**
	 * Render meta-data (tagging) for the logged in customer.
	 *
	 * @return string The rendered HTML
	 */
	protected function getCustomerTagging()
	{
		$customer = $this->context->customer;
		if (!NostoTaggingCustomer::isLoggedIn($customer))
			return '';

		$nosto_customer = new NostoTaggingCustomer();
		$nosto_customer->loadData($customer);

		$this->smarty->assign(array('nosto_customer' => $nosto_customer));
		return $this->display(__FILE__, 'views/templates/hook/top_customer-tagging.tpl');
	}

	/**
	 * Render meta-data (tagging) for the shopping cart.
	 *
	 * @return string The rendered HTML
	 */
	protected function getCartTagging()
	{
		$nosto_cart = new NostoTaggingCart();

		try {
			$nosto_cart->loadData($this->context->cart);
		} catch (NostoException $e) {
			/** @var NostoTaggingHelperLogger $logger */
			$logger = Nosto::helper('nosto_tagging/logger');
			$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode());
		}

		$this->smarty->assign(array('nosto_cart' => $nosto_cart));
		return $this->display(__FILE__, 'views/templates/hook/top_cart-tagging.tpl');
	}

	/**
	 * Render meta-data (tagging) for the price variation in use.
	 *
	 * This is needed for the multi currency features.
	 *
	 * @return string The rendered HTML
	 */
	protected function getPriceVariationTagging()
	{
		try {
			/** @var Currency|CurrencyCore $currency */
			$currency = $this->context->currency;
			$nosto_price_variation = new NostoPriceVariation($currency->iso_code);
		} catch (NostoException $e) {
			/** @var NostoTaggingHelperLogger $logger */
			$logger = Nosto::helper('nosto_tagging/logger');
			$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode());
			return '';
		}

		$this->smarty->assign(array('nosto_price_variation' => $nosto_price_variation));
		return $this->display(__FILE__, 'views/templates/hook/top_price_variation-tagging.tpl');
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
		$nosto_product = new NostoTaggingProduct();

		try {
			$nosto_product->loadData($product);
		} catch (NostoException $e) {
			/** @var NostoTaggingHelperLogger $logger */
			$logger = Nosto::helper('nosto_tagging/logger');
			$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode());
		}

		$params = array('nosto_product' => $nosto_product);

		if (Validate::isLoadedObject($category))
		{
			$nosto_category = new NostoTaggingCategory();
			$nosto_category->loadData($category);
			$params['nosto_category'] = $nosto_category;
		}

		$this->smarty->assign($params);
		return $this->display(__FILE__, 'views/templates/hook/footer-product_product-tagging.tpl');
	}

	/**
	 * Render meta-data (tagging) for a completed order.
	 *
	 * @param Order $order
	 * @return string The rendered HTML
	 */
	protected function getOrderTagging(Order $order)
	{
		$nosto_order = new NostoTaggingOrder();

		try {
			$nosto_order->loadData($order);
		} catch (NostoException $e) {
			/** @var NostoTaggingHelperLogger $logger */
			$logger = Nosto::helper('nosto_tagging/logger');
			$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode());
		}

		$this->smarty->assign(array('nosto_order' => $nosto_order));
		return $this->display(__FILE__, 'views/templates/hook/order-confirmation_order-tagging.tpl');
	}

	/**
	 * Render meta-data (tagging) for a category.
	 *
	 * @param Category $category
	 * @return string The rendered HTML
	 */
	protected function getCategoryTagging(Category $category)
	{
		$nosto_category = new NostoTaggingCategory();
		$nosto_category->loadData($category);

		$this->smarty->assign(array('nosto_category' => $nosto_category));
		return $this->display(__FILE__, 'views/templates/hook/category-footer_category-tagging.tpl');
	}

	/**
	 * Render meta-data (tagging) for a manufacturer.
	 *
	 * @param Manufacturer $manufacturer
	 * @return string The rendered HTML
	 */
	protected function getBrandTagging(Manufacturer $manufacturer)
	{
		$nosto_brand = new NostoTaggingBrand();
		$nosto_brand->loadData($manufacturer);

		$this->smarty->assign(array('nosto_brand' => $nosto_brand));
		return $this->display(__FILE__, 'views/templates/hook/manufacturer-footer_brand-tagging.tpl');
	}

	/**
	 * Render meta-data (tagging) for a search term.
	 *
	 * @param string $search_term the search term to tag.
	 * @return string the rendered HTML
	 */
	protected function getSearchTagging($search_term)
	{
		$nosto_search = new NostoTaggingSearch();
		$nosto_search->loadData($search_term);

		$this->smarty->assign(array('nosto_search' => $nosto_search));
		return $this->display(__FILE__, 'views/templates/hook/top_search-tagging.tpl');
	}

	/**
	 * Method for resolving correct smarty object
	 *
	 * @return Smarty|Smarty_Data
	 * @throws \NostoException
	 */
	protected function getSmarty()
	{
		if (
			!empty($this->smarty)
			&& method_exists($this->smarty, 'assign')
		) {
			return $this->smarty;
		} elseif (
			!empty($this->context->smarty)
			&& method_exists($this->context->smarty, 'assign')
		) {
			return $this->context->smarty;
		}
		throw new \NostoException('Could not find smarty');
	}
}
