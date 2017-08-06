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
class NostoTaggingMetaAccount extends \Nosto\Object\Signup\Signup
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
     * @return Language
     * @suppress PhanTypeMismatchArgument
     */
    private static function loadLanguage()
    {
        return new Language((int)Configuration::get('PS_LANG_DEFAULT'));
    }

    /**
     * @return Currency
     * @suppress PhanTypeMismatchArgument
     */
    private static function loadCurrency()
    {
        return new Currency((int)Configuration::get('PS_CURRENCY_DEFAULT'));
    }

    /**
     * @return Country
     * @suppress PhanTypeMismatchArgument
     */
    private static function loadCountry()
    {
        return new Country((int)Configuration::get('PS_COUNTRY_DEFAULT'));
    }

    /**
     * Loads the meta data for the context and given language.
     *
     * @param Context $context the context to use as data source.
     * @param int $id_lang the language to use as data source.
     * @return NostoTaggingMetaAccount|null
     */
    public static function loadData($context, $id_lang)
    {
        $signup = new NostoTaggingMetaAccount();
        /** @var NostoTaggingHelperCurrency $currency_helper */
        $currency_helper = Nosto::helper('nosto_tagging/currency');
        /** @var NostoTaggingHelperConfig $config_helper */
        $config_helper = Nosto::helper('nosto_tagging/config');

        $language = new Language($id_lang);
        if (!Validate::isLoadedObject($language)) {
            return null;
        }

        if (!Validate::isLoadedObject($context->language)) {
            $context->language = self::loadLanguage();
        }
        if (!Validate::isLoadedObject($context->currency)) {
            $context->currency = self::loadCurrency();
        }
        if (!Validate::isLoadedObject($context->country)) {
            $context->country = self::loadCountry();
        }
        $id_shop = null;
        $id_shop_group = null;
        if ($context->shop instanceof Shop) {
            $id_shop = $context->shop->id;
            $id_shop_group = $context->shop->id_shop_group;
        }
        $signup->setTitle(Configuration::get('PS_SHOP_NAME'));
        $signup->setName(Tools::substr(sha1((string)rand()), 0, 8));
        $signup->setFrontPageUrl(self::getContextShopUrl($context, $language));
        $signup->setCurrencyCode($context->currency->iso_code);
        $signup->setLanguageCode($context->language->iso_code);
        $signup->setOwnerLanguageCode($language->iso_code);
        $signup->setOwner(NostoTaggingMetaAccountOwner::loadData($context));
        $signup->setBillingDetails(NostoTaggingMetaAccountBilling::loadData($context));
        $signup->setCurrencies(self::buildCurrencies($context));
        if ($config_helper->useMultipleCurrencies($id_lang, $id_shop_group, $id_shop)) {
            $signup->setUseCurrencyExchangeRates(
                $config_helper->useMultipleCurrencies(
                    $id_lang,
                    $id_shop_group,
                    $id_shop
                )
            );
            $signup->setDefaultVariantId($currency_helper->getBaseCurrency($context)->iso_code);
        } else {
            $signup->setUseCurrencyExchangeRates(false);
        }
        return $signup;
    }

    /**
     * Returns the current shop's url from the context and language.
     *
     * @param Context $context the context.
     * @param Language $language the language.
     * @return string the absolute url.
     */
    protected static function getContextShopUrl($context, $language)
    {
        $shop = $context->shop;
        $ssl = Configuration::get('PS_SSL_ENABLED');
        $rewrite = (int)Configuration::get('PS_REWRITING_SETTINGS', null, null, $shop->id);
        $multi_lang = (Language::countActiveLanguages($shop->id) > 1);
        $base = ($ssl ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain) . $shop->getBaseURI();
        $lang = '';
        if ($multi_lang) {
            if ($rewrite) {
                $lang = $language->iso_code . '/';
            } else {
                $lang = '?id_lang=' . $language->id;
            }
        }
        return $base . $lang;
    }

    protected static function buildCurrencies(Context $context)
    {
        $nosto_currencies = array();
        /** @var NostoTaggingHelperCurrency $currency_helper */
        $currency_helper = Nosto::helper('nosto_tagging/currency');
        $currencies = $currency_helper->getCurrencies($context, true);
        foreach ($currencies as $currency) {
            $nosto_currency = $currency_helper->getNostoCurrency($currency, $context);
            $nosto_currencies[$currency['iso_code']] = $nosto_currency;
        }

        return $nosto_currencies;
    }
}
