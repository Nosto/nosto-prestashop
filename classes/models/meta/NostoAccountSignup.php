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

use Nosto\Object\Signup\Signup as NostoSDKAccountSignup;

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
     * @param Context $context the context
     * @param int $id_lang the language to use as data source.
     * @return NostoAccountSignup|null the signup object
     */
    public static function loadData(Context $context, $id_lang)
    {
        $nostoSignup = new NostoAccountSignup();

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
        $nostoSignup->setTitle(Configuration::get('PS_SHOP_NAME'));
        $nostoSignup->setName(Tools::substr(sha1((string)rand()), 0, 8));
        $nostoSignup->setFrontPageUrl(self::getContextShopUrl($context, $language));
        $nostoSignup->setCurrencyCode($context->currency->iso_code);
        $nostoSignup->setLanguageCode($context->language->iso_code);
        $nostoSignup->setOwnerLanguageCode($language->iso_code);
        $nostoSignup->setOwner(NostoAccountOwner::loadData($context));
        $nostoSignup->setBillingDetails(NostoAccountBilling::loadData($context));
        $nostoSignup->setCurrencies(self::buildCurrencies($context));
        if (Nosto::useMultipleCurrencies($id_lang)) {
            $nostoSignup->setUseCurrencyExchangeRates(Nosto::useMultipleCurrencies($id_lang));
            $nostoSignup->setDefaultVariantId(NostoHelperCurrency::getBaseCurrency()->iso_code);
        }

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoSignup), array(
            'nosto_account_signup' => $nostoSignup
        ));
        return $nostoSignup;
    }

    /**
     * Returns the current shop's url from the context and language.
     *
     * @param Context $context the context.
     * @param Language $language the language.
     * @return string the absolute url.
     */
    protected static function getContextShopUrl($context, $language) //TODO: Why is this not in the helper?
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
        $currencies = NostoHelperCurrency::getCurrencies(true);
        foreach ($currencies as $currency) {
            $nosto_currency = NostoHelperCurrency::getNostoCurrency($currency, $context);
            $nosto_currencies[$currency['iso_code']] = $nosto_currency;
        }

        return $nosto_currencies;
    }
}
