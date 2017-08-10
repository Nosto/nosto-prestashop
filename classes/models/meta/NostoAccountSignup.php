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

use \Nosto\Object\Signup\Signup as NostoSDKAccountSignup;

class NostoAccountSignup extends NostoSDKAccountSignup
{
    /**
     * @var string the API token used to identify an account creation.
     */
    const TOKEN = 'JRtgvoZLMl4NPqO9XWhRdvxkTMtN82ITTJij8U7necieJPCvjtZjm5C4fpNrYJ81';

    /**
     * NostoAccountSignup constructor.
     */
    public function __construct()
    {
        parent::__construct('prestashop', NostoAccountSignup::TOKEN, null);
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
     * @return NostoAccountSignup|null
     */
    public static function loadData($context, $id_lang)
    {
        $signup = new NostoAccountSignup();

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
        $idShop = null;
        $idShopGroup = null;
        if ($context->shop instanceof Shop) {
            $idShop = $context->shop->id;
            $idShopGroup = $context->shop->id_shop_group;
        }
        $signup->setTitle(Configuration::get('PS_SHOP_NAME'));
        $signup->setName(Tools::substr(sha1((string)rand()), 0, 8));
        $signup->setFrontPageUrl(self::getContextShopUrl($context, $language));
        $signup->setCurrencyCode($context->currency->iso_code);
        $signup->setLanguageCode($context->language->iso_code);
        $signup->setOwnerLanguageCode($language->iso_code);
        $signup->setOwner(NostoAccountOwner::loadData($context));
        $signup->setBillingDetails(NostoAccountBilling::loadData($context));
        $signup->setCurrencies(self::buildCurrencies($context));
        if (NostoHelperConfig::useMultipleCurrencies($id_lang, $idShopGroup, $idShop)) {
            $signup->setUseCurrencyExchangeRates(
                NostoHelperConfig::useMultipleCurrencies(
                    $id_lang,
                    $idShopGroup,
                    $idShop
                )
            );
            $signup->setDefaultVariantId(NostoHelperCurrency::getBaseCurrency($context)->iso_code);
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
        $currencies = NostoHelperCurrency::getCurrencies($context, true);
        foreach ($currencies as $currency) {
            $nosto_currency = NostoHelperCurrency::getNostoCurrency($currency, $context);
            $nosto_currencies[$currency['iso_code']] = $nosto_currency;
        }

        return $nosto_currencies;
    }
}
