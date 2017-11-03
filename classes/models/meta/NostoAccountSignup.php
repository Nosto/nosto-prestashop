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
    const PS_LANG_DEFAULT = 'PS_LANG_DEFAULT';
    const PS_COUNTRY_DEFAULT = 'PS_COUNTRY_DEFAULT';
    const PS_SHOP_NAME = 'PS_SHOP_NAME';

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
     * @return int language id
     * @suppress PhanTypeMismatchArgument
     */
    private static function loadLanguageId()
    {
        return (int)Configuration::get(self::PS_LANG_DEFAULT);
    }

    /**
     * @return int Currency id
     * @suppress PhanTypeMismatchArgument
     */
    private static function loadCurrencyId()
    {
        return (int)Configuration::get(NostoHelperCurrency::PS_CURRENCY_DEFAULT);
    }

    /**
     * @return int Country id
     * @suppress PhanTypeMismatchArgument
     */
    private static function loadCountryId()
    {
        return (int)Configuration::get(self::PS_COUNTRY_DEFAULT);
    }

    /**
     * Loads the meta data for the context and given language.
     *
     * @return NostoAccountSignup|null the signup object
     */
    public static function loadData()
    {
        $nostoSignup = new NostoAccountSignup();

        $language = NostoHelperContext::getLanguage();
        if (!Validate::isLoadedObject($language)) {
            return null;
        }

        $languageId = NostoHelperContext::getLanguageId();
        $currencyId = NostoHelperContext::getCurrencyId();
        $countryId = NostoHelperContext::getCountryId();
        if (!Validate::isLoadedObject(NostoHelperContext::getLanguage())) {
            $languageId = self::loadLanguageId();
        }
        if (!Validate::isLoadedObject(NostoHelperContext::getCurrency())) {
            $currencyId = self::loadCurrencyId();
        }
        if (!Validate::isLoadedObject(NostoHelperContext::getCountry())) {
            $countryId = self::loadCountryId();
        }

        NostoHelperContext::runInContext(
            function () use (&$nostoSignup) {
                $nostoSignup->setTitle(Configuration::get(NostoAccountSignup::PS_SHOP_NAME));
                $nostoSignup->setName(Tools::substr(sha1((string)rand()), 0, 8));
                $nostoSignup->setFrontPageUrl(NostoHelperUrl::getContextShopUrl());
                $nostoSignup->setCurrencyCode(NostoHelperContext::getCurrency()->iso_code);
                $nostoSignup->setLanguageCode(NostoHelperContext::getLanguage()->iso_code);
                $nostoSignup->setOwnerLanguageCode(NostoHelperContext::getLanguage()->iso_code);
                $nostoSignup->setOwner(NostoAccountOwner::loadData());
                $nostoSignup->setBillingDetails(NostoAccountBilling::loadData());
                $nostoSignup->setCurrencies(self::buildCurrencies());
                if (NostoHelperConfig::useMultipleCurrencies()) {
                    $nostoSignup->setUseCurrencyExchangeRates(true);
                    $nostoSignup->setDefaultVariantId(NostoHelperCurrency::getBaseCurrency()->iso_code);
                } elseif (NostoHelperConfig::getVariationEnabled()) {
                    $nostoSignup->setUseCurrencyExchangeRates(true);
                    $keyCollection = new NostoVariationKeyCollection();
                    $keyCollection->loadData();
                    $nostoSignup->setDefaultVariantId(
                        $keyCollection->getDefaultVariationKey()->getVariationId()
                    );
                }

                NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoSignup), array(
                    'nosto_account_signup' => $nostoSignup
                ));
            },
            (int)$languageId,
            false,
            (int)$currencyId,
            false,
            (int)$countryId
        );

        return $nostoSignup;
    }

    protected static function buildCurrencies()
    {
        $nostoCurrencies = array();
        $currencies = NostoHelperCurrency::getCurrencies(true);
        foreach ($currencies as $currency) {
            $nostoCurrency = NostoHelperCurrency::getNostoCurrency($currency);
            $nostoCurrencies[$currency['iso_code']] = $nostoCurrency;
        }

        return $nostoCurrencies;
    }
}
