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
 * Model for tagging carts.
 */
class NostoTaggingCart extends NostoTaggingModel
{
	/**
	 * @var NostoTaggingCartItem[] line items in the cart.
	 */
	public $line_items = array();

	/**
	 * Loads the cart data from supplied cart object.
	 *
	 * @param Cart|CartCore $cart the cart object.
	 */
	public function loadData(Cart $cart)
	{
		if (!Validate::isLoadedObject($cart) || $cart->getProducts() === array())
			return;

		/** @var Currency|CurrencyCore $currency */
		$currency = new Currency($cart->id_currency);
		if (!Validate::isLoadedObject($currency))
			return;

		foreach ($this->fetchCartItems($cart) as $item)
			$this->line_items[] = $this->buildLineItem($item, $currency);
	}

	/**
	 * Returns all the items currency in the shopping cart.
	 * For PS 1.5 and above, gift products are also included.
	 *
	 * @param Cart|CartCore $cart the cart.
	 * @return array the items.
	 */
	protected function fetchCartItems(Cart $cart)
	{
		$products = $cart->getProducts();

		// Cart rules are available from prestashop 1.5 onwards.
		if (_PS_VERSION_ >= '1.5')
		{
			$cart_rules = (array)$cart->getCartRules(CartRule::FILTER_ACTION_GIFT);

			$gift_products = array();
			foreach ($cart_rules as $cart_rule)
				if ((int)$cart_rule['gift_product'])
				{
					foreach ($products as $key => &$product)
						if (empty($product['gift'])
							&& (int)$product['id_product'] === (int)$cart_rule['gift_product']
							&& (int)$product['id_product_attribute'] === (int)$cart_rule['gift_product_attribute'])
						{
							$product['cart_quantity'] = (int)$product['cart_quantity'];
							$product['cart_quantity']--;

							if (!($product['cart_quantity'] > 0))
								unset($products[$key]);

							$gift_product = $product;
							$gift_product['cart_quantity'] = 1;
							$gift_product['price_wt'] = 0;
							$gift_product['gift'] = true;

							$gift_products[] = $gift_product;

							break; // One gift product per cart rule
						}
					unset($product);
				}

			return array_merge($products, $gift_products);
		}

		return $products;
	}

	/**
	 * Builds the Nosto line item for given cart item.
	 *
	 * @param array $item the item data.
	 * @param Currency|CurrencyCore $currency the currency the item price is in.
	 * @return NostoTaggingCartItem the Nosto line item.
	 */
	protected function buildLineItem(array $item, Currency $currency)
	{
		/** @var NostoTaggingHelperCurrency $helper_currency */
		$helper_currency = Nosto::helper('nosto_tagging/currency');

		$base_currency = $helper_currency->getBaseCurrency(Context::getContext());
		$nosto_base_currency = new NostoCurrencyCode($base_currency->iso_code);
		$nosto_currency = new NostoCurrencyCode($currency->iso_code);

		$name = $item['name'];
		if (isset($item['attributes_small']))
			$name .= ' ('.$item['attributes_small'].')';

		$nosto_price = NostoPrice::fromString($item['price_wt'], $nosto_currency);
		if ($currency->iso_code !== $base_currency->iso_code)
		{
			$currency_exchange = new NostoCurrencyExchange();
			$rate = new NostoCurrencyExchangeRate($nosto_currency, 1 / $currency->conversion_rate);
			$nosto_price = $currency_exchange->convert($nosto_price, $rate);
		}

		$line_item = new NostoTaggingCartItem();
		$line_item->loadData($item['id_product'], $name, $item['cart_quantity'], $nosto_price, $nosto_base_currency);

		return $line_item;
	}
}
