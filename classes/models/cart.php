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
	protected $line_items = array();

	/**
	 * Sets up this DTO.
	 *
	 * @param Cart|CartCore $cart the PS cart model.
	 * @param Context|null the PS context model.
	 */
	public function loadData(Cart $cart, Context $context = null)
	{
		if (!Validate::isLoadedObject($cart) || $cart->getProducts() === array())
			return;

		if (is_null($context))
			$context = Context::getContext();

		/** @var CurrencyCore $currency */
		$currency = new Currency($cart->id_currency);
		if (Validate::isLoadedObject($currency))
			foreach ($this->fetchCartItems($cart) as $item)
				$this->line_items[] = $this->buildLineItem($item, $currency, $context);

		$this->dispatchHookActionLoadAfter(array(
			'nosto_cart' => $this,
			'cart' => $cart,
			'context' => $context
		));
	}

	/**
	 * Returns all the items currency in the shopping cart.
	 * For PS 1.5 and above, gift products are also included.
	 *
	 * @param Cart|CartCore $cart the PS cart model.
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
	 * @param Context $context the PS context model.
	 * @return NostoTaggingCartItem the Nosto line item.
	 */
	protected function buildLineItem(array $item, Currency $currency, Context $context)
	{
		/** @var NostoTaggingHelperCurrency $helper_currency */
		$helper_currency = Nosto::helper('nosto_tagging/currency');
		/** @var NostoTaggingHelperPrice $helper_price */
		$helper_price = Nosto::helper('nosto_tagging/price');

		$base_currency = $helper_currency->getBaseCurrency($context);
		$nosto_base_currency = new NostoCurrencyCode($base_currency->iso_code);

		$name = $item['name'];
		if (isset($item['attributes_small']))
			$name .= ' ('.$item['attributes_small'].')';

		$nosto_price = new NostoPrice($item['price_wt']);
		if ($currency->iso_code !== $base_currency->iso_code)
			$nosto_price = $helper_price->convertToBaseCurrency($nosto_price, $currency);

		$line_item = new NostoTaggingCartItem($item['id_product'], $name, $item['cart_quantity'], $nosto_price,
			$nosto_base_currency);

		return $line_item;
	}

	/**
	 * Returns the cart line items.
	 *
	 * @return NostoTaggingCartItem[] the items.
	 */
	public function getLineItems()
	{
		return $this->line_items;
	}

	/**
	 * Adds a new item to the cart tagging.
	 *
	 * Usage:
	 * $object->addLineItem(NostoTaggingCartItem $item);
	 *
	 * @param NostoTaggingCartItem $item the new item.
	 */
	public function addLineItem(NostoTaggingCartItem $item)
	{
		$this->line_items[] = $item;
	}
}
