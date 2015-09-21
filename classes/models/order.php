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
 * Model for tagging orders.
 */
class NostoTaggingOrder extends NostoTaggingModel implements NostoOrderInterface
{
	/**
	 * @var bool if we should include special line items such as discounts and shipping costs.
	 */
	protected $include_special_items = true;

	/**
	 * @var string the order number.
	 */
	protected $order_number;

	/**
	 * @var NostoTaggingOrderBuyer buyer info.
	 */
	protected $buyer_info = array();

	/**
	 * @var NostoDate the order creation date.
	 */
	protected $created_date;

	/**
	 * @var NostoTaggingOrderItem[] purchased items in the order.
	 */
	protected $purchased_items = array();

	/**
	 * @var string the payment provider module and version used in the order.
	 */
	protected $payment_provider;

	/**
	 * @var NostoTaggingOrderStatus the order status.
	 */
	protected $order_status;

	/**
	 * Sets up this DTO.
	 *
	 * @param Order|OrderCore $order the PS order model.
	 * @param Context|null $context the PS context model.
	 */
	public function loadData(Order $order, Context $context = null)
	{
		if (!Validate::isLoadedObject($order))
			return;

		if (is_null($context))
			$context = Context::getContext();

		/** @var Currency|CurrencyCore $currency */
		$currency = new Currency($order->id_currency);
		// Set the currencies conversion rate to what it was when the order was made.
		$currency->conversion_rate = $order->conversion_rate;

		$customer = new Customer((int)$order->id_customer);
		// The order reference was introduced in prestashop 1.5 where orders can be split into multiple ones.
		$this->order_number = isset($order->reference) ? (string)$order->reference : $order->id;
		$this->buyer_info = new NostoTaggingOrderBuyer($customer);
		$this->created_date = new NostoDate(strtotime($order->date_add));

		foreach ($this->fetchOrderItems($order) as $item)
			if (($line_item = $this->buildLineItem($item, $currency, $context)) !== false)
				$this->purchased_items[] = $line_item;

		$payment_module = Module::getInstanceByName($order->module);
		if ($payment_module !== false && isset($payment_module->version))
			$this->payment_provider = $order->module.' ['.$payment_module->version.']';
		else
			$this->payment_provider = $order->module.' [unknown]';

		$this->order_status = new NostoTaggingOrderStatus($order);
	}

	/**
	 * Exclude any special order items, e.g. shipping costs.
	 * This methods needs to be invoked before NostoTaggingOrder::loadData.
	 */
	public function excludeSpecialItems()
	{
		$this->include_special_items = false;
	}

	/**
	 * The unique order number identifying the order.
	 *
	 * @return string|int the order number.
	 */
	public function getOrderNumber()
	{
		return $this->order_number;
	}

	/**
	 * The date when the order was placed.
	 *
	 * @return NostoDate the creation date.
	 */
	public function getCreatedDate()
	{
		return $this->created_date;
	}

	/**
	 * The payment provider used for placing the order, formatted according to "[provider name] [provider version]".
	 *
	 * @return string the payment provider.
	 */
	public function getPaymentProvider()
	{
		return $this->payment_provider;
	}

	/**
	 * The buyer info of the user who placed the order.
	 *
	 * @return NostoOrderBuyerInterface the meta data model.
	 */
	public function getBuyerInfo()
	{
		return $this->buyer_info;
	}

	/**
	 * The purchased items which were included in the order.
	 *
	 * @return NostoOrderItemInterface[] the meta data models.
	 */
	public function getPurchasedItems()
	{
		return $this->purchased_items;
	}

	/**
	 * Returns the order status model.
	 *
	 * @return NostoOrderStatusInterface the model.
	 */
	public function getOrderStatus()
	{
		return $this->order_status;
	}

	/**
	 * Turns an order item into a NostoTaggingOrderItem object.
	 *
	 * @param array $item the item data.
	 * @param Currency|CurrencyCore $currency the currency.
	 * @param Context|null $context the PS context model.
	 * @return NostoTaggingOrderItem the line item.
	 */
	protected function buildLineItem(array $item, Currency $currency, Context $context = null)
	{
		if (isset($item['product_id']))
		{
			$id_lang = (int)$context->language->id;

			/** @var Product|ProductCore $product */
			$product = new Product($item['product_id'], false, $id_lang);
			if (!Validate::isLoadedObject($product))
				return false;

			$product_name = $product->name;
			$id_attribute = (int)$item['product_attribute_id'];
			$attribute_combinations = $this->getProductAttributeCombinationsById($product, $id_attribute, $id_lang);
			if (!empty($attribute_combinations))
			{
				$attribute_combination_names = array();
				foreach ($attribute_combinations as $attribute_combination)
					$attribute_combination_names[] = $attribute_combination['attribute_name'];
				if (!empty($attribute_combination_names))
					$product_name .= ' ('.implode(', ', $attribute_combination_names).')';
			}

			$item['id'] = $product->id;
			$item['name'] = $product_name;
			$item['quantity'] = $item['product_quantity'];
			$item['price'] = $item['product_price_wt'];
		}

		/** @var NostoTaggingHelperCurrency $helper_currency */
		$helper_currency = Nosto::helper('nosto_tagging/currency');
		/** @var NostoTaggingHelperPrice $helper_price */
		$helper_price = Nosto::helper('nosto_tagging/price');

		$base_currency = $helper_currency->getBaseCurrency($context);
		$nosto_base_currency = new NostoCurrencyCode($base_currency->iso_code);

		$nosto_price = new NostoPrice($item['price']);
		if ($currency->iso_code !== $base_currency->iso_code)
			$nosto_price = $helper_price->convertToBaseCurrency($nosto_price, $currency);

		$line_item = new NostoTaggingOrderItem($item['id'], $item['name'], $item['quantity'], $nosto_price,
			$nosto_base_currency);

		return $line_item;
	}

	/**
	 * Returns the order items.
	 *
	 * Abstracts the difference between PS versions.
	 *
	 * @param Order|OrderCore $order the PS order model.
	 * @return array the items.
	 */
	protected function fetchOrderItems(Order $order)
	{
		$total_discounts_tax_incl = 0;
		$total_shipping_tax_incl = 0;
		$total_wrapping_tax_incl = 0;
		$total_gift_tax_incl = 0;

		// Cart rules and split orders are available from prestashop 1.5 onwards.
		if (_PS_VERSION_ >= '1.5')
		{
			$products = array();
			// One order can be split into multiple orders, so we need to combine their data.
			$order_collection = Order::getByReference($order->reference);
			foreach ($order_collection as $data)
			{
				$products = array_merge($products, $data->getProducts());
				$total_discounts_tax_incl = Tools::ps_round($total_discounts_tax_incl + $data->total_discounts_tax_incl, 2);
				$total_shipping_tax_incl = Tools::ps_round($total_shipping_tax_incl + $data->total_shipping_tax_incl, 2);
				$total_wrapping_tax_incl = Tools::ps_round($total_wrapping_tax_incl + $data->total_wrapping_tax_incl, 2);
			}

			$gift_products = array();
			$cart_rules = array();
			$cart = new Cart($order->id_cart);
			if (Validate::isLoadedObject($cart))
				$cart_rules = (array)$cart->getCartRules();

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
			$items = $order->getProducts();
			$total_discounts_tax_incl = $order->total_discounts;
			$total_shipping_tax_incl = $order->total_shipping;
			$total_wrapping_tax_incl = $order->total_wrapping;
		}

		if ($this->include_special_items && !empty($items))
		{
			if ($total_discounts_tax_incl > 0)
			{
				// Subtract possible gift product price from total as gifts are tagged with price zero (0).
				$total_discounts_tax_incl = Tools::ps_round($total_discounts_tax_incl - $total_gift_tax_incl, 2);
				if ($total_discounts_tax_incl > 0)
					$items[] = array(
						'id' => -1,
						'name' => 'Discount',
						'quantity' => 1,
						'price' => -$total_discounts_tax_incl
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
				$items[] = array(
					'id' => -1,
					'name' => 'Shipping',
					'quantity' => 1,
					'price' => $total_shipping_tax_incl
				);

			if ($total_wrapping_tax_incl > 0)
				$items[] = array(
					'id' => -1,
					'name' => 'Gift Wrapping',
					'quantity' => 1,
					'price' => $total_wrapping_tax_incl
				);
		}

		return $items;
	}

	/**
	 * Returns the product attribute combination by id_product_attribute.
	 *
	 * For PS 1.4 we need to query the combinations manually, while newer version of PS provide a handy getter.
	 *
	 * @param Product|ProductCore $product the product model.
	 * @param int $id_product_attribute the product attribute ID.
	 * @param int $id_lang the language ID.
	 * @return array the attribute combinations.
	 */
	protected function getProductAttributeCombinationsById($product, $id_product_attribute, $id_lang)
	{
		if (_PS_VERSION_ >= '1.5')
			return $product->getAttributeCombinationsById($id_product_attribute, $id_lang);

		return Db::getInstance()->ExecuteS('
			SELECT pa.*, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` group_name, al.`name` attribute_name, a.`id_attribute`, pa.`unit_price_impact`
			FROM `'._DB_PREFIX_.'product_attribute` pa
			LEFT JOIN `'._DB_PREFIX_.'product_attribute_combination` pac ON pac.`id_product_attribute` = pa.`id_product_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
			LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
			LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)$id_lang.')
			LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)$id_lang.')
			WHERE pa.`id_product` = '.(int)$product->id.'
			AND pa.`id_product_attribute` = '.(int)$id_product_attribute.'
			GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
			ORDER BY pa.`id_product_attribute`'
		);
	}
}
