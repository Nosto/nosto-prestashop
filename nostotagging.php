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

use Nosto\NostoException;

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

/**
 * Main module class the is responsible for all the module behaviour. This class is to be kept
 * lightweight with no more than single line method bodies that simply delegate to other services,
 * helpers or manager.
 *
 * @property Context $context
 * @property string $bootstrap
 */
class NostoTagging extends Module
{
    /** @var bool */
    public $bootstrap;
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
        $this->bootstrap = true; // Necessary for Bootstrap CSS initialisation in the UI
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
                || !$this->registerHook('productFooter')
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
                $this->_errors[] = $this->l('Failed to create Nosto customer table');
            }
            if (!NostoAdminTabManager::install()) {
                $success = false;
                $this->_errors[] = $this->l('Failed to create Nosto admin tab');
            }
            if (!NostoHookManager::initHooks(self::$custom_hooks)) {
                $success = false;
                $this->_errors[] = $this->l('Failed to register custom Nosto hooks');
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

        $template_file = 'views/templates/admin/config-bootstrap.tpl';
        if (_PS_VERSION_ < '1.6') {
            $template_file = 'views/templates/admin/legacy-config-bootstrap.tpl';
        }
        $output .= $this->display(__FILE__, $template_file);

        return $output;
    }

    /**
     * Layout hook for adding content to the <head> of every page. This hook renders the entire
     * client script, the add-to-cart script and some meta tags
     *
     * @return string The HTML to output
     */
    public function hookDisplayHeader()
    {
        return NostoHeaderContent::get($this);
    }

    /**
     * Backwards compatibility layout hook for adding content to the <head> of every page. This hook
     * should not have any logic and should only delegate to another hook.
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
     * Layout hook for adding content to the header of every page. This hook renders the entire
     * tagging if the tagging wasn't rendered in a previous hook.
     *
     * @return string The HTML to output
     */
    public function hookDisplayTop()
    {
        $html = '';
        if (NostoHelperConfig::getNostotaggingRenderPosition()
            !== NostoHelperConfig::NOSTOTAGGING_POSITION_FOOTER) {
            $html = NostoDefaultTagging::get($this);
            $html .= self::dispatchPseudoHooks();
        }

        return $html;
    }

    /**
     * Returns hidden nosto recommendation elements for the current controller.
     * These are used as a fallback for showing recommendations if the appropriate hooks are not
     * present in the theme. The hidden elements are put into place and shown in the shop with
     * JavaScript.
     *
     * @return string the html.
     */
    public static function dispatchPseudoHooks()
    {
        $methodName = 'pseudoHookLoadingPage';
        $methodName .= str_replace('-', '', NostoHelperController::getControllerName());
        if (method_exists('NostoHeaderContent', $methodName)) {
            return self::$methodName();
        } else {
            // If the current page is not one of the ones we want to show recommendations on, just
            // return empty.
            return '';
        }
    }

    private static function pseudoHookLoadingPageIndex()
    {
        $html = NostoHiddenElement::append('frontpage-nosto-1');
        $html .= NostoHiddenElement::append('frontpage-nosto-2');
        $html .= NostoHiddenElement::append('frontpage-nosto-3');
        $html .= NostoHiddenElement::append('frontpage-nosto-4');

        return $html;
    }

    private static function pseudoHookLoadingPageProduct()
    {
        $html = NostoHiddenElement::append('nosto-page-product1');
        $html .= NostoHiddenElement::append('nosto-page-product2');
        $html .= NostoHiddenElement::append('nosto-page-product3');

        return $html;
    }

    private static function pseudoHookLoadingPageOrder()
    {
        if ((int)Tools::getValue('step', 0) !== 0) {
            return '';
        }

        $html = NostoHiddenElement::append('nosto-page-cart1');
        $html .= NostoHiddenElement::append('nosto-page-cart2');
        $html .= NostoHiddenElement::append('nosto-page-cart3');

        return $html;
    }

    private static function pseudoHookLoadingPageCategory()
    {
        $html = NostoHiddenElement::append('nosto-page-category1');
        $html .= NostoHiddenElement::append('nosto-page-category2');

        return $html;
    }

    private static function pseudoHookLoadingPageManufacturer()
    {
        return self::pseudoHookLoadingPageCategory();
    }

    private static function pseudoHookLoadingPageSearch()
    {
        $html = NostoHiddenElement::prepend('nosto-page-search1');
        $html .= NostoHiddenElement::append('nosto-page-search2');

        return $html;
    }

    private static function pseudoHookLoadingPagePageNotFound()
    {
        $html = NostoHiddenElement::append('notfound-nosto-1');
        $html .= NostoHiddenElement::append('notfound-nosto-2');
        $html .= NostoHiddenElement::append('notfound-nosto-3');

        return $html;
    }

    private static function pseudoHookLoadingPage404()
    {
        return self::pseudoHookLoadingPagePageNotFound();
    }

    private static function pseudoHookLoadingPageOrderConfirmation()
    {
        $html = NostoHiddenElement::append('thankyou-nosto-1');
        $html .= NostoHiddenElement::append('thankyou-nosto-2');

        return $html;
    }

    /**
     * Modern layout hook for adding content to the top of every page in displayNav1. This hooks is
     * newer 1.7 hook that does the same as the top hook. This hook should not have any logic and
     * should only delegate to another hook.
     *
     * @since Prestashop 1.7.0.0
     * @return string The HTML to output
     */
    public function hookDisplayNav1()
    {
        return $this->hookDisplayTop();
    }

    /**
     * Backwards compatibility layout hook that renders content in the header of every page. This
     * hook should not have any logic and should only delegate to another hook.
     *
     * @see NostoTagging::hookDisplayTop()
     * @return string The HTML to output
     */
    public function hookTop()
    {
        return $this->hookDisplayTop();
    }

    /**
     * Layout hook for adding content to the footer of every page. This hook renders a recommendation
     * element and also renders the entire tagging if the tagging wasn't rendered in a previous
     * hook.
     *
     * @return string The HTML to output
     */
    public function hookDisplayFooter()
    {
        $html = '';
        if (NostoHelperConfig::getNostotaggingRenderPosition()
            === NostoHelperConfig::NOSTOTAGGING_POSITION_FOOTER) {
            $html = NostoDefaultTagging::get($this);
            $html .= self::dispatchPseudoHooks();
        }
        $html .= NostoRecommendationElement::get("nosto-page-footer");

        return $html;
    }

    /**
     * Backwards compatibility layout hook for adding content to the footer of every page. This hook
     * should not have any logic and should only delegate to another hook.
     *
     * @see NostoTagging::hookDisplayFooter()
     * @return string The HTML to output
     */
    public function hookFooter()
    {
        return $this->hookDisplayFooter();
    }

    /**
     * Layout hook for adding content to the left column of every page. This hook renders a single
     * recommendation element. This hook is extremely theme-dependant and may not always exist.
     *
     * @return string The HTML to output
     */
    public function hookDisplayLeftColumn()
    {
        return NostoRecommendationElement::get("nosto-column-left");
    }

    /**
     * Backwards compatibility layout hook for adding content to the left column of every page.
     * This hook should not have any logic and should only delegate to another hook.
     *
     * @see NostoTagging::hookDisplayRightColumn()
     * @return string The HTML to output
     */
    public function hookLeftColumn()
    {
        return $this->hookDisplayLeftColumn();
    }

    /**
     * Layout hook for adding content to the right column of every page. This hook renders a single
     * recommendation element. This hook is extremely theme-dependant and may not always exist.
     *
     * @return string The HTML to output
     */
    public function hookDisplayRightColumn()
    {
        return NostoRecommendationElement::get("nosto-column-right");
    }

    /**
     * Backwards compatibility layout hook for adding content to the right column of every page.
     * This hook should not have any logic and should only delegate to another hook.
     *
     * @see NostoTagging::hookDisplayRightColumn()
     * @return string The HTML to output
     */
    public function hookRightColumn()
    {
        return $this->hookDisplayRightColumn();
    }

    /**
     * Layout hook for adding content below the product description on the product page. This hook
     * renders three recommendation elements. The product tagging is omitted from here and instead
     * rendered along with the rest of the tagging to keep all the tagging consolidated.
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
     * Backwards compatibility layout hook for adding content below the product description on the
     * product page. This hook should not have any logic and should only delegate to another hook.
     *
     * @see NostoTagging::hookDisplayFooterProduct()
     * @return string The HTML to output
     */
    public function hookProductFooter()
    {
        return $this->hookDisplayFooterProduct();
    }

    /**
     * Layout hook for adding content to the cart page below the itemised cart listing. This hooks
     * renders three recommendation elements on the cart page and also updates the customer link
     * table.
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
     * Backwards compatibility layout hook for adding content to the cart page below the itemised
     * cart listing. This hook should not have any logic and should only delegate to another hook.
     *
     * @see NostoTagging::hookDisplayShoppingCartFooter()
     * @return string The HTML to output
     */
    public function hookShoppingCart()
    {
        return $this->hookDisplayShoppingCartFooter();
    }

    /**
     * Backwards compatibility layout hook for adding content to the order page below the itemised
     * order listing.
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
     * Backwards compatibility layout hook for adding content to the order page below the itemised
     * order listing. This hook should not have any logic and should only delegate to another hook.
     *
     * @see NostoTagging::hookDisplayOrderConfirmation()
     * @return string The HTML to output
     */
    public function hookOrderConfirmation()
    {
        return $this->hookDisplayOrderConfirmation();
    }

    /**
     * Layout hook for adding content to search page above the category items list. This hook renders
     * a single recommendation element.
     * <br />
     * Please note that in order for this hook to be executed, it will have to be added to the
     * theme category.tpl file.
     *
     *   {hook h='displayCategoryTop'}
     *
     * @return string The HTML to output
     */
    public function hookDisplayCategoryTop()
    {
        return NostoRecommendationElement::get("nosto-page-category1");
    }

    /**
     * Layout hook for adding content to search page below the category items list. This hook renders
     * a single recommendation element.
     * <br />
     * Please note that in order for this hook to be executed, it will have to be added to the
     * theme category.tpl file.
     *
     *   {hook h='displayCategoryFooter'}
     *
     * @return string The HTML to output
     */
    public function hookDisplayCategoryFooter()
    {
        return NostoRecommendationElement::get("nosto-page-category2");
    }

    /**
     * Layout hook for adding content to search page above the search result list. This hook renders
     * a single recommendation element.
     * <br />
     * Please note that in order for this hook to be executed, it will have to be added to the
     * theme search.tpl file.
     *
     *   {hook h='displaySearchTop'}
     *
     * @return string The HTML to output
     */
    public function hookDisplaySearchTop()
    {
        return NostoRecommendationElement::get("nosto-page-search1");
    }

    /**
     * Layout hook for adding content to search page below the search result list. This hook renders
     * a single recommendation element.
     * <br />
     * Please note that in order for this hook to be executed, it will have to be added to the
     * theme search.tpl file.
     *
     *   {hook h='displaySearchFooter'}
     *
     * @return string The HTML to output
     */
    public function hookDisplaySearchFooter()
    {
        return NostoRecommendationElement::get("nosto-page-search2");
    }

    /**
     * Layout hook for updating the customer link table with the Prestashop customer id and the Nosto
     * customer id. This hook doesn't render anything as the cart tagging is rendered along with the
     * other tagging while the recommendation elements are at the bottom of the page. No recommendation
     * elements are rendered here as it is too intrusive.
     */
    public function hookDisplayPaymentTop()
    {
        NostoCustomerManager::updateNostoId();
    }

    /**
     * Backwards compatibility layout hook that renders content above the payment page. This
     * hook should not have any logic and should only delegate to another hook.
     *
     * @see NostoTagging::hookDisplayPaymentTop()
     */
    public function hookPaymentTop()
    {
        $this->hookDisplayPaymentTop();
    }

    /**
     * Observer hook that is called when the order's status is updated. This hook sends an order
     * order confirmation to Nosto via the API.
     *
     * This is a fallback for the regular order tagging on the "order confirmation page", as there
     * are cases when the customer does not get redirected back to the shop after the payment is
     * completed.
     *
     * @param array $params the observer parameters, one of which contains the order model
     */
    public function hookActionOrderStatusPostUpdate(array $params)
    {
        $operation = new NostoOrderService();
        $operation->send($params);
    }

    /**
     * Backwards compatibility observer hook that is called when an order's status is updated. This
     * hook should not have any logic and should only delegate to another hook.
     *
     * @see NostoTagging::hookActionOrderStatusPostUpdate()
     * @param array $params the observer parameters, one of which contains the order model
     */
    public function hookPostUpdateOrderStatus(array $params)
    {
        $this->hookActionOrderStatusPostUpdate($params);
    }

    /**
     * Layout hook for adding content to the home page. This hooks renders four recommendation
     * elements on the front page
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
     * Backwards compatibility layout hook for adding content to the home page. This hook should
     * not have any logic and should only delegate to another hook.
     *
     * @see NostoTagging::hookDisplayHome()
     * @return string The HTML to output
     */
    public function hookHome()
    {
        return $this->hookDisplayHome();
    }

    /**
     * Hook that is fired after a object has been updated in the database. This hook intercepts all
     * object modifications but the service filters out non-product events i.e. events whose
     * parameter named `object` don't have a product object.
     *
     * @param array $params the observer parameters, one of which contains the mutated model
     */
    public function hookActionObjectUpdateAfter(array $params)
    {
        try {
            $operation = new NostoProductService();
            $operation->upsert($params);
        } catch (Exception $e) {
            NostoHelperLogger::error($e);
        }
    }

    /**
     * Hook that is fired after a object has been deleted in the database. This hook intercepts all
     * object deletions but the service filters out non-product events i.e. events whose
     * parameter named `object` don't have a product object.
     *
     * @param array $params the observer parameters, one of which contains the mutated model
     */
    public function hookActionObjectDeleteAfter(array $params)
    {
        try {
            $operation = new NostoProductService();
            $operation->delete($params);
        } catch (Exception $e) {
            NostoHelperLogger::error($e);
        }
    }

    /**
     * Hook that is fired after a object has been created in the database. This hook intercepts all
     * object additions but the service filters out non-product events i.e. events whose
     * parameter named `object` don't have a product object.
     *
     * @param array $params the observer parameters, one of which contains the mutated model
     */
    public function hookActionObjectAddAfter(array $params)
    {
        try {
            $operation = new NostoProductService();
            $operation->upsert($params);
        } catch (Exception $e) {
            NostoHelperLogger::error($e);
        }
    }

    /**
     * Observer hook that is called when a product is updated, right before said modification. This
     * hook sends a product upsert call to Nosto
     *
     * @see NostoTagging::hookActionObjectAddAfter
     * @param array $params the observer parameters, one of which contains the product model
     */
    public function hookUpdateProduct(array $params)
    {
        try {
            $this->hookActionObjectUpdateAfter(array('object' => $params['product']));
        } catch (Exception $e) {
            NostoHelperLogger::error($e);
        }
    }

    /**
     * Observer hook that is called when a product is deleted, right before said deletion. This hook
     * sends a product delete call to Nosto
     *
     * @see NostoTagging::hookActionObjectDeleteAfter
     * @param array $params the observer parameters, one of which contains the product model
     */
    public function hookDeleteProduct(array $params)
    {
        try {
            $this->hookActionObjectDeleteAfter(array('object' => $params['product']));
        } catch (Exception $e) {
            NostoHelperLogger::error($e);
        }
    }

    /**
     * Observer hook that is called when a product is created, right before said addition. This hook
     * sends a product upsert call to Nosto
     *
     * @see NostoTagging::hookActionObjectAddAfter
     * @param array $params the observer parameters, one of which contains the product model
     */
    public function hookAddProduct(array $params)
    {
        try {
            $this->hookActionObjectAddAfter(array('object' => $params['product']));
        } catch (Exception $e) {
            NostoHelperLogger::error($e);
        }
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
        try {
            $this->hookActionObjectUpdateAfter(array('object' => $params['product']));
        } catch (Exception $e) {
            NostoHelperLogger::error($e);
        }
    }

    /**
     * Admin hook that is triggered when the header of the back-office is being rendered. This hook
     * renders all the different warnings and information messages to be displayed.
     */
    public function hookDisplayBackOfficeTop()
    {
        NostoNotificationManager::checkAndDisplay($this);
    }

    /**
     * Admin hook that is triggered when the footer of the back-office is being rendered. This hook
     * sends all the exchange-rates to Nosto so if the merchant has forgotten to configure the cron,
     * we should still get some updates.
     */
    public function hookBackOfficeFooter()
    {
        return $this->updateExchangeRatesIfNeeded(false);
    }

    /**
     * Helper method to display a general message in the admin. This method is plug for the method
     * in the underlying class to increase the visibility from protected to public
     *
     * @param string $message the general message to be displayed
     * @return bool if the displaying of the general message was successful
     */
    public function adminDisplayInformation($message)
    {
        return parent::adminDisplayInformation($message);
    }

    /**
     * Helper method to display a warning message in the admin. This method is plug for the method
     * in the underlying class to increase the visibility from protected to public
     *
     * @param string $message the warning message to be displayed
     * @return bool if the displaying of the warning message was successful
     */
    public function adminDisplayWarning($message)
    {
        return parent::adminDisplayWarning($message);
    }

    /**
     * Helper method to render a template in the current Smarty context. All calls to the module's
     * display method require a relative path to the views directory and therefore this method is
     * used as to indirectly invoke the display method
     *
     * @param string $template the relative path to the template to render
     * @return string The HTML to output
     */
    public function render($template)
    {
        return parent::display(__FILE__, $template);
    }

    /**
     * Method for resolving correct smarty object
     *
     * @return Smarty|Smarty_Data
     * @throws NostoException
     */
    protected function getSmarty()
    {
        if (!empty($this->smarty) && method_exists($this->smarty, 'assign')) {
            return $this->smarty;
        } elseif (!empty($this->context->smarty) && method_exists($this->context->smarty, 'assign')) {
            return $this->context->smarty;
        }

        throw new NostoException('Could not find smarty');
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
    public function hookActionObjectCurrencyUpdateAfter()
    {
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
            } catch (NostoException $e) {
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
}
