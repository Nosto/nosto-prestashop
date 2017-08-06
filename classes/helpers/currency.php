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
    const CURRENCY_SYMBOL_MARKER = '¤';
    const CURRENCY_GROUP_LENGTH = 3;
    const CURRENCY_PRECISION = 2;

    /**
     * @param $id
     * @return Currency
     * @suppress PhanTypeMismatchArgument
     */
    private static function loadCurrency($id)
    {
        return new Currency((string)$id);
    }

    /**
     * Fetches the base currency from the context.
     *
     * @param Context|ContextCore $context the context.
     * @return Currency
     * @throws \Nosto\NostoException
     */
    public static function getBaseCurrency(Context $context)
    {
        $id_lang = $context->language->id;
        $id_shop = $context->shop->id;
        if (isset($context->shop->id_shop_group)) {
            $id_shop_group = $context->shop->id_shop_group;
        } else {
            $id_shop_group = null;
        }

        $base_id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT', $id_lang, $id_shop_group,
            $id_shop);
        if ($base_id_currency === 0) {
            $base_id_currency = (int)Configuration::get('PS_CURRENCY_DEFAULT', null, $id_shop_group,
                $id_shop);
        }
        $base_currency = self::loadCurrency($base_id_currency);
        if (!Validate::isLoadedObject($base_currency)) {
            throw new Nosto\NostoException(
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
     * @param boolean $only_active if set to true, only active languages will be returned
     * @return array the found currencies.
     */
    public static function getCurrencies(Context $context, $only_active = false)
    {
        $id_shop = (int)$context->shop->id;
        $all_currencies = Currency::getCurrenciesByIdShop($id_shop);
        if ($only_active === true) {
            $currencies = array();
            foreach ($all_currencies as $currency) {
                if (self::currencyActive($currency)) {
                    $currencies[] = $currency;
                }
            }
        } else {
            $currencies = $all_currencies;
        }

        return $currencies;
    }

    /**
     * Parses a PS currency into a Nosto currency.
     *
     * @param array $currency the PS currency data.
     * @param Context $context conteIs signup UI is broken? When xt where the currencies are used.
     * @return \Nosto\Object\Format
     * @throws \Nosto\NostoException
     */
    public function getNostoCurrency(array $currency, Context $context = null)
    {
        if (
            $context instanceof Context
            && (_PS_VERSION_ >= '1.7')
        ) {
            // In Prestashop 1.7 we use the CLDR
            try {
                $nosto_currency = self::createWithCldr($currency, $context);
                return $nosto_currency;
            } catch (Exception $e) {
                NostoTaggingHelperLogger::error(
                    sprintf(
                        'Failed to resolve currency: %s (%s)',
                        $e->getMessage(),
                        $e->getCode()
                    )
                );
            }
        }
        if (empty($currency['format'])) {
            $currency['format'] = 2;  //Fallback to format 2
        }
        switch ($currency['format']) {
            /* X 0,000.00 */
            case 1:
                $group_symbol = ',';
                $decimal_symbol = '.';
                $symbol_position = true;
                break;
            /* 0 000,00 X*/
            case 2:
                $group_symbol = ' ';
                $decimal_symbol = ',';
                $symbol_position = false;
                break;
            /* X 0.000,00 */
            case 3:
                $group_symbol = '.';
                $decimal_symbol = ',';
                $symbol_position = true;
                break;
            /* 0,000.00 X */
            case 4:
                $group_symbol = ',';
                $decimal_symbol = '.';
                $symbol_position = false;
                break;
            /* X 0'000.00 */
            case 5:
                $group_symbol = '\'';
                $decimal_symbol = '.';
                $symbol_position = true;
                break;

            default:
                throw new Nosto\NostoException(
                    sprintf(
                        'Unsupported PrestaShop currency format %d.',
                        $currency['format']
                    )
                );
        }
        return new Nosto\Object\Format(
            $symbol_position,
            $group_symbol,
            self::CURRENCY_GROUP_LENGTH,
            $decimal_symbol,
            self::CURRENCY_PRECISION
        );
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
     * Creates Nosto currency object using CLDR that was introduced in Prestashop 1.7
     *
     * @see https://github.com/ICanBoogie/CLDR
     *
     * @param array $currency Prestashop currency array
     * @param Context $context
     * @return \Nosto\Object\Format
     * @suppress PhanTypeMismatchArgument
     */
    public static function createWithCldr(array $currency, Context $context)
    {
        $cldr = Tools::getCldr(null, $context->language->language_code);
        // @codingStandardsIgnoreLine
        $cldr_currency = new \ICanBoogie\CLDR\Currency($cldr->getRepository(),
            $currency['iso_code']);
        $localized_currency = $cldr_currency->localize($cldr->getCulture());
        $pattern = $localized_currency->locale->numbers->currency_formats['standard'];
        $symbols = $localized_currency->locale->numbers->symbols;
        $symbol_pos = Tools::strpos($pattern, self::CURRENCY_SYMBOL_MARKER);

        // Check if the currency symbol is before or after the amount.
        $symbol_position = $symbol_pos === 0;
        $group_symbol = isset($symbols['group']) ? $symbols['group'] : ',';
        $decimal_symbol = isset($symbols['decimal']) ? $symbols['decimal'] : ',';

        return new Nosto\Object\Format(
            $symbol_position,
            $group_symbol,
            self::CURRENCY_GROUP_LENGTH,
            $decimal_symbol,
            self::CURRENCY_PRECISION
        );
    }

    /**
     * Updates exchange rates for all stores and Nosto accounts
     *
     * @throws \Nosto\NostoException
     */
    public function updateExchangeRatesForAllStores()
    {
        /** @var NostoTaggingHelperContextFactory $context_factory */
        $context_factory = Nosto::helper('nosto_tagging/context_factory');
        /** @var NostoTaggingHelperConfig $helper_config */
        $helper_config = Nosto::helper('nosto_tagging/config');

        foreach (Shop::getShops() as $shop) {
            $id_shop = isset($shop['id_shop']) ? (int)$shop['id_shop'] : null;
            $id_shop_group = isset($shop['id_shop_group']) ? (int)$shop['id_shop_group'] : null;
            foreach (Language::getLanguages(true, $id_shop) as $language) {
                $id_lang = (int)$language['id_lang'];
                $use_multiple_currencies = $helper_config->useMultipleCurrencies($id_lang,
                    $id_shop_group, $id_shop);
                if ($use_multiple_currencies) {
                    $nosto_account = NostoTaggingHelperAccount::find($id_lang, $id_shop_group,
                        $id_shop);
                    if (!is_null($nosto_account)) {
                        $context = $context_factory->forgeContext($id_lang, $id_shop);
                        $operation = new RatesService($nosto_account, $context);
                        if (!$operation->updateCurrencyExchangeRates()) {
                            throw new Nosto\NostoException(
                                sprintf(
                                    'Exchange rate update failed for %s',
                                    $nosto_account->getName()
                                )
                            );
                        } else {
                            $context_factory->revertToOriginalContext();
                        }
                    }
                }
            }
        }
    }

    public static function currencyActive(array $currency)
    {
        $active = true;
        if (!$currency['active']) {
            $active = false;
        } else {
            if (isset($currency['deleted']) && $currency['deleted']) {
                $active = false;
            }
        }

        return $active;
    }
}
