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
 * Meta data class for account related information needed when creating new accounts.
 */
class NostoTaggingMetaAccount extends NostoAccountMeta
{
    /**
     * @var string the API token used to identify an account creation.
     */
    protected $sign_up_api_token = 'JRtgvoZLMl4NPqO9XWhRdvxkTMtN82ITTJij8U7necieJPCvjtZjm5C4fpNrYJ81';

    /**
     * NostoTaggingMetaAccount constructor.
     */
    public function __construct()
    {
        parent::__construct('prestashop', $this->sign_up_api_token, null);
    }

    /**
     * Loads the meta data for the context and given language.
     *
     * @param Context $context the context to use as data source.
     * @param int $id_lang the language to use as data source.
     */
    public function loadData($context, $id_lang)
    {

        /** @var NostoTaggingHelperCurrency $currency_helper */
        $currency_helper = Nosto::helper('nosto_tagging/currency');
        /** @var NostoTaggingHelperConfig $config_helper */
        $config_helper = Nosto::helper('nosto_tagging/config');

        $language = new Language($id_lang);
        if (!Validate::isLoadedObject($language)) {
            return;
        }

        if (!Validate::isLoadedObject($context->language)) {
            $context->language = new Language((int)Configuration::get('PS_LANG_DEFAULT'));
        }
        if (!Validate::isLoadedObject($context->currency)) {
            $context->currency = new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
        }
        if (!Validate::isLoadedObject($context->country)) {
            $context->country = new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
        }
        $id_shop = null;
        $id_shop_group = null;
        if ($context->shop instanceof Shop) {
            $id_shop = $context->shop->id;
            $id_shop_group = $context->shop->id_shop_group;
        }
        $this->setTitle(Configuration::get('PS_SHOP_NAME'));
        $this->setName(Tools::substr(sha1(rand()), 0, 8));
        $this->setFrontPageUrl($this->getContextShopUrl($context, $language));
        $this->setCurrencyCode($context->currency->iso_code);
        $this->setLanguageCode($context->language->iso_code);
        $this->setOwnerLanguageCode($language->iso_code);
        $this->owner = new NostoTaggingMetaAccountOwner();
        $this->owner->loadData($context);
        $this->billing = new NostoTaggingMetaAccountBilling();
        $this->billing->loadData($context);
        $this->setCurrencies($this->buildCurrencies($context));
        if ($config_helper->useMultipleCurrencies($id_lang, $id_shop_group, $id_shop)) {
            $this->setUseCurrencyExchangeRates(
                $config_helper->useMultipleCurrencies(
                    $id_lang,
                    $id_shop_group,
                    $id_shop
                )
            );
            $this->setDefaultVariationId($currency_helper->getBaseCurrency($context)->iso_code);
        } else {
            $this->setUseCurrencyExchangeRates(false);
        }
    }

    /**
     * Returns the current shop's url from the context and language.
     *
     * @param Context $context the context.
     * @param Language $language the language.
     * @return string the absolute url.
     */
    protected function getContextShopUrl($context, $language)
    {
        $shop = $context->shop;
        $ssl = Configuration::get('PS_SSL_ENABLED');
        $rewrite = (int)Configuration::get('PS_REWRITING_SETTINGS', null, null, $shop->id);
        $multi_lang = (Language::countActiveLanguages($shop->id) > 1);
        $base = ($ssl ? 'https://'.$shop->domain_ssl : 'http://'.$shop->domain).$shop->getBaseURI();
        $lang = '';
        if ($multi_lang) {
            if ($rewrite) {
                $lang = $language->iso_code.'/';
            } else {
                $lang = '?id_lang='.$language->id;
            }
        }
        return $base.$lang;
    }

    protected function buildCurrencies(Context $context)
    {
        $nosto_currencies = array();
        /** @var NostoTaggingHelperCurrency $currency_helper */
        $currency_helper = Nosto::helper('nosto_tagging/currency');
        $currencies = $currency_helper->getCurrencies($context, true);
        foreach ($currencies as $currency) {
            $nosto_currency = $currency_helper->getNostoCurrency($currency, $context);
            $nosto_currencies[] = $nosto_currency;
        }

        return $nosto_currencies;
    }
}
