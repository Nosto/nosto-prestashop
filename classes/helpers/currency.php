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
 * Helper class for currency related tasks.
 */
class NostoTaggingHelperCurrency
{
    /**
     * Fetches the base currency from the context.
     *
     * @param Context|ContextCore $context the context.
     * @return Currency
     *
     * @throws NostoException if the currency cannot be found, we require it.
     */
    public function getBaseCurrency(Context $context)
    {
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        if (isset($context->shop->id_shop_group)) {
            $id_shop_group = $context->shop->id_shop_group;
        } else {
            $id_shop_group = null;
        }

        $base_id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT', $id_lang, $id_shop_group, $id_shop);
        if ($base_id_currency === 0) {
            $base_id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT', null, $id_shop_group, $id_shop);
        }
        $base_currency = new Currency($base_id_currency);
        if (!Validate::isLoadedObject($base_currency)) {
            throw new NostoException(
                sprintf(
                    'Failed to find base currency for shop #%s and lang #%s.',
                    $id_shop,
                    $id_lang
                )
            );
        }

        return $base_currency;
    }

    /**
     * Fetches all currencies defined in context.
     *
     * @param Context|ContextCore $context the context.
     * @return array the found currencies.
     */
    public function getCurrencies(Context $context)
    {
        $id_shop = (int)$context->shop->id;
        if (_PS_VERSION_ >= '1.5') {
            return Currency::getCurrenciesByIdShop($id_shop);
        } else {
            return Currency::getCurrencies();
        }
    }

    /**
     * Parses a PS currency into a Nosto currency.
     *
     * @param array $currency the PS currency data.
     * @return NostoCurrency the nosto currency.
     *
     * @throws NostoException if currency cannot be parsed.
     */
    public function getNostoCurrency(array $currency)
    {
        switch ($currency['format']) {
            /* X 0,000.00 */
            case 1:
                $group_symbol = ',';
                $decimal_symbol = '.';
                $group_length = 3;
                $precision = 2;
                $symbol_position = NostoCurrencySymbol::SYMBOL_POS_LEFT;
                break;
            /* 0 000,00 X*/
            case 2:
                $group_symbol = ' ';
                $decimal_symbol = ',';
                $group_length = 3;
                $precision = 2;
                $symbol_position = NostoCurrencySymbol::SYMBOL_POS_RIGHT;
                break;
            /* X 0.000,00 */
            case 3:
                $group_symbol = '.';
                $decimal_symbol = ',';
                $group_length = 3;
                $precision = 2;
                $symbol_position = NostoCurrencySymbol::SYMBOL_POS_LEFT;
                break;
            /* 0,000.00 X */
            case 4:
                $group_symbol = ',';
                $decimal_symbol = '.';
                $group_length = 3;
                $precision = 2;
                $symbol_position = NostoCurrencySymbol::SYMBOL_POS_RIGHT;
                break;
            /* X 0'000.00 */
            case 5:
                $group_symbol = '\'';
                $decimal_symbol = '.';
                $group_length = 3;
                $precision = 2;
                $symbol_position = NostoCurrencySymbol::SYMBOL_POS_LEFT;
                break;

            default:
                throw new NostoException(sprintf('Unsupported PrestaShop currency format %d.', $currency['format']));
        }

        return new NostoCurrency(
            new NostoCurrencyCode($currency['iso_code']),
            new NostoCurrencySymbol($currency['sign'], $symbol_position),
            new NostoCurrencyFormat($group_symbol, $group_length, $decimal_symbol, $precision)
        );
    }

    /**
     * Returns a collection of all currency exchange rates for the context.
     *
     * @param Context $context the context.
     * @return NostoTaggingCollectionExchangeRates
     */
    public function getExchangeRateCollection(Context $context)
    {
        $base_currency_code = $this->getBaseCurrency($context)->iso_code;
        $currencies = $this->getCurrencies($context);
        $exchange_rates = array();
        foreach ($currencies as $currency) {
            // Skip base currencyCode.
            if (
                $currency['iso_code'] === $base_currency_code
                || $currency['deleted'] == 1
            ) {
                continue;
            }

            $exchange_rates[] = new NostoExchangeRate(
                $currency['iso_code'],
                $currency['iso_code'],
                $currency['conversion_rate']
            );
        }

        $rates = new NostoTaggingCollectionExchangeRates($exchange_rates);
        return $rates;
    }


    /**
     * @param Context $context
     * @return string Currency code in ISO 4217
     */
    public function getActiveCurrency(Context $context)
    {
        return $context->currency->iso_code;
    }

    /**
     * Returns a collection of countries that have tax rules
     *
     * @param Context $context_clone the context.
     * @return NostoTaggingCollectionExchangeRates
     */
    public function getTaxRulesExchangeRateCollection(Context $context)
    {
        /** @var NostoTaggingHelperPrice $helper_price */
        $helper_price = Nosto::helper('nosto_tagging/price');
        /** @var NostoTaggingHelperProduct $helper_product */
        $helper_product = Nosto::helper('nosto_tagging/product');

        $context_clone = clone $context;
        $product = $helper_product->getSingleActiveProduct($context_clone);

        $tax_rule_countries = $this->getCountriesWithTaxRules($context_clone);
        $rates_array = array();
        $base_price = $helper_price->getProductPriceInclTax(
            $product,
            $context_clone,
            $this->getBaseCurrency($context_clone)
        );

        // We would need to calculate these for all currencies
        // Try to come up with a list "EUR_AU", "EUR_FI", "EUR_DE"
        $currencies = $this->getCurrencies($context_clone);
        foreach ($tax_rule_countries as $country_id => $country) {
            foreach ($currencies as $currency_arr) {
                if ($currency_arr['deleted'] == 1) {
                    continue;
                }
                $currency = new Currency($currency_arr['id_currency']);
                //var_dump($currency); die;
                /* @var ContextCore $context_clone */
                $context_clone->country = $country;
                $context_clone->customer->geoloc_id_country = $country->id;
                $context_clone->currency = $currency;
                $price = $helper_price->getProductListPriceInclTax(
                    $product,
                    $context_clone,
                    $currency
                );
                $exchange_rate_name = $this->getGeneratedVariationId($context_clone);
                $exchange_rate = round($price/$base_price, 4);
                $rates_array[] = new NostoExchangeRate(
                    $exchange_rate_name,
                    $context_clone->currency->iso_code,
                    $exchange_rate
                );
            }
        }
        $normal_exchange_rates = $this->getExchangeRateCollection($context);
        /* @var NostoExchangeRate $nosto_exchange_rate */
        foreach ($normal_exchange_rates as $nosto_exchange_rate) {
            $rates_array[] = $nosto_exchange_rate;
        }

        $ratest_collection = new NostoTaggingCollectionExchangeRates($rates_array);
        return $ratest_collection;
    }

    /**
     * Generates the variation id based on country and language
     *
     * @param Context $context
     * @return string
     */
    public function getGeneratedVariationId(Context $context)
    {
        $countriesWithTaxRules = $this->getCountriesWithTaxRules($context);

        if (array_key_exists($context->country->id, $countriesWithTaxRules)) {
            $variationId = sprintf(
                '%s_%s',
                $context->country->iso_code,
                $context->currency->iso_code
            );
        } else {
            $variationId = $context->currency->iso_code;
        }

        return $variationId;
    }

    /**
     * @param Context $context
     * @return array
     */
    public function getCountriesWithTaxRules(Context $context)
    {
        $res = array();
        $id_lang = $context->language->id;
        $taxRuleGroups = $this->getTaxGroupsInUse($context);
        // We can only handle single tax group with exhchange rate multiplier
        /* @var TaxRulesGroup $taxRuleGroup */
        foreach ($taxRuleGroups as $taxRuleGroupId) {
            $taxRuleGroup = new TaxRulesGroup($taxRuleGroupId);
            $countries = TaxRule::getTaxRulesByGroupId($id_lang, $taxRuleGroup->id);
            foreach ($countries as $country) {
                $country_id = $country['id_country'];
                if (!isset($res[$country_id])) {
                    $res[$country_id] = new Country($country_id);
                }
            }
        }
        return $res;
    }
    /**
     * Returns an array of tax rule groups that are assigned to any product
     *
     * @param Context $context the context.
     * @return array
     */
    public function getTaxGroupsInUse(Context $context)
    {
        $res = array();
        $sql = sprintf(
            '
                SELECT 
                    p.id_tax_rules_group AS id
                FROM 
                    %sproduct p
                INNER JOIN
                    %stax_rules_group trg ON (p.id_tax_rules_group = trg.id_tax_rules_group)
                WHERE
                    trg.active = 1
                GROUP BY 
                    p.id_tax_rules_group;
           ',
            _DB_PREFIX_,
            _DB_PREFIX_
        );

        $rows = Db::getInstance()->executeS($sql);
        foreach ($rows as $row) {
            $res[] = $row['id'];
        }
        return $res;
    }

    /**
     * Updates exchange rates for all stores and Nosto accounts
     *
     * @throws NostoException
     * @return void
     */
    public function updateExchangeRatesForAllStores()
    {
        /** @var NostoTaggingHelperAccount $helper_account */
        $helper_account = Nosto::helper('nosto_tagging/account');
        /** @var NostoTaggingHelperContextFactory $context_factory */
        $context_factory = Nosto::helper('nosto_tagging/context_factory');
        /** @var NostoTaggingHelperConfig $helper_config*/
        $helper_config = Nosto::helper('nosto_tagging/config');

        foreach (Shop::getShops() as $shop) {
            $id_shop = isset($shop['id_shop']) ? (int)$shop['id_shop'] : null;
            $id_shop_group = isset($shop['id_shop_group']) ? (int)$shop['id_shop_group'] : null;
            foreach (Language::getLanguages(true, $id_shop) as $language) {
                $id_lang = (int)$language['id_lang'];
                $use_multiple_currencies = $helper_config->useMultipleCurrencies($id_lang);
                if ($use_multiple_currencies) {
                    $nosto_account = $helper_account->find($id_lang, $id_shop_group, $id_shop);
                    if (!is_null($nosto_account)) {
                        $context = $context_factory->forgeContext($id_lang, $id_shop);
                        if (!$helper_account->updateCurrencyExchangeRates(
                            $nosto_account,
                            $context
                        )
                        ) {
                            throw new NostoException(
                                sprintf(
                                    'Exchange rate update failed for %s',
                                    $nosto_account->getName()
                                )
                            );
                        }
                        $context_factory->revertToOriginalContext();
                    }
                }
            }
        }
    }

    /**
     * Returns the exchange rates and possible variations used in this context
     *
     * @param Context $context
     * @return NostoTaggingCollectionExchangeRates
     */
    public function getExchangeRatesInUse(Context $context)
    {
        /** @var NostoTaggingHelperConfig $config_helper */
        $config_helper = Nosto::helper('nosto_tagging/config');
        $multi_currency_method = $config_helper->getMultiCurrencyMethod($context->language->id);
        if ($multi_currency_method === NostoTaggingHelperConfig::MULTI_CURRENCY_METHOD_TAX_RULES_EXCHANGE_RATE) {
            $exchange_rates = $this->getTaxRulesExchangeRateCollection($context);
        } else {
            $exchange_rates = $this->getExchangeRateCollection($context);
        }

        return $exchange_rates;
    }
}
