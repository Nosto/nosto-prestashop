<?php

/**
 * Block for tagging carts.
 */
class NostoTaggingCart extends NostoTaggingBlock
{
	/**
	 * @var array line items in the cart.
	 */
	public $line_items = array();

	/**
	 * @inheritdoc
	 */
	public function getRequiredItems()
	{
		return array('line_items');
	}

	/**
	 * @inheritdoc
	 */
	public function populate()
	{
		$cart = $this->object;
		if (Validate::isLoadedObject($cart) && ($products = $cart->getProducts()) !== array())
		{
			$currency = $this->context->currency;
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
				$this->line_items[] = array(
					'product_id' => (int)$item['id_product'],
					'quantity' => (int)$item['cart_quantity'],
					'name' => (string)$item['name'],
					'unit_price' => NostoTaggingFormatter::formatPrice($item['price_wt']),
					'price_currency_code' => (string)$currency->iso_code,
				);
		}
	}
}
