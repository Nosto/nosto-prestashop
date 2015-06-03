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
	 * @var array line items in the cart.
	 */
	public $line_items = array();

	/**
	 * Loads the cart data from supplied cart object.
	 *
	 * @param Cart $cart the cart object.
	 */
	public function loadData(Cart $cart)
	{
		if (!Validate::isLoadedObject($cart) || ($products = $cart->getProducts()) === array())
			return;

		$currency = new Currency($cart->id_currency);
		if (!Validate::isLoadedObject($currency))
			return;

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

			$items = array_merge($products, $gift_products);
		}
		else
			$items = $products;

		foreach ($items as $item)
		{
			$name = $item['name'];
			if (isset($item['attributes_small']))
				$name .= ' ('.$item['attributes_small'].')';

			$this->line_items[] = array(
				'product_id' => (int)$item['id_product'],
				'quantity' => (int)$item['cart_quantity'],
				'name' => (string)$name,
				'unit_price' => Nosto::helper('price')->format($item['price_wt']),
				'price_currency_code' => (string)$currency->iso_code,
			);
		}
	}
}
