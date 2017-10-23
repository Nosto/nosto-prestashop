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
 * We need to do this as this module file is parsed with eval() on the modules page, and eval() messes up the __FILE__.
 */
if ((basename(__FILE__) === 'nostotagging.php')) {
    $module_dir = dirname(__FILE__);
    require_once($module_dir.'/libs/nosto/php-sdk/src/config.inc.php');
    require_once($module_dir.'/classes/admin-notification.php');
    require_once($module_dir.'/classes/collections/exchange-rates.php');
    require_once($module_dir.'/classes/helpers/account.php');
    require_once($module_dir.'/classes/helpers/admin-tab.php');
    require_once($module_dir.'/classes/helpers/config.php');
    require_once($module_dir.'/classes/helpers/customer.php');
    require_once($module_dir.'/classes/helpers/flash-message.php');
    require_once($module_dir.'/classes/helpers/image.php');
    require_once($module_dir.'/classes/helpers/logger.php');
    require_once($module_dir.'/classes/helpers/notification.php');
    require_once($module_dir.'/classes/helpers/nosto-operation.php');
    require_once($module_dir.'/classes/helpers/product-operation.php');
    require_once($module_dir.'/classes/helpers/order-operation.php');
    require_once($module_dir.'/classes/helpers/updater.php');
    require_once($module_dir.'/classes/helpers/url.php');
    require_once($module_dir.'/classes/helpers/currency.php');
    require_once($module_dir.'/classes/helpers/context-factory.php');
    require_once($module_dir.'/classes/helpers/price.php');
    require_once($module_dir.'/classes/meta/account.php');
    require_once($module_dir.'/classes/meta/account-billing.php');
    require_once($module_dir.'/classes/meta/account-iframe.php');
    require_once($module_dir.'/classes/meta/account-owner.php');
    require_once($module_dir.'/classes/meta/oauth.php');
    require_once($module_dir.'/classes/models/base.php');
    require_once($module_dir.'/classes/models/cart.php');
    require_once($module_dir.'/classes/models/category.php');
    require_once($module_dir.'/classes/models/customer.php');
    require_once($module_dir.'/classes/models/order.php');
    require_once($module_dir.'/classes/models/order-buyer.php');
    require_once($module_dir.'/classes/models/price-variation.php');
    require_once($module_dir.'/classes/models/order-purchased-item.php');
    require_once($module_dir.'/classes/models/order-status.php');
    require_once($module_dir.'/classes/models/product.php');
    require_once($module_dir.'/classes/models/brand.php');
    require_once($module_dir.'/classes/models/search.php');
}

/**
 * NostoTagging module that integrates Nosto marketing automation service.
 *
 * @property Context $context
 */
class NostoTagging extends Module
{
    const AJAX_REQUEST_PAREMETER_KEY = 'ajax';
    /**
     * The version of the Nosto plug-in
     * @var string
     */
    const PLUGIN_VERSION = '2.8.9';

    /**
     * Internal name of the Nosto plug-in
     * @var string
     */
    const MODULE_NAME = 'nostotagging';

    /**
     * Page type constants
     */
    const PAGE_TYPE_FRONT_PAGE = 'front';
    const PAGE_TYPE_CART = 'cart';
    const PAGE_TYPE_PRODUCT = 'product';
    const PAGE_TYPE_CATEGORY = 'category';
    const PAGE_TYPE_SEARCH = 'search';
    const PAGE_TYPE_NOTFOUND = 'notfound';
    const PAGE_TYPE_ORDER = 'order';

    /**
     * Nosto cookie name
     */
    const COOKIE_NAME = '2c_cId';

    /**
     * Global cookie scope
     */
    const GLOBAL_COOKIES = '_COOKIE';

    /**
     * @var string the algorithm to use for hashing visitor id.
     */
    const VISITOR_HASH_ALGO = 'sha256';

    /**
     * Keeps the state of Nosto default tagging
     *
     * @var boolean
     */
    private static $tagging_rendered = false;

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
     */
    public function __construct()
    {
        $this->name = self::MODULE_NAME;
        $this->tab = 'advertising_marketing';
        $this->version = self::PLUGIN_VERSION;
        $this->author = 'Nosto';
        $this->need_instance = 1;
        $this->bootstrap = true;
        $this->ps_versions_compliancy = array('min' => '1.5', 'max' => _PS_VERSION_);
        $this->module_key = '8d80397cab6ca02dfe8ef681b48c37a3';

        parent::__construct();
        $this->displayName = $this->l('Nosto Personalization for PrestaShop');
        $this->description = $this->l(
            'Increase your conversion rate and average order value by delivering your customers personalized product
            recommendations throughout their shopping journey.'
        );

        NostoHttpRequest::buildUserAgent('Prestashop', _PS_VERSION_, $this->version);
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
            /* @var NostoTaggingHelperCustomer $helper_customer */
            $helper_customer = Nosto::helper('nosto_tagging/customer');
            if (!$helper_customer->createTables()) {
                $success = false;
                $this->_errors[] = $this->l(
                    'Failed to create Nosto customer table'
                );
            }
            if (!NostoTaggingHelperAdminTab::install()) {
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
                if (version_compare(_PS_VERSION_, '1.5.4.0', '<')) {
                    /** @var NostoTaggingHelperConfig $config_helper */
                    $config_helper = Nosto::helper('nosto_tagging/config');
                    $config_helper->saveInstalledVersion($this->version);
                }

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
            && NostoTaggingHelperAccount::deleteAll()
            && NostoTaggingHelperConfig::purge()
            && NostoTaggingHelperCustomer::dropTables()
            && NostoTaggingHelperAdminTab::uninstall();
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
        $admin_url = $this->getAdminUrl();

        /** @var NostoTaggingHelperConfig $config_helper */
        $config_helper = Nosto::helper('nosto_tagging/config');
        $config_helper->saveAdminUrl($admin_url);
        $output = '';
        $languages = Language::getLanguages(true, $this->context->shop->id);
        /** @var EmployeeCore $employee */
        $employee = $this->context->employee;
        $account_email = $employee->email;
        /** @var NostoTaggingHelperFlashMessage $helper_flash */
        $helper_flash = Nosto::helper('nosto_tagging/flash_message');
        /** @var NostoTaggingHelperUrl $helper_url */
        $helper_url = Nosto::helper('nosto_tagging/url');
        /** @var NostoTaggingHelperConfig $helper_config */
        $helper_config = Nosto::helper('nosto_tagging/config');
        $id_shop = null;
        $id_shop_group = null;
        if ($this->context->shop instanceof Shop) {
            $id_shop = $this->context->shop->id;
            $id_shop_group = $this->context->shop->id_shop_group;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $language_id = (int)Tools::getValue($this->name.'_current_language');
            $current_language = $this->ensureAdminLanguage($languages, $language_id);
            if (Shop::getContext() !== Shop::CONTEXT_SHOP) {
            // Do nothing.
                // After the redirect this will be checked again and an error message is outputted.
            } elseif ($current_language['id_lang'] != $language_id) {
                $helper_flash->add('error', $this->l('Language cannot be empty.'));
            } elseif (
                Tools::isSubmit('submit_nostotagging_new_account')
                || Tools::getValue('nostotagging_account_action') === 'newAccount'
            ) {
                $account_email = (string)Tools::getValue($this->name.'_account_email');
                if (empty($account_email)) {
                    $helper_flash->add('error', $this->l('Email cannot be empty.'));
                } elseif (!Validate::isEmail($account_email)) {
                    $helper_flash->add('error', $this->l('Email is not a valid email address.'));
                } else {
                    try {
                        if (Tools::isSubmit('nostotagging_account_details')) {
                            $account_details = Tools::jsonDecode(Tools::getValue('nostotagging_account_details'));
                        } else {
                            $account_details = false;
                        }
                        $this->createAccount($language_id, $account_email, $account_details);
                        $helper_config->clearCache();
                        $helper_flash->add(
                            'success',
                            $this->l(
                                'Account created. Please check your email and follow the instructions to set a'
                                . ' password for your new account within three days.'
                            )
                        );
                    } catch (NostoApiResponseException $e) {
                        $helper_flash->add(
                            'error',
                            $this->l(
                                'Account could not be automatically created due to missing or invalid parameters.'
                                . ' Please see your Prestashop logs for details'
                            )
                        );
                        /* @var NostoTaggingHelperLogger $logger */
                        $logger = Nosto::helper('nosto_tagging/logger');
                        $logger->error(
                            'Creating Nosto account failed: ' . $e->getMessage() .':'.$e->getCode(),
                            $e->getCode(),
                            'Employee',
                            (int)$employee->id
                        );
                    } catch (Exception $e) {
                        $helper_flash->add(
                            'error',
                            $this->l('Account could not be automatically created. Please see logs for details.')
                        );
                        /* @var NostoTaggingHelperLogger $logger */
                        $logger = Nosto::helper('nosto_tagging/logger');
                        $logger->error(
                            'Creating Nosto account failed: ' . $e->getMessage() .':'.$e->getCode(),
                            $e->getCode(),
                            'Employee',
                            (int)$employee->id
                        );
                    }
                }
            } elseif (
                Tools::isSubmit('submit_nostotagging_authorize_account')
                || Tools::getValue('nostotagging_account_action') === 'connectAccount'
                || Tools::getValue('nostotagging_account_action') === 'syncAccount'
            ) {
                $meta = new NostoTaggingMetaOauth();
                $meta->setModuleName($this->name);
                $meta->setModulePath($this->_path);
                $meta->loadData($this->context, $language_id);
                $client = new NostoOAuthClient($meta);
                Tools::redirect($client->getAuthorizationUrl(), '');
                die();
            } elseif (
                Tools::isSubmit('submit_nostotagging_reset_account')
                || Tools::getValue('nostotagging_account_action') === 'removeAccount'
            ) {
                $account = NostoTaggingHelperAccount::findByContext($this->context);
                $helper_config->clearCache();
                NostoTaggingHelperAccount::delete($account, $language_id);
            } elseif (Tools::isSubmit('submit_nostotagging_update_exchange_rates')) {
                $nosto_account = NostoTaggingHelperAccount::find($language_id, $id_shop_group, $id_shop);
                if ($nosto_account &&
                    NostoTaggingHelperAccount::updateCurrencyExchangeRates(
                        $nosto_account,
                        $this->context
                    )
                ) {
                    $helper_flash->add(
                        'success',
                        $this->l(
                            'Exchange rates successfully updated to Nosto'
                        )
                    );
                } else {
                    if (!$nosto_account->getApiToken(NostoApiToken::API_EXCHANGE_RATES)) {
                        $message = 'Failed to update exchange rates to Nosto due to a missing API token. 
                            Please, reconnect your account with Nosto';
                    } else {
                        $message = 'There was an error updating the exchange rates. 
                            See Prestashop logs for more information.';
                    }
                    $helper_flash->add(
                        'error',
                        $this->l($message)
                    );
                }
            } elseif (
                Tools::isSubmit('submit_nostotagging_advanced_settings')
                && Tools::isSubmit('multi_currency_method')
            ) {
                /** @var NostoTaggingHelperConfig $helper_config */
                $helper_config = Nosto::helper('nosto_tagging/config');
                /** @var NostoTaggingHelperFlashMessage $helper_flash */
                $helper_flash = Nosto::helper('nosto_tagging/flash_message');
                $helper_config->saveMultiCurrencyMethod(
                    Tools::getValue('multi_currency_method'),
                    $language_id,
                    $id_shop_group,
                    $id_shop
                );
                $helper_config->saveNostoTaggingRenderPosition(
                    Tools::getValue('nostotagging_position'),
                    $language_id,
                    $id_shop_group,
                    $id_shop
                );
                $helper_config->saveImageType(
                    Tools::getValue('image_type'),
                    $language_id,
                    $id_shop_group,
                    $id_shop
                );
                $account = NostoTaggingHelperAccount::find($language_id, $id_shop_group, $id_shop);
                $account_meta = new NostoTaggingMetaAccount();
                $account_meta->loadData($this->context, $language_id);
                // Make sure we Nosto is installed for the current store
                if (empty($account) || !$account->isConnectedToNosto()) {
                    Tools::redirect(
                        NostoHttpRequest::replaceQueryParamInUrl(
                            'language_id',
                            $language_id,
                            $admin_url
                        ),
                        ''
                    );
                    die;
                }
                try {
                    NostoTaggingHelperAccount::updateSettings($account, $account_meta);
                    $helper_flash->add('success', $this->l('The settings have been saved.'));
                } catch (NostoException $e) {
                    /* @var NostoTaggingHelperLogger $logger */
                    $logger = Nosto::helper('nosto_tagging/logger');
                    $logger->error(
                        __CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(),
                        $e->getCode(),
                        'Employee',
                        (int)$employee->id
                    );

                    $helper_flash->add(
                        'error',
                        $this->l('There was an error saving the settings. Please, see log for details.')
                    );
                }
                // Also update the exchange rates if multi currency is used
                if ($account_meta->getUseCurrencyExchangeRates()) {
                    NostoTaggingHelperAccount::updateCurrencyExchangeRates($account, $this->context);
                }
            }

            // Refresh the page after every POST to get rid of form re-submission errors.
            Tools::redirect(NostoHttpRequest::replaceQueryParamInUrl('language_id', $language_id, $admin_url), '');
            die;
        } else {
            $language_id = (int)Tools::getValue('language_id', 0);

            if (($error_message = Tools::getValue('oauth_error')) !== false) {
                $output .= $this->displayError($this->l($error_message));
            }
            if (($success_message = Tools::getValue('oauth_success')) !== false) {
                $output .= $this->displayConfirmation($this->l($success_message));
            }

            foreach ($helper_flash->getList('success') as $flash_message) {
                $output .= $this->displayConfirmation($flash_message);
            }
            foreach ($helper_flash->getList('error') as $flash_message) {
                $output .= $this->displayError($flash_message);
            }

            if (Shop::getContext() !== Shop::CONTEXT_SHOP) {
                $output .= $this->displayError($this->l('Please choose a shop to configure Nosto for.'));
            }
        }
        // Choose current language if it has not been set.
        if (!isset($current_language)) {
            $current_language = $this->ensureAdminLanguage($languages, $language_id);
            $language_id = (int)$current_language['id_lang'];
        }
        /** @var NostoAccount $account */
        $account = NostoTaggingHelperAccount::find($language_id, $id_shop_group, $id_shop);
        $missing_tokens = true;
        if (
            $account instanceof NostoAccountInterface
            && $account->getApiToken(NostoApiToken::API_EXCHANGE_RATES)
            && $account->getApiToken(NostoApiToken::API_SETTINGS)
        ) {
            $missing_tokens = false;
        }
        // When no account is found we will show the installation URL
        if (
            $account instanceof NostoAccountInterface === false
            && Shop::getContext() === Shop::CONTEXT_SHOP
        ) {
            $account_iframe = new NostoTaggingMetaAccountIframe();
            $account_iframe->loadData($this->context, $language_id);
            /* @var NostoHelperIframe $iframe_helper */
            $iframe_helper = Nosto::helper('iframe');
            $iframe_installation_url = $iframe_helper->getUrl($account_iframe, null, array('v'=>1));
        } else {
            $iframe_installation_url = null;
        }
        /** @var NostoTaggingHelperImage $helper_images */
        $helper_images = Nosto::helper('nosto_tagging/image');
        $this->getSmarty()->assign(array(
            $this->name.'_form_action' => $this->getAdminUrl(),
            $this->name.'_create_account' => $this->getAdminUrl(),
            $this->name.'_delete_account' => $this->getAdminUrl(),
            $this->name.'_connect_account' => $this->getAdminUrl(),
            $this->name.'_has_account' => ($account !== null),
            $this->name.'_account_name' => ($account !== null) ? $account->getName() : null,
            $this->name.'_account_email' => $account_email,
            $this->name.'_account_authorized' => ($account !== null) ? $account->isConnectedToNosto() : false,
            $this->name.'_languages' => $languages,
            $this->name.'_current_language' => $current_language,
            $this->name.'_translations' => array(
                'installed_heading' => sprintf(
                    $this->l('You have installed Nosto to your %s shop'),
                    $current_language['name']
                ),
                'installed_subheading' => sprintf(
                    $this->l('Your account ID is %s'),
                    ($account !== null) ? $account->getName() : ''
                ),
                'not_installed_subheading' => sprintf(
                    $this->l('Install Nosto to your %s shop'),
                    $current_language['name']
                ),
                'exchange_rate_crontab_example' =>sprintf(
                    '0 0 * * * curl --silent %s > /dev/null 2>&1',
                    $helper_url->getModuleUrl(
                        $this->name,
                        $this->_path,
                        'cronRates',
                        $current_language['id_lang'],
                        $id_shop,
                        array('token' => $this->getCronAccessToken())
                    )
                ),
            ),
            'multi_currency_method' => $helper_config->getMultiCurrencyMethod(
                $current_language['id_lang'],
                $id_shop_group,
                $id_shop
            ),
            'nostotagging_position' => $helper_config->getNostotaggingRenderPosition(
                $current_language['id_lang'],
                $id_shop_group,
                $id_shop
            ),
            $this->name.'_ps_version_class' => 'ps-'.str_replace('.', '', Tools::substr(_PS_VERSION_, 0, 3)),
            'missing_tokens' => $missing_tokens,
            'iframe_installation_url' => $iframe_installation_url,
            'iframe_origin' => $helper_url->getIframeOrigin(),
            'module_path' => $this->_path,
            'image_types' => $helper_images->getProductImageTypes(),
            'current_image_type' => $helper_config->getImageType(
                $current_language['id_lang'],
                $id_shop_group,
                $id_shop
            )
        ));
       // Try to login employee to Nosto in order to get a url to the internal setting pages,
        // which are then shown in an iframe on the module config page.
        if (
            $account
            && $account->isConnectedToNosto()
            && Shop::getContext() === Shop::CONTEXT_SHOP
        ) {
            try {
                $meta = new NostoTaggingMetaAccountIframe();
                $meta->setUniqueId($this->getUniqueInstallationId());
                $meta->loadData($this->context, $language_id);
                $url = $account->getIframeUrl($meta);
                if (!empty($url)) {
                    $this->getSmarty()->assign(array('iframe_url' => $url));
                }
            } catch (NostoException $e) {
                /* @var NostoTaggingHelperLogger $logger */
                $logger = Nosto::helper('nosto_tagging/logger');
                $logger->error(
                    __CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(),
                    $e->getCode(),
                    'Employee',
                    (int)$employee->id
                );
            }
        }
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
            $template_file  = 'views/templates/admin/legacy-config-bootstrap.tpl';
        }

        return $template_file;
    }
    /**
     * Creates a new Nosto account for given shop language.
     *
     * @param int $id_lang the language ID for which to create the account.
     * @param string $email the account owner email address.
     * @param string $account_details the details for the account.
     * @return bool true if account was created, false otherwise.
     */
    protected function createAccount($id_lang, $email, $account_details = "")
    {
        $meta = new NostoTaggingMetaAccount();
        $meta->loadData($this->context, $id_lang);
        $meta->getOwner()->setEmail($email);
        $meta->setDetails($account_details);
        /** @var NostoAccount $account */
        $account = NostoAccount::create($meta);
        $id_shop = null;
        $id_shop_group = null;
        if ($this->context->shop instanceof Shop) {
            $id_shop = $this->context->shop->id;
            $id_shop_group = $this->context->shop->id_shop_group;
        }

        return NostoTaggingHelperAccount::save($account, $id_lang, $id_shop_group, $id_shop);
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
     * Hook for adding content to the <head> section of the HTML pages.
     *
     * Adds the Nosto embed script.
     *
     * @return string The HTML to output
     */
    public function hookDisplayHeader()
    {
        /** @var NostoAccount $account */
        $account = NostoTaggingHelperAccount::findByContext($this->context);
        if ($account === null) {
            return '';
        }

        /** @var NostoTaggingHelperUrl $url_helper */
        $url_helper = Nosto::helper('nosto_tagging/url');
        $server_address = $url_helper->getServerAddress();
        /** @var LinkCore $link */
        $link = self::buildLinkClass();
        $hidden_recommendation_elements = $this->getHiddenRecommendationElements();
        $this->getSmarty()->assign(array(
            'server_address' => $server_address,
            'account_name' => $account->getName(),
            'nosto_version' => $this->version,
            'nosto_unique_id' => $this->getUniqueInstallationId(),
            'nosto_language' => Tools::strtolower($this->context->language->iso_code),
            'add_to_cart_url' => $link->getPageLink('cart.php'),
            'static_token' => Tools::getToken(false),
            'disable_autoload' => (bool)!empty($hidden_recommendation_elements)
        ));

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
            $ctrl->addCss($this->_path.'views/css/nostotagging-back-office.css');
        }
        $this->updateExhangeRatesIfNeeded(false);
    }

    /**
     * Generates and renders the defualt tagging if not already added to the
     * page
     *
     * @return string
     */
    public function getDefaultTagging()
    {
        $html = '';
        if (
            NostoTaggingHelperAccount::isContextConnected($this->context)
            && self::$tagging_rendered === false
        ) {
            $html = $this->generateDefaultTagging();
        }
        self::$tagging_rendered = true;

        return $html;
    }

    /**
     * Generates the tagging based on controller
     *
     * @return string
     */
    public function generateDefaultTagging()
    {
        $html = '';
        $html .= $this->getCustomerTagging();
        $html .= $this->getCartTagging();
        $html .= $this->getPriceVariationTagging();
        if ($this->isController('category')) {
            // The "getCategory" method is available from Prestashop 1.5.6.0 upwards.
            if (method_exists($this->context->controller, 'getCategory')) {
                $category = $this->context->controller->getCategory();
            } else {
                $category = new Category((int)Tools::getValue('id_category'), $this->context->language->id);
            }

            if (Validate::isLoadedObject($category)) {
                $html .= $this->getCategoryTagging($category);
                $html .= $this->getPageTypeTagging(self::PAGE_TYPE_CATEGORY);
            }
        } elseif ($this->isController('manufacturer')) {
            // The "getManufacturer" method is available from Prestashop 1.5.6.0 upwards.
            if (method_exists($this->context->controller, 'getManufacturer')) {
                $manufacturer = $this->context->controller->getManufacturer();
            } else {
                $manufacturer = new Manufacturer((int)Tools::getValue('id_manufacturer'), $this->context->language->id);
            }

            if (Validate::isLoadedObject($manufacturer)) {
                $html .= $this->getBrandTagging($manufacturer);
            }
        } elseif ($this->isController('search')) {
            $search_term = Tools::getValue('search_query', Tools::getValue('s'));
            if (!is_null($search_term)) {
                $html .= $this->getSearchTagging($search_term);
                $html .= $this->getPageTypeTagging(self::PAGE_TYPE_SEARCH);
            }
        } elseif ($this->isController('product')) {
            $product = $this->resolveProductInContext();
            $category = $this->resolveCategoryInContext();

            if ($product instanceof Product) {
                $html .= $this->getProductTagging($product, $category);
                $html .= $this->getPageTypeTagging(self::PAGE_TYPE_PRODUCT);
            }
        } elseif ($this->isController('order-confirmation')) {
            $order = $this->resolveOrderInContext();
            if ($order instanceof Order) {
                $html .= $this->getOrderTagging($order);
                $html .= $this->getPageTypeTagging(self::PAGE_TYPE_ORDER);
            }
        } elseif ($this->isController('pagenotfound') || $this->isController('404')) {
            $html .= $this->getPageTypeTagging(self::PAGE_TYPE_NOTFOUND);
        }
        $html .= $this->display(__FILE__, 'views/templates/hook/top_nosto-elements.tpl');
        $html .= $this->getHiddenRecommendationElements();

        return $html;
    }

    /**
     * Tries to resolve current / active order confirmation in context
     *
     * @return Order|null
     */
    protected function resolveOrderInContext()
    {
        $order = null;
        if ($id_order = (int)Tools::getValue('id_order')) {
            $order = new Order($id_order);
        }
        if (
            $order instanceof Order === false
            || !Validate::isLoadedObject($order)
        ) {
            $order = null;
        }

        return $order;
    }

    /**
     * Tries to resolve current / active category in context
     *
     * @return Category|null
     */
    protected function resolveCategoryInContext()
    {
        $category = null;
        if (method_exists($this->context->controller, 'getCategory')) {
            $category = $this->context->controller->getCategory();
        }
        if ($category instanceof Category == false) {
            $id_category = null;
            if (Tools::getValue('id_cateogry')) {
                $id_category = Tools::getValue('id_category');
            } elseif (
                isset($this->context->cookie)
                && ($this->context->cookie->last_visited_category)
            ) {
                $id_category = $this->context->cookie->last_visited_category;
            }
            if ($id_category) {
                $category = new Category($id_category, $this->context->language->id, $this->context->shop->id);
            }
        }
        if (
            $category instanceof Category === false
            || !Validate::isLoadedObject($category)
        ) {
            $category = null;
        }

        return $category;
    }

    /**
     * Tries to resolve current / active product in context
     *
     * @return null|Product
     */
    protected function resolveProductInContext()
    {
        $product = null;
        if (method_exists($this->context->controller, 'getProduct')) {
            $product = $this->context->controller->getProduct();
        }
        // If product is not set try to get use parameters (mostly for Prestashop < 1.5)
        if ($product instanceof Product == false) {
            $id_product = null;
            if (Tools::getValue('id_product')) {
                $id_product = Tools::getValue('id_product');
            }
            if ($id_product) {
                $product = new Product($id_product, true, $this->context->language->id);
            }
        }
        if (
            $product instanceof Product == false
            || !Validate::isLoadedObject($product)
        ) {
            $product = null;
        }

        return $product;
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
        return $this->getDefaultTagging();
    }

    /**
     * Hook for adding content to the top of every page in displayNav1.
     *
     * Adds customer and cart tagging.
     * Adds nosto elements.
     * @since Prestashop 1.7.0.0
     * @return string The HTML to output
     */
    public function hookDisplayNav1()
    {
        return $this->getDefaultTagging();
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
        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }
        $html = $this->getDefaultTagging();
        $html .= $this->display(__FILE__, 'views/templates/hook/footer_nosto-elements.tpl');
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
        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }

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
        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }

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
    public function hookDisplayFooterProduct(/** @noinspection PhpUnusedParameterInspection */
        array $params
    ) {
        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }

        return $this->display(__FILE__, 'views/templates/hook/footer-product_nosto-elements.tpl');
    }

    /**
     * Backwards compatibility hook.
     *
     * @see NostoTagging::hookDisplayFooterProduct()
     * @param array $params
     * @return string The HTML to output
     */
    public function hookProductFooter(array $params)
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
        /* @var NostoTaggingHelperCustomer $customer_helper */
        $customer_helper = Nosto::helper('nosto_tagging/customer');
        $customer_helper->updateNostoId();

        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }

        $html = $this->display(__FILE__, 'views/templates/hook/shopping-cart-footer_nosto-elements.tpl');
        $html .= $this->getPageTypeTagging(self::PAGE_TYPE_CART);
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
     * @param array $params
     * @return string The HTML to output
     */
    public function hookDisplayOrderConfirmation(/** @noinspection PhpUnusedParameterInspection */
        array $params
    ) {
        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }

        return '';
    }

    /**
     * Backwards compatibility hook.
     *
     * @see NostoTagging::hookDisplayOrderConfirmation()
     * @param array $params
     * @return string The HTML to output
     */
    public function hookOrderConfirmation(array $params)
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
        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }

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
        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }

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
        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }

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
        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }

        return $this->display(__FILE__, 'views/templates/hook/search-footer_nosto-elements.tpl');
    }

    /**
     * Hook for updating the customer link table with the Prestashop customer id and the Nosto customer id.
     */
    public function hookDisplayPaymentTop()
    {
        /* @var NostoTaggingHelperCustomer $customer_helper */
        $customer_helper = Nosto::helper('nosto_tagging/customer');
        $customer_helper->updateNostoId();
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
    public function hookActionOrderStatusPostUpdate(array $params)
    {
        if (isset($params['id_order'])) {
            $order = new Order($params['id_order']);
            if ($order instanceof Order === false) {
                return;
            }
            /* @var NostoTaggingHelperOrderOperation $order_operation*/
            $order_operation = Nosto::helper('nosto_tagging/order_operation');
            $context = $this->getContext();
            $order_operation->send($order, $context);
        }
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
     *
     * Adds nosto elements.
     *
     * @return string The HTML to output
     */
    public function hookDisplayHome()
    {
        if (!NostoTaggingHelperAccount::isContextConnected($this->context)) {
            return '';
        }
        $html = $this->display(__FILE__, 'views/templates/hook/home_nosto-elements.tpl');
        $html .= $this->getPageTypeTagging(self::PAGE_TYPE_FRONT_PAGE);
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
        if (isset($params['object'])) {
            if ($params['object'] instanceof Product) {
                /* @var $nostoProductOperation NostoTaggingHelperProductOperation */
                $nostoProductOperation = Nosto::helper('nosto_tagging/product_operation');
                $nostoProductOperation->updateProduct($params['object']);
            }
        }
    }

    /**
     * Hook that is fired after a object is deleted from the db.
     *
     * @param array $params
     */
    public function hookActionObjectDeleteAfter(array $params)
    {
        if (isset($params['object'])) {
            if ($params['object'] instanceof Product) {
                /** @var NostoTaggingHelperProductOperation $operation */
                $operation = Nosto::helper('nosto_tagging/product_operation');
                $operation->delete($params['object']);
            }
        }
    }

    /**
     * Hook that is fired after a object has been created in the db.
     *
     * @param array $params
     */
    public function hookActionObjectAddAfter(array $params)
    {
        if (isset($params['object'])) {
            if ($params['object'] instanceof Product) {
                /** @var NostoTaggingHelperProductOperation $operation */
                $operation = Nosto::helper('nosto_tagging/product_operation');
                $operation->create($params['object']);
            }
        }
    }

    /**
     * Hook called when a product is update with a new picture, right after said update
     *
     * @see NostoTagging::hookActionObjectUpdateAfter
     * @param array $params
     */
    public function hookUpdateProduct(array $params)
    {
        if (isset($params['product'])) {
            $this->hookActionObjectUpdateAfter(array('object' => $params['product']));
        }
    }

    /**
     * Hook called when a product is deleted, right before said deletion
     *
     * @see NostoTagging::hookActionObjectDeleteAfter
     * @param array $params
     */
    public function hookDeleteProduct(array $params)
    {
        if (isset($params['product'])) {
            $this->hookActionObjectDeleteAfter(array('object' => $params['product']));
        }
    }

    /**
     * Hook called when a product is added, right after said addition
     *
     * @see NostoTagging::hookActionObjectAddAfter
     * @param array $params
     */
    public function hookAddProduct(array $params)
    {
        if (isset($params['product'])) {
            $this->hookActionObjectAddAfter(array('object' => $params['product']));
        }
    }

    /**
     * Hook called during an the validation of an order, the status of which being something other than
     * "canceled" or "Payment error", for each of the order's item
     *
     * @see NostoTagging::hookActionObjectUpdateAfter
     * @param array $params
     */
    public function hookUpdateQuantity(array $params)
    {
        if (isset($params['product'])) {
            $this->hookActionObjectUpdateAfter(array('object' => $params['product']));
        }
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
     * Gets the current admin config language data.
     *
     * @param array $languages list of valid languages.
     * @param int $id_lang if a specific language is required.
     * @return array the language data array.
     */
    protected function ensureAdminLanguage(array $languages, $id_lang)
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
     * These are used as a fallback for showing recommendations if the appropriate hooks are not present in the theme.
     * The hidden elements are put into place and shown in the shop with JavaScript.
     *
     * @return string the html.
     */
    protected function getHiddenRecommendationElements()
    {
        if ($this->isController('index')) {
        // The home page.
            return $this->display(__FILE__, 'views/templates/hook/home_hidden-nosto-elements.tpl');
        } elseif ($this->isController('product')) {
        // The product page.
            return $this->display(__FILE__, 'views/templates/hook/footer-product_hidden-nosto-elements.tpl');
        } elseif ($this->isController('order') && (int)Tools::getValue('step', 0) === 0) {
        // The cart summary page.
            return $this->display(__FILE__, 'views/templates/hook/shopping-cart-footer_hidden-nosto-elements.tpl');
        } elseif ($this->isController('category') || $this->isController('manufacturer')) {
        // The category/manufacturer page.
            return $this->display(__FILE__, 'views/templates/hook/category-footer_hidden-nosto-elements.tpl');
        } elseif ($this->isController('search')) {
        // The search page.
            return $this->display(__FILE__, 'views/templates/hook/search_hidden-nosto-elements.tpl');
        } elseif ($this->isController('pagenotfound') || $this->isController('404')) {
        // The search page.
            return $this->display(__FILE__, 'views/templates/hook/404_hidden_nosto-elements.tpl');
        } elseif ($this->isController('order-confirmation')) {
        // The search page.
            return $this->display(__FILE__, 'views/templates/hook/order-confirmation_hidden_nosto-elements.tpl');
        } else {
            // If the current page is not one of the ones we want to show recommendations on, just return empty.
            return '';
        }
    }

    /**
     * Checks if the given controller is the current one.
     *
     * @param string $name the controller name
     * @return bool true if the given name is the same as the controllers php_self variable, false otherwise.
     */
    protected function isController($name)
    {
        $result = false;
        // For prestashop 1.5 and 1.6 we can in most cases access the current controllers php_self property.
        if (!empty($this->context->controller->php_self)) {
            $result = $this->context->controller->php_self === $name;
        } elseif (($controller = Tools::getValue('controller')) !== false) {
            $result = $controller === $name;
        }

        // Fallback when controller cannot be recognised.
        return $result;
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
        $success = true;
        if (!empty(self::$custom_hooks)) {
            foreach (self::$custom_hooks as $hook) {
                $callback = array('Hook', (method_exists('Hook', 'getIdByName')) ? 'getIdByName' : 'get');
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
     * Render meta-data (tagging) for the logged in customer.
     *
     * @return string The rendered HTML
     */
    protected function getCustomerTagging()
    {
        $nosto_customer = new NostoTaggingCustomer();
        if (!$nosto_customer->isCustomerLoggedIn($this->context->customer)) {
            return '';
        }

        $nosto_customer->loadData($this->context->customer);

        $this->getSmarty()->assign(array(
            'nosto_customer' => $nosto_customer,
            'nosto_hcid' => self::getVisitorChecksum()
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
        $nosto_cart = new NostoTaggingCart();
        $nosto_cart->loadData($this->context->cart);

        $this->getSmarty()->assign(array(
            'nosto_cart' => $nosto_cart,
            'nosto_hcid' => self::getVisitorChecksum()
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
        $nosto_product = new NostoTaggingProduct();
        $nosto_product->loadData($this->context, $product);

        $params = array('nosto_product' => $nosto_product);

        if (Validate::isLoadedObject($category)) {
            $nosto_category = new NostoTaggingCategory();
            $nosto_category->loadData($this->context, $category);
            $params['nosto_category'] = $nosto_category;
        }

        $this->getSmarty()->assign($params);
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
        $nosto_order->loadData($this->context, $order);

        $this->getSmarty()->assign(array(
            'nosto_order' => $nosto_order,
        ));

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
        $nosto_category->loadData($this->context, $category);

        $this->getSmarty()->assign(array(
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
        $nosto_brand = new NostoTaggingBrand();
        $nosto_brand->loadData($manufacturer);

        $this->getSmarty()->assign(array(
            'nosto_brand' => $nosto_brand,
        ));

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
        $nosto_search->setSearchTerm($search_term);

        $this->getSmarty()->assign(array(
            'nosto_search' => $nosto_search,
        ));

        return $this->display(__FILE__, 'views/templates/hook/top_search-tagging.tpl');
    }

    /**
     * Method for resolving correct smarty object
     *
     * @return Smarty|Smarty_Data
     * @throws NostoException
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

        throw new NostoException('Could not find smarty');
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
        /* @var $currencyHelper NostoTaggingHelperCurrency */
        $currencyHelper = Nosto::helper('nosto_tagging/currency');
        /** @var NostoTaggingHelperConfig $helper_config */
        $helper_config = Nosto::helper('nosto_tagging/config');
        $id_lang = $this->context->language->id;
        $id_shop = null;
        $id_shop_group = null;
        if ($this->context->shop instanceof Shop) {
            $id_shop = $this->context->shop->id;
            $id_shop_group = $this->context->shop->id_shop_group;
        }
        if ($helper_config->useMultipleCurrencies($id_lang, $id_shop_group, $id_shop)) {
            $defaultVariationId = $currencyHelper->getActiveCurrency($this->context);
            $priceVariation = new NostoTaggingPriceVariation($defaultVariationId);
            $this->getSmarty()->assign(array('nosto_price_variation' => $priceVariation));

            return $this->display(__FILE__, 'views/templates/hook/top_price_variation-tagging.tpl');
        }

        return '';
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
        if (empty($token)) {
            // Running bin2hex() will make the string length 32 characters.
            $token = bin2hex(NostoCryptRandom::getRandomString(16));
            $helper_config->saveCronAccessToken($token);
        }
        return $token;
    }

    /**
     * Updates the exchange rates to Nosto when user logs in or logs out
     *
     * @param array $params
     */
    public function hookDisplayBackOfficeTop(/** @noinspection PhpUnusedParameterInspection */
        array $params
    ) {
        //Do not render any thing when it is a ajax request
        if (array_key_exists(self::AJAX_REQUEST_PAREMETER_KEY, $_REQUEST)
            && $_REQUEST[self::AJAX_REQUEST_PAREMETER_KEY] == 1
        ) {
            return;
        }

        $this->checkNotifications();
    }

    /**
     * @param array $params
     */
    public function hookBackOfficeFooter(/** @noinspection PhpUnusedParameterInspection */
        array $params
    ) {
        return $this->updateExhangeRatesIfNeeded(false);
    }
    /**
     * Defines exhange rates updated for current session
     */
    public function defineExchangeRatesAsUpdated()
    {
        if ($this->getContext()->cookie && $this->adminLoggedIn()) {
            $this->getContext()->cookie->nostoExchangeRatesUpdated = true;
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

        $cookie = $this->getContext()->cookie;
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
     *
     * @param array $params
     */
    public function hookActionObjectCurrencyUpdateAfter(/** @noinspection PhpUnusedParameterInspection */
        array $params
    ) {
        return $this->updateExhangeRatesIfNeeded(true);
    }

    /**
     * Updates the exchange rates to Nosto if needed
     *
     * @param boolean $force if set to true cookie check is ignored
     * @internal param array $params
     */
    public function updateExhangeRatesIfNeeded($force = false)
    {
        if ($this->exchangeRatesShouldBeUpdated() || $force === true) {
            $this->defineExchangeRatesAsUpdated(); // This ensures we only try this at once
            /** @var NostoTaggingHelperCurrency $currency_helper */
            $currency_helper = Nosto::helper('nosto_tagging/currency');
            try {
                $currency_helper->updateExchangeRatesForAllStores();
                $this->defineExchangeRatesAsUpdated();
            } catch (NostoException $e) {
                /* @var NostoTaggingHelperLogger $logger */
                $logger = Nosto::helper('nosto_tagging/logger');
                $logger->error(
                    'Exchange rate sync failed with error: %s',
                    $e->getMessage()
                );
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
     * Override method.
     * Check smarty before calling Module.display()
     *
     * @param string $file
     * @param string $template
     * @param string|null $cache_id
     * @param string|null $compile_id
     * @return
     */
    public function display($file, $template, $cache_id = null, $compile_id = null)
    {
        if ($this->smarty == null) {
            return null;
        }

        return parent::display($file, $template, $cache_id, $compile_id);
    }

    /**
     * Render page type tagging
     *
     * @param string $page_type
     * @return string the rendered HTML
     */
    protected function getPageTypeTagging($page_type)
    {
        $this->getSmarty()->assign(array(
            'nosto_page_type' => $page_type,
        ));

        return $this->display(__FILE__, 'views/templates/hook/top_page_type-tagging.tpl');
    }

    /**
     * Checks all Nosto notifications and adds them as an admin notification
     */
    public function checkNotifications()
    {
        /* @var NostoTaggingHelperNotification $helper_notification */
        $helper_notification = Nosto::helper('nosto_tagging/notification');
        $notifications = $helper_notification->getAll();
        if (is_array($notifications) && count($notifications)>0) {
            /* @var NostoTaggingAdminNotification $notification */
            foreach ($notifications as $notification) {
                if (
                    $notification->getNotificationType() === NostoNotificationInterface::TYPE_MISSING_INSTALLATION
                    && !$this->isController('AdminModules')
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
     * @param NostoTaggingAdminNotification $notification
     */
    protected function addPrestashopNotification(NostoTaggingAdminNotification $notification)
    {
        switch ($notification->getNotificationSeverity()) {
            case NostoNotificationInterface::SEVERITY_INFO:
                $this->adminDisplayInformation($notification->getFormattedMessage());
                break;
            case NostoNotificationInterface::SEVERITY_WARNING:
                $this->adminDisplayWarning($notification->getFormattedMessage());
                break;
            default:
        }
    }

    public static function readNostoCookie()
    {
        // We use the $GLOBALS here, instead of the Prestashop cookie class, as we are accessing a
        // nosto cookie that have been set by the JavaScript loaded from nosto.com. Accessing global $_COOKIE array
        // is not allowed by Prestashop's new validation rules effective from April 2016.
        // We read it to keep a mapping of the Nosto user ID and the Prestashop user ID so we can identify which user
        // actually completed an order. We do this for tracking whether or not to send abandoned cart emails.
        if ($GLOBALS[self::GLOBAL_COOKIES] && isset($GLOBALS[self::GLOBAL_COOKIES][self::COOKIE_NAME])) {
            return $GLOBALS[self::GLOBAL_COOKIES][self::COOKIE_NAME];
        } else {
            return null;
        }
    }

    /**
     * Return the checksum for visitor
     *
     * @return string
     */
    public function getVisitorChecksum()
    {
        $coo = self::readNostoCookie();
        if ($coo) {
            return hash(self::VISITOR_HASH_ALGO, $coo);
        }
        return null;
    }

    /**
     * Returns link class initialized with https or http
     *
     * @return Link
     */
    public static function buildLinkClass()
    {
        if (Configuration::get('PS_SSL_ENABLED_EVERYWHERE')) {
            $link = new Link('https://', 'https://');
        } else {
            $link = new Link('http://', 'http://');
        }

        return $link;
    }
}
