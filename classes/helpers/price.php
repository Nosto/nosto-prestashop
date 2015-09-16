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
		return $this->calcPrice($product, $context, $currency, true);
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
		return $this->calcPrice($product, $context, $currency, false);
	}

	/**
	 * Returns the product price for the given currency.
	 *
	 * @param Product|ProductCore $product the product.
	 * @param Context|ContextCore $context the context.
	 * @param Currency|CurrencyCore $currency the currency.
	 * @param bool $discounted_price
	 * @return NostoPrice the price.
	 */
	protected function calcPrice(Product $product, Context $context, Currency $currency, $discounted_price = true)
	{
		// If the requested currency is not the one in the context, then set it.
		if ($currency->iso_code !== $context->currency->iso_code)
		{
			/** @var Currency|CurrencyCore $old_currency */
			$old_currency = $context->currency;
			$context->currency = $currency;
			// PS 1.4 has the currency stored in the cookie.
			if (isset($context->cookie, $context->cookie->id_currency))
				$context->cookie->id_currency = $currency->id;
		}

		$id_customer = (int)$context->cookie->id_customer;
		$incl_tax = (bool)!Product::getTaxCalculationMethod($id_customer);
		$specific_price_output = null;
		$value = Product::getPriceStatic((int)$product->id, $incl_tax, null, /* $id_product_attribute */ 6,
			/* $decimals */ null, /* $divisor */ false, /* $only_reduction */ $discounted_price, /* $user_reduction */
			1, /* $quantity */ false, /* $force_associated_tax */ null, /* $id_customer */ null, /* $id_cart */
			null, /* $id_address */ $specific_price_output, /* $specific_price_output */
			true, /* $with_eco_tax */ true, /* $use_group_reduction */ $context, true /* $use_customer_price */);

		// If currency was replaced in context, restore the old one.
		if (isset($old_currency))
		{
			$context->currency = $old_currency;
			// PS 1.4 has the currency stored in the cookie.
			if (isset($context->cookie, $context->cookie->id_currency))
				$context->cookie->id_currency = $old_currency->id;
		}

		return new NostoPrice($value);
	}
}
