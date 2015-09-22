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
 * Abstract model for representing a line item in the cart and orders.
 */
abstract class NostoTaggingLineItem
{
	/**
	 * @var int the unique identifier.
	 */
	protected $product_id;

	/**
	 * @var int quantity of the item.
	 */
	protected $quantity;

	/**
	 * @var string name of the item.
	 */
	protected $name;

	/**
	 * @var NostoPrice unit price of the item.
	 */
	protected $unit_price;

	/**
	 * @var NostoCurrencyCode 3-letter ISO code (ISO 4217) for the price currency.
	 */
	protected $currency;

	/**
	 * Constructor.
	 *
	 * Sets the value object data.
	 *
	 * @param int $id the items ID.
	 * @param string $name the item name.
	 * @param int $quantity the quantity of items.
	 * @param NostoPrice $price the item unit price.
	 * @param NostoCurrencyCode $currency the item currency.
	 */
	public function __construct($id, $name, $quantity, NostoPrice $price, NostoCurrencyCode $currency)
	{
		$this->product_id = (int)$id;
		$this->name = (string)$name;
		$this->quantity = (int)$quantity;
		$this->unit_price = $price;
		$this->currency = $currency;
	}

	/**
	 * The unique identifier of the item.
	 * If this item is for discounts or shipping cost, the id can be 0.
	 *
	 * @return string|int
	 */
	public function getProductId()
	{
		return $this->product_id;
	}

	/**
	 * The quantity of the item.
	 *
	 * @return int the quantity.
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * The name of the item.
	 *
	 * @return string the name.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * The unit price of the item.
	 *
	 * @return NostoPrice the unit price.
	 */
	public function getUnitPrice()
	{
		return $this->unit_price;
	}

	/**
	 * The 3-letter ISO code (ISO 4217) for the price currency.
	 *
	 * @return NostoCurrencyCode the currency ISO code.
	 */
	public function getCurrency()
	{
		return $this->currency;
	}
}
