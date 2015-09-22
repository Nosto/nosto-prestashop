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
 * Helper class for price operations.
 */
class NostoTaggingHelperPrice
{
	/**
	 * Returns the product price including discounts and taxes for the given currency.
	 *
	 * @param Product|ProductCore $product the product.
	 * @param Context|ContextCore $context the context.
	 * @param Currency|CurrencyCore $currency the currency.
	 * @return NostoPrice the price.
	 */
	public function getProductPriceInclTax(Product $product, Context $context, Currency $currency)
	{
		return $this->calcPrice($product->id, $currency, $context, array('user_reduction' => true));
	}

	/**
	 * Returns the product list price including taxes for the given currency.
	 *
	 * @param Product|ProductCore $product the product.
	 * @param Context|ContextCore $context the context.
	 * @param Currency|CurrencyCore $currency the currency.
	 * @return NostoPrice the price.
	 */
	public function getProductListPriceInclTax(Product $product, Context $context, Currency $currency)
	{
		return $this->calcPrice($product->id, $currency, $context, array('user_reduction' => false));
	}

	/**
	 * Returns the cart item price including taxes for the given currency.
	 *
	 * @param Cart|CartCore $cart the cart.
	 * @param array $item the cart item.
	 * @param Context $context the context.
	 * @param Currency $currency the currency.
	 * @return NostoPrice the price.
	 */
	public function getCartItemPriceInclTax(Cart $cart, array $item, Context $context, Currency $currency)
	{
		if (Configuration::get('PS_TAX_ADDRESS_TYPE') == 'id_address_invoice')
			$id_address = (int)$cart->id_address_invoice;
		else
			$id_address = (int)$item['id_address_delivery'];

		return $this->calcPrice((int)$item['id_product'], $currency, $context, array(
			'user_reduction' => true,
			'id_product_attribute' => (isset($item['id_product_attribute']) ? (int)$item['id_product_attribute'] : null),
			'id_customer' => ((int)$cart->id_customer ? (int)$cart->id_customer : null),
			'id_cart' => (int)$cart->id,
			'id_address' => (Address::addressExists($id_address) ? (int)$id_address : null),
		));
	}

	/**
	 * Converts the price to it's base currency.
	 *
	 * @param NostoPrice $price the price in other than it's base currency.
	 * @param Currency|CurrencyCore $currency the currency the price is currently in.
	 * @return NostoPrice the converted price.
	 */
	public function convertToBaseCurrency(NostoPrice $price, Currency $currency)
	{
		$nosto_currency = new NostoCurrencyCode($currency->iso_code);
		$currency_exchange = new NostoCurrencyExchange();
		$rate = new NostoCurrencyExchangeRate($nosto_currency, 1 / $currency->conversion_rate);
		$new_price = $currency_exchange->convert($price, $rate);
		return $this->roundPrice($new_price);
	}

	/**
	 * Returns the product price for the given currency.
	 * The price is rounded according to the configured rounding mode in PS.
	 *
	 * @param int $id_product the product ID.
	 * @param Currency|CurrencyCore $currency the currency object.
	 * @param Context $context the context object.
	 * @param array $options options for the Product::getPriceStatic method.
	 * @return NostoPrice the price.
	 */
	protected function calcPrice($id_product, Currency $currency, Context $context, array $options = array())
	{
		// If the requested currency is not the one in the context, then set it.
		if ($currency->iso_code !== $context->currency->iso_code)
		{
			/** @var Currency|CurrencyCore $old_currency */
			$old_currency = $context->currency;
			$context->currency = $currency;
			// PS 1.4 has the currency stored in the cookie.
			if (isset($context->cookie, $context->cookie->id_currency))
			{
				$context->cookie->id_currency = $currency->id;
				$context->cart->id_currency = $currency->id;
			}
		}

		$options = array_merge(array(
			'include_tax' => true,
			'id_product_attribute' => null,
			'decimals' => 6,
			'divisor' => null,
			'only_reduction' => false,
			'user_reduction' => true,
			'quantity' => 1,
			'force_associated_tax' => false,
			'id_customer' => null,
			'id_cart' => null,
			'id_address' => null,
			'with_eco_tax' => true,
			'use_group_reduction' => true,
			'use_customer_price' => true,
		), $options);
		// This option is used as a reference, so we need it in a separate variable.
		$specific_price_output = null;

		$value = Product::getPriceStatic((int)$id_product, $options['include_tax'], $options['id_product_attribute'],
			$options['decimals'], $options['divisor'], $options['only_reduction'], $options['user_reduction'],
			$options['quantity'], $options['force_associated_tax'], $options['id_customer'], $options['id_cart'],
			$options['id_address'], $specific_price_output, $options['with_eco_tax'], $options['use_group_reduction'],
			$context, $options['use_customer_price']);

		// If currency was replaced in context, restore the old one.
		if (isset($old_currency))
		{
			$context->currency = $old_currency;
			// PS 1.4 has the currency stored in the cookie.
			if (isset($context->cookie, $context->cookie->id_currency))
			{
				$context->cookie->id_currency = $old_currency->id;
				$context->cart->id_currency = $old_currency->id;
			}
		}

		return $this->roundPrice(new NostoPrice($value));
	}

	/**
	 * Rounds the price according to the PS rounding mode setting.
	 *
	 * @param NostoPrice $price the price to round.
	 * @return NostoPrice the rounded price.
	 */
	protected function roundPrice(NostoPrice $price)
	{
		return new NostoPrice(Tools::ps_round($price->getRawPrice(), 2));
	}
}
