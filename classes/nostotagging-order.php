<?php
/**
 * 2013-2014 Nosto Solutions Ltd
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
 * @copyright 2013-2014 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Block for tagging orders.
 */
class NostoTaggingOrder extends NostoTaggingBlock implements NostoOrderInterface
{
	/**
	 * @var string the order number.
	 */
	public $order_number;

	/**
	 * @var array buyer info, including, first_name, last_name, email.
	 */
	public $buyer = array();

	/**
	 * @var string the order creation date.
	 */
	public $created_at;

	/**
	 * @var array list of purchased items in the order, including product_id, quantity, name, unit_price, price_currency_code.
	 */
	public $purchased_items = array();

	/**
	 * @var string the payment provider module and version used in the order (only used in API requests).
	 */
	public $payment_provider;

	/**
	 * @inheritdoc
	 */
	public function getRequiredItems()
	{
		return array(
			'order_number',
			'buyer',
			'created_at',
			'purchased_items',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function populate()
	{
		$order = $this->object;
		if (!Validate::isLoadedObject($order))
			return;
		$currency = new Currency($order->id_currency);
		if (!Validate::isLoadedObject($currency))
			return;

		$products = array();
		$total_discounts_tax_incl = 0;
		$total_shipping_tax_incl = 0;
		$total_wrapping_tax_incl = 0;
		$total_gift_tax_incl = 0;

		// Cart rules and split orders are available from prestashop 1.5 onwards.
		if (_PS_VERSION_ >= '1.5')
		{
			// One order can be split into multiple orders, so we need to combine their data.
			$order_collection = Order::getByReference($order->reference);
			foreach ($order_collection as $item)
			{
				/** @var $item Order */
				$products = array_merge($products, $item->getProducts());
				$total_discounts_tax_incl = Tools::ps_round($total_discounts_tax_incl + $item->total_discounts_tax_incl, 2);
				$total_shipping_tax_incl = Tools::ps_round($total_shipping_tax_incl + $item->total_shipping_tax_incl, 2);
				$total_wrapping_tax_incl = Tools::ps_round($total_wrapping_tax_incl + $item->total_wrapping_tax_incl, 2);
			}

			// We need the cart rules used for the order to check for gift products and free shipping.
			// The cart is the same even if the order is split into many objects.
			$cart = new Cart($order->id_cart);
			if (Validate::isLoadedObject($cart))
				$cart_rules = (array)$cart->getCartRules();
			else
				$cart_rules = array();

			$gift_products = array();
			foreach ($cart_rules as $cart_rule)
				if ((int)$cart_rule['gift_product'])
				{
					foreach ($products as $key => &$product)
						if (empty($product['gift'])
							&& (int)$product['product_id'] === (int)$cart_rule['gift_product']
							&& (int)$product['product_attribute_id'] === (int)$cart_rule['gift_product_attribute'])
						{
							$product['product_quantity'] = (int)$product['product_quantity'];
							$product['product_quantity']--;

							if (!($product['product_quantity'] > 0))
								unset($products[$key]);

							$total_gift_tax_incl = Tools::ps_round($total_gift_tax_incl + $product['product_price_wt'], 2);

							$gift_product = $product;
							$gift_product['product_quantity'] = 1;
							$gift_product['product_price_wt'] = 0;
							$gift_product['gift'] = true;

							$gift_products[] = $gift_product;

							break; // One gift product per cart rule
						}
					unset($product);
				}

			$items = array_merge($products, $gift_products);
		}
		else
		{
			$products = $order->getProducts();
			$total_discounts_tax_incl = $order->total_discounts;
			$total_shipping_tax_incl = $order->total_shipping;
			$total_wrapping_tax_incl = $order->total_wrapping;

			$items = $products;
		}

		$customer = new Customer((int)$order->id_customer);
		// The order reference was introduced in prestashop 1.5 where orders can be split into multiple ones.
		$this->order_number = isset($order->reference) ? (string)$order->reference : $order->id;
		$this->buyer = array(
			'first_name' => $customer->firstname,
			'last_name' => $customer->lastname,
			'email' => $customer->email,
		);
		$this->created_at = NostoTaggingFormatter::formatDate($order->date_add);

		foreach ($items as $item)
		{
			$p = new Product($item['product_id'], false, $this->context->language->id);
			if (Validate::isLoadedObject($p))
				$this->purchased_items[] = array(
					'product_id' => (int)$p->id,
					'quantity' => (int)$item['product_quantity'],
					'name' => (string)$p->name,
					'unit_price' => NostoTaggingFormatter::formatPrice($item['product_price_wt']),
					'price_currency_code' => (string)$currency->iso_code,
				);
		}

		if (!empty($this->purchased_items))
		{
			// Add special items for discounts, shipping and gift wrapping.

			if ($total_discounts_tax_incl > 0)
			{
				// Subtract possible gift product price from total as gifts are tagged with price zero (0).
				$total_discounts_tax_incl = Tools::ps_round($total_discounts_tax_incl - $total_gift_tax_incl, 2);
				if ($total_discounts_tax_incl > 0)
					$this->purchased_items[] = array(
						'product_id' => -1,
						'quantity' => 1,
						'name' => 'Discount',
						'unit_price' => NostoTaggingFormatter::formatPrice(-$total_discounts_tax_incl), // Note the negative value.
						'price_currency_code' => (string)$currency->iso_code,
					);
			}

			// Check is free shipping applies to the cart.
			$free_shipping = false;
			if (isset($cart_rules))
				foreach ($cart_rules as $cart_rule)
					if ((int)$cart_rule['free_shipping'])
					{
						$free_shipping = true;
						break;
					}

			if (!$free_shipping && $total_shipping_tax_incl > 0)
				$this->purchased_items[] = array(
					'product_id' => -1,
					'quantity' => 1,
					'name' => 'Shipping',
					'unit_price' => NostoTaggingFormatter::formatPrice($total_shipping_tax_incl),
					'price_currency_code' => (string)$currency->iso_code,
				);

			if ($total_wrapping_tax_incl > 0)
				$this->purchased_items[] = array(
					'product_id' => -1,
					'quantity' => 1,
					'name' => 'Gift Wrapping',
					'unit_price' => NostoTaggingFormatter::formatPrice($total_wrapping_tax_incl),
					'price_currency_code' => (string)$currency->iso_code,
				);
		}
	}
}
