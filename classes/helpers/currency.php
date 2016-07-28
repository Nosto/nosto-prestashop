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
        $id_shop_group = $context->shop->id_shop_group;
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
     * @param Context $context context where the currencies are used.
     * @return NostoCurrency the nosto currency.
     *
     * @throws NostoException if currency cannot be parsed.
     */
    public function getNostoCurrency(array $currency, Context $context = null)
    {
        if (
            $context instanceof Context
            && version_compare(_PS_VERSION_, 1.7)
        ) {

            // In Prestashop 1.7 (Beta 1) we use the CLDR
            try {
                $nosto_currency = self::createWithCldr($currency, $context);
                return $nosto_currency;
            } catch (Exception $e) {
                Nosto::helper('nosto_tagging/logger')->error(
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
                $symbol_position = NostoCurrencySymbol::SYMBOL_POS_LEFT;
                break;
            /* 0 000,00 X*/
            case 2:
                $group_symbol = ' ';
                $decimal_symbol = ',';
                $symbol_position = NostoCurrencySymbol::SYMBOL_POS_RIGHT;
                break;
            /* X 0.000,00 */
            case 3:
                $group_symbol = '.';
                $decimal_symbol = ',';
                $symbol_position = NostoCurrencySymbol::SYMBOL_POS_LEFT;
                break;
            /* 0,000.00 X */
            case 4:
                $group_symbol = ',';
                $decimal_symbol = '.';
                $symbol_position = NostoCurrencySymbol::SYMBOL_POS_RIGHT;
                break;
            /* X 0'000.00 */
            case 5:
                $group_symbol = '\'';
                $decimal_symbol = '.';
                $symbol_position = NostoCurrencySymbol::SYMBOL_POS_LEFT;
                break;

            default:
                throw new NostoException(
                    sprintf(
                        'Unsupported PrestaShop currency format %d.',
                        $currency['format']
                    )
                );
        }
        return new NostoCurrency(
            new NostoCurrencyCode($currency['iso_code']),
            new NostoCurrencySymbol($currency['sign'], $symbol_position),
            new NostoCurrencyFormat(
                $group_symbol,
                self::CURRENCY_GROUP_LENGTH,
                $decimal_symbol,
                self::CURRENCY_PRECISION
            )
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
     * Creates Nosto currency object using CLDR that was introduced in Prestashop 1.7
     * @see https://github.com/ICanBoogie/CLDR
     *
     * @param array $currency Prestashop currency array
     * @param Context $context
     *
     * @return NostoCurrency
     */
    public static function createWithCldr(array $currency, Context $context)
    {
        $cldr = Tools::getCldr(null, $context->language->language_code);
        $cldr_currency = new \ICanBoogie\CLDR\Currency($cldr->getRepository(), $currency['iso_code']);
        $localized_currency = $cldr_currency->localize($cldr->getCulture());

        $pattern = $localized_currency->locale->numbers->currency_formats['standard'];
        $symbols = $localized_currency->locale->numbers->symbols;
        $symbol_pos = Tools::strpos($pattern, self::CURRENCY_SYMBOL_MARKER);
        if ($symbol_pos === 0) {
            $symbol_position = NostoCurrencySymbol::SYMBOL_POS_LEFT;
        } else {
            $symbol_position = NostoCurrencySymbol::SYMBOL_POS_RIGHT;
        }
        $currency_code = $currency['iso_code'];
        $currency_symbol = $currency['sign'];
        $group_symbol = isset($symbols['group']) ? $symbols['group'] : ',';
        $decimal_symbol = isset($symbols['decimal']) ? $symbols['decimal'] : ',';

        return new NostoCurrency(
            new NostoCurrencyCode($currency_code),
            new NostoCurrencySymbol($currency_symbol, $symbol_position),
            new NostoCurrencyFormat(
                $group_symbol,
                self::CURRENCY_GROUP_LENGTH,
                $decimal_symbol,
                self::CURRENCY_PRECISION
            )
        );

    }
}
