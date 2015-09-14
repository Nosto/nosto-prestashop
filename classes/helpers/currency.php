<?php
/**
 * 2013-2015 Nosto Solutions Ltd
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
 * @copyright 2013-2015 Nosto Solutions Ltd
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
	 * @return Currency|CurrencyCore the base currency.
	 *
	 * @throws NostoException if the currency cannot be found, we require it.
	 */
	public function getBaseCurrency(Context $context)
	{
		$id_lang = $context->language->id;
		$id_shop = $context->shop->id;
		$id_shop_group = $context->shop->id_shop_group;
		$base_id_currency = (int)Configuration::get(
			'PS_CURRENCY_DEFAULT',
			$id_lang,
			$id_shop_group,
			$id_shop
		);
		if ($base_id_currency === 0)
		{
			$base_id_currency = (int)Configuration::get(
				'PS_CURRENCY_DEFAULT',
				null,
				$id_shop_group,
				$id_shop
			);
		}

		$base_currency = new Currency($base_id_currency);
		if (!Validate::isLoadedObject($base_currency))
			throw new NostoException(
				sprintf(
					'Failed to find base currency for shop #%s and lang #%s.',
					$id_shop,
					$id_lang
				)
			);

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
		$currencies = Currency::getCurrenciesByIdShop($context->shop->id);
		return is_array($currencies) ? $currencies : array();
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
		switch ($currency['format'])
		{
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
	 * @return NostoCurrencyExchangeRateCollection the exchange rate collection.
	 */
	public function getExchangeRateCollection(Context $context)
	{
		$base_currency = $this->getBaseCurrency($context);
		$currencies = $this->getCurrencies($context);

		$collection = new NostoCurrencyExchangeRateCollection();
		foreach ($currencies as $currency) {
			// Skip base currency.
			if ($currency['iso_code'] === $base_currency->iso_code) {
				continue;
			}
			$collection[] = new NostoCurrencyExchangeRate(
				new NostoCurrencyCode($currency['iso_code']),
				$currency['conversion_rate']
			);
		}
		return $collection;
	}
}
