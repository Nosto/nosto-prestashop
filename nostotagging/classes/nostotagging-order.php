<?php

/**
 * Block for tagging orders.
 */
class NostoTaggingOrder extends NostoTaggingBlock
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
		if (Validate::isLoadedObject($order))
		{
			$currency = new Currency($order->id_currency);
			if (Validate::isLoadedObject($currency))
			{
				$products = array();
				$total_discounts_tax_incl = 0;
				$total_shipping_tax_incl = 0;
				$total_wrapping_tax_incl = 0;
				$total_gift_tax_incl = 0;

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

				$customer = $order->getCustomer();

				$this->order_number = (string)$order->reference;
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
	}
}
