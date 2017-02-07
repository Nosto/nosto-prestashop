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

/**
 * Meta data class for account iframe related information needed when showing the admin iframe on module settings page.
 */
class NostoTaggingMetaAccountIframe implements NostoAccountMetaDataIframeInterface
{
    /**
     * @var string the admin user first name.
     */
    protected $first_name;

    /**
     * @var string the admin user last name.
     */
    protected $last_name;

    /**
     * @var    string the admin user email address.
     */
    protected $email;

    /**
     * @var string the language ISO (ISO 639-1) code for oauth server locale.
     */
    protected $language_iso_code;

    /**
     * @var string the language ISO (ISO 639-1) for the store view scope.
     */
    protected $language_iso_code_shop;

    /**
     * @var string unique ID that identifies the Magento installation.
     */
    protected $unique_id;

    /**
     * @var string the version number of the Nosto module/extension running on the e-commerce installation.
     */
    protected $version_module;

    /**
     * @var string preview url for the product page in the active store scope.
     */
    protected $preview_url_product;

    /**
     * @var string preview url for the category page in the active store scope.
     */
    protected $preview_url_category;

    /**
     * @var string preview url for the search page in the active store scope.
     */
    protected $preview_url_search;

    /**
     * @var string preview url for the cart page in the active store scope.
     */
    protected $preview_url_cart;

    /**
     * @var string preview url for the front page in the active store scope.
     */
    protected $preview_url_front;

    /**
     * @var string the name of the shop context where nosto is installed or is about to be installed.
     */
    protected $shop_name;

    /**
     * @var array installed modules
     */
    protected $modules;

    /**
     * Loads the meta-data from context.
     *
     * @param Context $context the context to get the meta-data from.
     * @param int $id_lang the language ID of the shop for which to get the meta-data.
     */
    public function loadData($context, $id_lang)
    {
        $shop_language = new Language($id_lang);
        $shop_context = $context->shop->getContext();
        if (
            !Validate::isLoadedObject($shop_language)
            || $shop_context !== Shop::CONTEXT_SHOP
        ) {
            return;
        }

        /** @var NostoTaggingHelperUrl $url_helper */
        $url_helper = Nosto::helper('nosto_tagging/url');

        $this->first_name = $context->employee->firstname;
        $this->last_name = $context->employee->lastname;
        $this->email = $context->employee->email;
        $this->language_iso_code = $context->language->iso_code;
        $this->language_iso_code_shop = $shop_language->iso_code;
        $this->preview_url_product = $url_helper->getPreviewUrlProduct(null, $id_lang);
        $this->preview_url_category = $url_helper->getPreviewUrlCategory(null, $id_lang);
        $this->preview_url_search = $url_helper->getPreviewUrlSearch($id_lang);
        $this->preview_url_cart = $url_helper->getPreviewUrlCart($id_lang);
        $this->preview_url_front = $url_helper->getPreviewUrlHome($id_lang);
        $this->shop_name = $shop_language->name;
        $this->version_module = NostoTagging::PLUGIN_VERSION;
    }

    /**
     * The name of the platform the iframe is used on.
     * A list of valid platform names is issued by Nosto.
     *
     * @return string the platform name.
     */
    public function getPlatform()
    {
        return 'prestashop';
    }

    /**
     * Sets the first name of the admin user.
     *
     * @param string $first_name the first name.
     */
    public function setFirstName($first_name)
    {
        $this->first_name = $first_name;
    }

    /**
     * The first name of the user who is loading the config iframe.
     *
     * @return string the first name.
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * Sets the last name of the admin user.
     *
     * @param string $last_name the last name.
     */
    public function setLastName($last_name)
    {
        $this->last_name = $last_name;
    }

    /**
     * The last name of the user who is loading the config iframe.
     *
     * @return string the last name.
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * Sets the email address of the admin user.
     *
     * @param string $email the email address.
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * The email address of the user who is loading the config iframe.
     *
     * @return string the email address.
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Sets the language ISO code.
     *
     * @param string $code the ISO code.
     */
    public function setLanguageIsoCode($code)
    {
        $this->language_iso_code = $code;
    }

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the user who is
     * loading the config iframe.
     *
     * @return string the language ISO code.
     */
    public function getLanguageIsoCode()
    {
        return $this->language_iso_code;
    }

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the shop the
     * account belongs to.
     *
     * @return string the language ISO code.
     */
    public function getLanguageIsoCodeShop()
    {
        return $this->language_iso_code_shop;
    }

    /**
     * Sets the unique identifier for the e-commerce installation.
     * This identifier is used to link accounts together that are created on
     * the same installation.
     *
     * @param string $unique_id the unique ID.
     */
    public function setUniqueId($unique_id)
    {
        $this->unique_id = $unique_id;
    }

    /**
     * Unique identifier for the e-commerce installation.
     * This identifier is used to link accounts together that are created on
     * the same installation.
     *
     * @return string the identifier.
     */
    public function getUniqueId()
    {
        return $this->unique_id;
    }

    /**
     * The version number of the platform the e-commerce installation is
     * running on.
     *
     * @return string the platform version.
     */
    public function getVersionPlatform()
    {
        return _PS_VERSION_;
    }

    /**
     * Sets the version number of the Nosto module/extension running on the
     * e-commerce installation.
     *
     * @param string $version the version number.
     */
    public function setVersionModule($version)
    {
        $this->version_module = $version;
    }

    /**
     * The version number of the Nosto module/extension running on the
     * e-commerce installation.
     *
     * @return string the module version.
     */
    public function getVersionModule()
    {
        return $this->version_module;
    }

    /**
     * An absolute URL for any product page in the shop the account is linked
     * to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/products/product123?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview
     * the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlProduct()
    {
        return $this->preview_url_product;
    }

    /**
     * An absolute URL for any category page in the shop the account is linked
     * to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/products/category123?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview
     * the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlCategory()
    {
        return $this->preview_url_category;
    }

    /**
     * An absolute URL for the search page in the shop the account is linked
     * to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/search?query=red?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview
     * the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlSearch()
    {
        return $this->preview_url_search;
    }

    /**
     * An absolute URL for the shopping cart page in the shop the account is
     * linked to, with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com/cart?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview
     * the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlCart()
    {
        return $this->preview_url_cart;
    }

    /**
     * An absolute URL for the front page in the shop the account is linked to,
     * with the nostodebug GET parameter enabled.
     * e.g. http://myshop.com?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview
     * the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlFront()
    {
        return $this->preview_url_front;
    }

    /**
     * Returns the name of the shop context where nosto is installed or about to be installed.
     *
     * @return string the name.
     */
    public function getShopName()
    {
        return $this->shop_name;
    }

    /**
     * @inheritdoc
     */
    public function getModules()
    {
        return $this->modules;
    }

    /**
     * Setter for the modules
     *
     * @param array $modules
     */
    public function setModules(array $modules)
    {
        $this->modules = $modules;
    }
}
