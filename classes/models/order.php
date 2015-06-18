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
class NostoTaggingOrder extends NostoTaggingModel implements NostoOrderInterface, NostoValidatableInterface
{
	/**
	 * @var bool if we should include special line items such as discounts and shipping costs.
	 */
	public $include_special_items = true;

	/**
	 * @var string the order number.
	 */
	protected $order_number;

	/**
	 * @var NostoTaggingOrderBuyer buyer info.
	 */
	protected $buyer_info = array();

	/**
	 * @var string the order creation date.
	 */
	protected $created_date;

	/**
	 * @var NostoTaggingOrderPurchasedItem[] purchased items in the order.
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
	 * @inheritdoc
	 */
	public function getOrderNumber()
	{
		return $this->order_number;
	}

	/**
	 * @inheritdoc
	 */
	public function getCreatedDate()
	{
		return $this->created_date;
	}

	/**
	 * @inheritdoc
	 */
	public function getPaymentProvider()
	{
		return $this->payment_provider;
	}

	/**
	 * @inheritdoc
	 */
	public function getBuyerInfo()
	{
		return $this->buyer_info;
	}

	/**
	 * @inheritdoc
	 */
	public function getPurchasedItems()
	{
		return $this->purchased_items;
	}

	/**
	 * @inheritdoc
	 */
	public function getOrderStatus()
	{
		return $this->order_status;
	}

	/**
	 * @inheritdoc
	 */
	public function getValidationRules()
	{
		return array(
			array(
				array(
					'order_number',
					'created_date',
					'buyer_info',
					'purchased_items',
				),
				'required'
			)
		);
	}

	/**
	 * Loads the order data from supplied context and order objects.
	 *
	 * @param Context $context the context object.
	 * @param Order $order the order object.
	 */
	public function loadData(Context $context, Order $order)
	{
		if (!Validate::isLoadedObject($order))
			return;

		$customer = new Customer((int)$order->id_customer);
		// The order reference was introduced in prestashop 1.5 where orders can be split into multiple ones.
		$this->order_number = isset($order->reference) ? (string)$order->reference : $order->id;
		$this->buyer_info = new NostoTaggingOrderBuyer();
		$this->buyer_info->loadData($customer);
		$this->created_date = Nosto::helper('date')->format($order->date_add);
		$this->purchased_items = $this->findPurchasedItems($context, $order);

		$payment_module = Module::getInstanceByName($order->module);
		if ($payment_module !== false && isset($payment_module->version))
			$this->payment_provider = $order->module.' ['.$payment_module->version.']';
		else
			$this->payment_provider = $order->module.' [unknown]';

		$this->order_status = new NostoTaggingOrderStatus();
		$this->order_status->loadData($order);
	}

	/**
	 * Finds purchased items for the order.
	 *
	 * @param Context $context the context.
	 * @param Order $order the order object.
	 * @return NostoTaggingOrderPurchasedItem[] the purchased items.
	 */
	protected function findPurchasedItems(Context $context, Order $order)
	{
		$purchased_items = array();

		$currency = new Currency($order->id_currency);
		if (!Validate::isLoadedObject($currency))
			return $purchased_items;

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

		$id_lang = (int)$context->language->id;
		foreach ($items as $item)
		{
			$p = new Product($item['product_id'], false, $context->language->id);
			if (Validate::isLoadedObject($p))
			{
				$product_name = $p->name;
				$id_attribute = (int)$item['product_attribute_id'];
				$attribute_combinations = $this->getProductAttributeCombinationsById($p, $id_attribute, $id_lang);
				if (!empty($attribute_combinations))
				{
					$attribute_combination_names = array();
					foreach ($attribute_combinations as $attribute_combination)
						$attribute_combination_names[] = $attribute_combination['attribute_name'];
					if (!empty($attribute_combination_names))
						$product_name .= ' ('.implode(', ', $attribute_combination_names).')';
				}

				$purchased_item = new NostoTaggingOrderPurchasedItem();
				$purchased_item->setProductId((int)$p->id);
				$purchased_item->setQuantity((int)$item['product_quantity']);
				$purchased_item->setName((string)$product_name);
				$purchased_item->setUnitPrice(Nosto::helper('price')->format($item['product_price_wt']));
				$purchased_item->setCurrencyCode((string)$currency->iso_code);
				$purchased_items[] = $purchased_item;
			}
		}

		if ($this->include_special_items && !empty($purchased_items))
		{
			// Add special items for discounts, shipping and gift wrapping.

			if ($total_discounts_tax_incl > 0)
			{
				// Subtract possible gift product price from total as gifts are tagged with price zero (0).
				$total_discounts_tax_incl = Tools::ps_round($total_discounts_tax_incl - $total_gift_tax_incl, 2);
				if ($total_discounts_tax_incl > 0)
				{
					$purchased_item = new NostoTaggingOrderPurchasedItem();
					$purchased_item->setProductId(-1);
					$purchased_item->setQuantity(1);
					$purchased_item->setName('Discount');
					// Note the negative value.
					$purchased_item->setUnitPrice(Nosto::helper('price')->format(-$total_discounts_tax_incl));
					$purchased_item->setCurrencyCode((string)$currency->iso_code);
					$purchased_items[] = $purchased_item;
				}
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
			{
				$purchased_item = new NostoTaggingOrderPurchasedItem();
				$purchased_item->setProductId(-1);
				$purchased_item->setQuantity(1);
				$purchased_item->setName('Shipping');
				$purchased_item->setUnitPrice(Nosto::helper('price')->format($total_shipping_tax_incl));
				$purchased_item->setCurrencyCode((string)$currency->iso_code);
				$purchased_items[] = $purchased_item;
			}

			if ($total_wrapping_tax_incl > 0)
			{
				$purchased_item = new NostoTaggingOrderPurchasedItem();
				$purchased_item->setProductId(-1);
				$purchased_item->setQuantity(1);
				$purchased_item->setName('Gift Wrapping');
				$purchased_item->setUnitPrice(Nosto::helper('price')->format($total_wrapping_tax_incl));
				$purchased_item->setCurrencyCode((string)$currency->iso_code);
				$purchased_items[] = $purchased_item;
			}
		}

		return $purchased_items;
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
			LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int)($id_lang).')
			LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int)($id_lang).')
			WHERE pa.`id_product` = '.(int)($product->id).'
			AND pa.`id_product_attribute` = '.(int)$id_product_attribute.'
			GROUP BY pa.`id_product_attribute`, ag.`id_attribute_group`
			ORDER BY pa.`id_product_attribute`'
		);
	}
}
