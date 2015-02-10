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
 * Purchased item model used by the order model.
 */
class NostoTaggingOrderPurchasedItem implements NostoOrderPurchasedItemInterface
{
	/**
	 * @var int the unique identifier of the purchased item.
	 */
	protected $product_id;

	/**
	 * @var int quantity of the item included in the order.
	 */
	protected $quantity;

	/**
	 * @var string name of the item included in the order.
	 */
	protected $name;

	/**
	 * @var float unit price of the item included in the order.
	 */
	protected $unit_price;

	/**
	 * @var string 3-letter ISO code (ISO 4217) for the currency the item was purchased in.
	 */
	protected $currency_code;

	/**
	 * Sets the unique identifier of the purchased item.
	 *
	 * @param int $product_id the identifier.
	 */
	public function setProductId($product_id)
	{
		$this->product_id = $product_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getProductId()
	{
		return $this->product_id;
	}

	/**
	 * Sets the quantity of the item included in the order.
	 *
	 * @param int $quantity the quantity.
	 */
	public function setQuantity($quantity)
	{
		$this->quantity = $quantity;
	}

	/**
	 * @inheritdoc
	 */
	public function getQuantity()
	{
		return $this->quantity;
	}

	/**
	 * Sets the name of the item included in the order.
	 *
	 * @param string $name the name.
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Sets the unit price of the item included in the order.
	 *
	 * @param float $price the unit price.
	 */
	public function setUnitPrice($price)
	{
		$this->unit_price = $price;
	}

	/**
	 * @inheritdoc
	 */
	public function getUnitPrice()
	{
		return $this->unit_price;
	}

	/**
	 * Sets the 3-letter ISO code (ISO 4217) for the currency the item was purchased in.
	 *
	 * @param string $code the ISO code.
	 */
	public function setCurrencyCode($code)
	{
		$this->currency_code = $code;
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrencyCode()
	{
		return $this->currency_code;
	}
}
