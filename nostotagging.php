<?php
/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/*
 * Only try to load class files if we can resolve the __FILE__ global to the current file.
 * We need to do this as this module file is parsed with eval() on the modules page, and eval()
 * messes up the __FILE__.
 */
if ((basename(__FILE__) === 'nostotagging.php')) {
    define('NOSTO_DIR', dirname(__FILE__));
    define('NOSTO_VERSION', NostoTagging::PLUGIN_VERSION);
    /** @noinspection PhpIncludeInspection */
    require_once("bootstrap.php");
}

use Nosto\NostoException as NostoSDKException;
use Nosto\Object\Notification as NostoSDKNotification;
use Nosto\Request\Http\HttpRequest as NostoSDKHttpRequest;

/**
 * NostoTagging module that integrates Nosto marketing automation service.
 *
 * @property Context $context
 */
class NostoTagging extends Module
{
    /**
     * The version of the Nosto plug-in
     *
     * @var string
     */
    const PLUGIN_VERSION = '2.8.6';

    /**
     * Internal name of the Nosto plug-in
     *
     * @var string
     */
    const MODULE_NAME = 'nostotagging';

    /**
     * @var string the algorithm to use for hashing visitor id.
     */
    const VISITOR_HASH_ALGO = 'sha256';

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
            'name' => 'actionNostoCartLoadAfter',
            'title' => 'After load nosto cart',
            'description' => 'Action hook fired after a Nosto cart object has been loaded.',
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
            'name' => 'actionNostoPriceVariantLoadAfter',
            'title' => 'After load nosto price variation',
            'description' => 'Action hook fired after a Nosto price variation object has been initialized.',
        ),
        array(
            'name' => 'actionNostoRatesLoadAfter',
            'title' => 'After load nosto exchange rates',
            'description' => 'Action hook fired after a Nosto exchange rate collection has been initialized.',
        ),
    );

    /**
     * Constructor.
     *
     * Defines module attributes.
     *
     * @suppress PhanTypeMismatchProperty
     */
    public function __construct()
    {
        $this->name = self::MODULE_NAME;
        $this->tab = 'advertising_marketing';
        $this->version = self::PLUGIN_VERSION;
        $this->author = 'Nosto';
        $this->need_instance = 1;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->module_key = '8d80397cab6ca02dfe8ef681b48c37a3';

        parent::__construct();
        $this->displayName = $this->l('Nosto Personalization for PrestaShop');
        $this->description = $this->l(
            'Increase your conversion rate and average order value by delivering your customers personalized product
            recommendations throughout their shopping journey.'
        );
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
        $success = false;
        if (parent::install()) {
            $success = true;
            if (
                !$this->registerHook('displayCategoryTop')
                || !$this->registerHook('displayCategoryFooter')
                || !$this->registerHook('displaySearchTop')
                || !$this->registerHook('displaySearchFooter')
                || !$this->registerHook('header')
                || !$this->registerHook('top')
                || !$this->registerHook('footer')
                || !$this->registerHook('productfooter')
                || !$this->registerHook('shoppingCart')
                || !$this->registerHook('orderConfirmation')
                || !$this->registerHook('postUpdateOrderStatus')
                || !$this->registerHook('paymentTop')
                || !$this->registerHook('home')
            ) {
                $success = false;
                $this->_errors[] = $this->l(
                    'Failed to register hooks'
                );
            }
            if (!NostoCustomerManager::createTables()) {
                $success = false;
                $this->_errors[] = $this->l(
                    'Failed to create Nosto customer table'
                );
            }
            if (!NostoAdminTabManager::install()) {
                $success = false;
                $this->_errors[] = $this->l(
                    'Failed to create Nosto admin tab'
                );
            }
            if (!$this->initHooks()) {
                $success = false;
            }
            // For versions < 1.5.3.1 we need to keep track of the currently installed version.
            // This is to enable auto-update of the module by running its upgrade scripts.
            // This config value is updated in the NostoTaggingUpdater helper every time the module is updated.
            if ($success) {
                $success = $this->registerHook('actionObjectUpdateAfter')
                    && $this->registerHook('actionObjectDeleteAfter')
                    && $this->registerHook('actionObjectAddAfter')
                    && $this->registerHook('actionObjectCurrencyUpdateAfter')
                    && $this->registerHook('displayBackOfficeTop')
                    && $this->registerHook('displayBackOfficeHeader');
                // New hooks in 1.7
                if (version_compare(_PS_VERSION_, '1.7.0.0', '>=')) {
                    $this->registerHook('displayNav1');
                }
            }
        }

        return $success;
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
            && NostoHelperAccount::deleteAll()
            && NostoHelperConfig::purge()
            && NostoCustomerManager::dropTables()
            && NostoAdminTabManager::uninstall();
    }

    /**
     * Get content for displaying message
     *
     * @return string display content
     */
    private function displayMessages()
    {
        $output = '';
        if (($errorMessage = Tools::getValue('oauth_error')) !== false) {
            $output .= $this->displayError($this->l($errorMessage));
        }
        if (($successMessage = Tools::getValue('oauth_success')) !== false) {
            $output .= $this->displayConfirmation($this->l($successMessage));
        }

        foreach (NostoHelperFlash::getList('success') as $flash_message) {
            $output .= $this->displayConfirmation($flash_message);
        }
        foreach (NostoHelperFlash::getList('error') as $flash_message) {
            $output .= $this->displayError($flash_message);
        }

        if (Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $output .= $this->displayError($this->l('Please choose a shop to configure Nosto for.'));
        }

        return $output;
    }

    /**
     * Renders the module administration form.
     * Also handles the form submit action.
     *
     * @return string The HTML to output.
     */
    public function getContent()
    {
        $output = $this->displayMessages();

        $indexController = new NostoIndexController();
        $smartyMetaData = $indexController->getSmartyMetaData($this);
        $this->getSmarty()->assign($smartyMetaData);

        $this->getSmarty()->assign(array(
            'module_path' => $this->_path
        ));
        $output .= $this->display(__FILE__, $this->getSettingsTemplate());

        return $output;
    }

    /**
     * @return string
     */
    private function getSettingsTemplate()
    {
        $template_file = 'views/templates/admin/config-bootstrap.tpl';
        if (_PS_VERSION_ < '1.6') {
            $template_file = 'views/templates/admin/legacy-config-bootstrap.tpl';
        }

        return $template_file;
    }

    /**
     * Hook for adding content to the <head> section of the HTML pages.
     * Adds the Nosto embed script.
     *
     * @return string The HTML to output
     */
    public function hookDisplayHeader()
    {
        return NostoHeaderContent::get($this);
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
     * Also updates exchange rates if needed.
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
        if ($ctrl instanceof AdminController && method_exists($ctrl, 'addCss')) {
            $ctrl->addCss($this->_path . 'views/css/nostotagging-back-office.css');
        }
        $this->updateExchangeRatesIfNeeded(false);
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
        return NostoDefaultTagging::get($this);
    }

    /**
     * Hook for adding content to the top of every page in displayNav1.
     *
     * Adds customer and cart tagging.
     * Adds nosto elements.
     *
     * @since Prestashop 1.7.0.0
     * @return string The HTML to output
     */
    public function hookDisplayNav1()
    {
        return NostoDefaultTagging::get($this);
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
        $html = NostoDefaultTagging::get($this);
        $html .= NostoRecommendationElement::get("nosto-page-footer");
        return $html;
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
        return NostoRecommendationElement::get("nosto-column-left");
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
        return NostoRecommendationElement::get("nosto-column-right");
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
     * @return string The HTML to output
     */
    public function hookDisplayFooterProduct()
    {
        $html = '';
        $html .= NostoRecommendationElement::get("nosto-page-product1");
        $html .= NostoRecommendationElement::get("nosto-page-product2");
        $html .= NostoRecommendationElement::get("nosto-page-product3");
        return $html;
    }

    /**
     * Backwards compatibility hook.
     *
     * @see NostoTagging::hookDisplayFooterProduct()
     * @return string The HTML to output
     */
    public function hookProductFooter()
    {
        return $this->hookDisplayFooterProduct();
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
        NostoCustomerManager::updateNostoId();

        $html = '';
        $html .= NostoRecommendationElement::get("nosto-page-cart1");
        $html .= NostoRecommendationElement::get("nosto-page-cart2");
        $html .= NostoRecommendationElement::get("nosto-page-cart3");
        return $html;
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
     * @return string The HTML to output
     */
    public function hookDisplayOrderConfirmation()
    {
        if (!Nosto::isContextConnected()) {
            return '';
        }

        return ''; //TODO: Nothing rendered here?!?!
    }

    /**
     * Backwards compatibility hook.
     *
     * @see NostoTagging::hookDisplayOrderConfirmation()
     * @return string The HTML to output
     */
    public function hookOrderConfirmation()
    {
        return $this->hookDisplayOrderConfirmation();
    }

    /**
     * Hook for adding content to category page above the product list.
     *
     * Adds nosto elements.
     *
     * Please note that in order for this hook to be executed, it will have to be added to the
     * theme category.tpl file.
     *
     * - Theme category.tpl: add the below line to the top of the file
     *   {hook h='displayCategoryTop'}
     *
     * @return string The HTML to output
     */
    public function hookDisplayCategoryTop()
    {
        return NostoRecommendationElement::get("nosto-page-category1");
    }

    /**
     * Hook for adding content to category page below the product list.
     *
     * Adds nosto elements.
     *
     * Please note that in order for this hook to be executed, it will have to be added to the
     * theme category.tpl file.
     *
     * - Theme category.tpl: add the below line to the end of the file
     *   {hook h='displayCategoryFooter'}
     *
     * @return string The HTML to output
     */
    public function hookDisplayCategoryFooter()
    {
        return NostoRecommendationElement::get("nosto-page-category2");
    }

    /**
     * Hook for adding content to search page above the search result list.
     *
     * Adds nosto elements.
     *
     * Please note that in order for this hook to be executed, it will have to be added to the
     * theme search.tpl file.
     *
     * - Theme search.tpl: add the below line to the top of the file
     *   {hook h='displaySearchTop'}
     *
     * @return string The HTML to output
     */
    public function hookDisplaySearchTop()
    {
        return NostoRecommendationElement::get("nosto-page-search1");
    }

    /**
     * Hook for adding content to search page below the search result list.
     *
     * Adds nosto elements.
     *
     * Please note that in order for this hook to be executed, it will have to be added to the
     * theme search.tpl file.
     *
     * - Theme search.tpl: add the below line to the end of the file
     *   {hook h='displaySearchFooter'}
     *
     * @return string The HTML to output
     */
    public function hookDisplaySearchFooter()
    {
        return NostoRecommendationElement::get("nosto-page-search2");
    }

    /**
     * Hook for updating the customer link table with the Prestashop customer id and the Nosto
     * customer id.
     */
    public function hookDisplayPaymentTop()
    {
        NostoCustomerManager::updateNostoId();
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
     * This is a fallback for the regular order tagging on the "order confirmation page", as there
     * are cases when the customer does not get redirected back to the shop after the payment is
     * completed.
     *
     * @param array $params
     */
    public function hookActionOrderStatusPostUpdate(array $params)
    {
        $operation = new NostoOrderService(Context::getContext());
        $operation->send($params);
    }

    /**
     * Backwards compatibility hook.
     *
     * @see NostoTagging::hookActionOrderStatusPostUpdate()
     * @param array $params
     */
    public function hookPostUpdateOrderStatus(array $params)
    {
        $this->hookActionOrderStatusPostUpdate($params);
    }

    /**
     * Hook for adding content to the home page.
     * Adds nosto elements.
     *
     * @return string The HTML to output
     */
    public function hookDisplayHome()
    {
        $html = '';
        $html .= NostoRecommendationElement::get("frontpage-nosto-1");
        $html .= NostoRecommendationElement::get("frontpage-nosto-2");
        $html .= NostoRecommendationElement::get("frontpage-nosto-3");
        $html .= NostoRecommendationElement::get("frontpage-nosto-4");
        return $html;
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
    public function hookActionObjectUpdateAfter(array $params)
    {
        $operation = new NostoProductService();
        $operation->upsert($params);
    }

    /**
     * Hook that is fired after a object is deleted from the db.
     *
     * @param array $params
     */
    public function hookActionObjectDeleteAfter(array $params)
    {
        $operation = new NostoProductService();
        $operation->delete($params);
    }

    /**
     * Hook that is fired after a object has been created in the db.
     *
     * @param array $params
     */
    public function hookActionObjectAddAfter(array $params)
    {
        $operation = new NostoProductService();
        $operation->upsert($params);
    }

    /**
     * Hook called when a product is update with a new picture, right after said update
     *
     * @see NostoTagging::hookActionObjectUpdateAfter
     * @param array $params
     */
    public function hookUpdateProduct(array $params)
    {
        $this->hookActionObjectUpdateAfter(array('object' => $params['product']));
    }

    /**
     * Hook called when a product is deleted, right before said deletion
     *
     * @see NostoTagging::hookActionObjectDeleteAfter
     * @param array $params
     */
    public function hookDeleteProduct(array $params)
    {
        $this->hookActionObjectDeleteAfter(array('object' => $params['product']));
    }

    /**
     * Hook called when a product is added, right after said addition
     *
     * @see NostoTagging::hookActionObjectAddAfter
     * @param array $params
     */
    public function hookAddProduct(array $params)
    {
        $this->hookActionObjectAddAfter(array('object' => $params['product']));
    }

    /**
     * Hook called during an the validation of an order, the status of which being something other
     * than
     * "canceled" or "Payment error", for each of the order's item
     *
     * @see NostoTagging::hookActionObjectUpdateAfter
     * @param array $params
     */
    public function hookUpdateQuantity(array $params)
    {
        $this->hookActionObjectUpdateAfter(array('object' => $params['product']));
    }

    /**
     * Gets the current admin config language data.
     *
     * @param array $languages list of valid languages.
     * @param int $id_lang if a specific language is required.
     * @return array the language data array.
     */
    public static function ensureAdminLanguage(array $languages, $id_lang)
    {
        foreach ($languages as $language) {
            if ($language['id_lang'] == $id_lang) {
                return $language;
            }
        }

        if (isset($languages[0])) {
            return $languages[0];
        } else {
            return array('id_lang' => 0, 'name' => '', 'iso_code' => '');
        }
    }

    /**
     * Returns hidden nosto recommendation elements for the current controller.
     * These are used as a fallback for showing recommendations if the appropriate hooks are not
     * present in the theme. The hidden elements are put into place and shown in the shop with
     * JavaScript.
     *
     * @return string the html.
     */
    public function getHiddenRecommendationElements()
    {
        if (NostoHelperController::isController('index')) {
            // The home page.
            return $this->display(__FILE__, 'views/templates/hook/home_hidden-nosto-elements.tpl');
        } elseif (NostoHelperController::isController('product')) {
            // The product page.
            return $this->display(__FILE__,
                'views/templates/hook/footer-product_hidden-nosto-elements.tpl');
        } elseif (NostoHelperController::isController('order') && (int)Tools::getValue('step',
                0) === 0
        ) {
            // The cart summary page.
            return $this->display(__FILE__,
                'views/templates/hook/shopping-cart-footer_hidden-nosto-elements.tpl');
        } elseif (NostoHelperController::isController('category')
            || NostoHelperController::isController('manufacturer')
        ) {
            // The category/manufacturer page.
            return $this->display(__FILE__,
                'views/templates/hook/category-footer_hidden-nosto-elements.tpl');
        } elseif (NostoHelperController::isController('search')) {
            // The search page.
            return $this->display(__FILE__,
                'views/templates/hook/search_hidden-nosto-elements.tpl');
        } elseif (NostoHelperController::isController('pagenotfound')
            || NostoHelperController::isController('404')
        ) {
            // The search page.
            return $this->display(__FILE__, 'views/templates/hook/404_hidden_nosto-elements.tpl');
        } elseif (NostoHelperController::isController('order-confirmation')) {
            // The search page.
            return $this->display(__FILE__,
                'views/templates/hook/order-confirmation_hidden_nosto-elements.tpl');
        } else {
            // If the current page is not one of the ones we want to show recommendations on, just return empty.
            return '';
        }
    }

    /**
     * Returns the admin url.
     * Note the url is parsed from the current url, so this can only work if called when on the
     * admin page.
     *
     * @return string the url.
     */
    protected function getAdminUrl()
    {
        $current_url = Tools::getHttpHost(true) . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
        $parsed_url = NostoSDKHttpRequest::parseUrl($current_url);
        $parsed_query_string = NostoSDKHttpRequest::parseQueryString($parsed_url['query']);
        $valid_params = array(
            'controller',
            'token',
            'configure',
            'tab_module',
            'module_name',
            'tab',
        );
        $query_params = array();
        foreach ($valid_params as $valid_param) {
            if (isset($parsed_query_string[$valid_param])) {
                $query_params[$valid_param] = $parsed_query_string[$valid_param];
            }
        }
        $parsed_url['query'] = http_build_query($query_params);
        return NostoSDKHttpRequest::buildUrl($parsed_url);
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
        $success = true;
        if (!empty(self::$custom_hooks)) {
            foreach (self::$custom_hooks as $hook) {
                $callback = array(
                    'Hook',
                    (method_exists('Hook', 'getIdByName')) ? 'getIdByName' : 'get'
                );
                $id_hook = call_user_func($callback, $hook['name']);
                if (empty($id_hook)) {
                    $new_hook = new Hook();
                    $new_hook->name = pSQL($hook['name']);
                    $new_hook->title = pSQL($hook['title']);
                    $new_hook->description = pSQL($hook['description']);
                    $new_hook->add();
                    $id_hook = $new_hook->id;
                    if (!$id_hook) {
                        $success = false;
                    }
                }
            }
        }

        return $success;
    }

    /**
     * Method for resolving correct smarty object
     *
     * @return Smarty|Smarty_Data
     * @throws NostoSDKException
     */
    protected function getSmarty()
    {
        if (!empty($this->smarty)
            && method_exists($this->smarty, 'assign')
        ) {
            return $this->smarty;
        } elseif (!empty($this->context->smarty)
            && method_exists($this->context->smarty, 'assign')
        ) {
            return $this->context->smarty;
        }

        throw new NostoSDKException('Could not find smarty');
    }

    /**
     * Updates the exchange rates to Nosto when user logs in or logs out
     */
    public function hookDisplayBackOfficeTop()
    {
        $this->checkNotifications();
    }

    public function hookBackOfficeFooter()
    {
        return $this->updateExchangeRatesIfNeeded(false);
    }

    /**
     * Defines exchange rates updated for current session
     */
    public function defineExchangeRatesAsUpdated()
    {
        if (Context::getContext()->cookie && $this->adminLoggedIn()) {
            /** @noinspection PhpUndefinedFieldInspection */
            Context::getContext()->cookie->nostoExchangeRatesUpdated = (string)true;
        }
    }

    /**
     * Checks if the exchange rates have been updated during the current
     * admin session
     *
     * @return boolean
     */
    public function exchangeRatesShouldBeUpdated()
    {
        if (!$this->adminLoggedIn()) {
            return false;
        }

        $cookie = Context::getContext()->cookie;
        if (
            isset($cookie->nostoExchangeRatesUpdated)
            && $cookie->nostoExchangeRatesUpdated == true //@codingStandardsIgnoreLine
        ) {

            return false;
        }

        return true;
    }

    /**
     * Updates the exchange rates to Nosto when currency object is saved
     */
    public function hookActionObjectCurrencyUpdateAfter() {
        return $this->updateExchangeRatesIfNeeded(true);
    }

    /**
     * Updates the exchange rates to Nosto if needed
     *
     * @param boolean $force if set to true cookie check is ignored
     */
    public function updateExchangeRatesIfNeeded($force = false)
    {
        if ($this->exchangeRatesShouldBeUpdated() || $force === true) {
            $this->defineExchangeRatesAsUpdated(); // This ensures we only try this at once
            try {
                $operation = new NostoRatesService();
                $operation->updateExchangeRatesForAllStores();
                $this->defineExchangeRatesAsUpdated();
            } catch (NostoSDKException $e) {
                NostoHelperLogger::error($e, 'Exchange rate sync failed with error');
            }
        }
    }

    /**
     * Checks if user is logged into store admin
     *
     * @return bool
     */
    public function adminLoggedIn()
    {
        /* @var Employee $employee */
        $employee = $this->context->employee;
        $logged_in = false;
        if ($employee instanceof Employee && $employee->id) {
            $logged_in = true;
        }

        return $logged_in;
    }

    /**
     * Checks all Nosto notifications and adds them as an admin notification
     */
    public function checkNotifications()
    {
        $notifications = NostoNotificationManager::getAll();
        if (is_array($notifications) && count($notifications) > 0) {
            /* @var NostoNotification $notification */
            foreach ($notifications as $notification) {
                if (
                    $notification->getNotificationType() === NostoSDKNotification::TYPE_MISSING_INSTALLATION
                    && !NostoHelperController::isController('AdminModules')
                ) {
                    continue;
                }
                $this->addPrestashopNotification($notification);
            }
        }
    }

    /**
     * Adds a Prestashop admin notification
     *
     * @param NostoNotification $notification
     */
    protected function addPrestashopNotification(NostoNotification $notification)
    {
        switch ($notification->getNotificationSeverity()) {
            case NostoSDKNotification::SEVERITY_INFO:
                $this->adminDisplayInformation($notification->getFormattedMessage());
                break;
            case NostoSDKNotification::SEVERITY_WARNING:
                $this->adminDisplayWarning($notification->getFormattedMessage());
                break;
            default:
        }
    }
}
