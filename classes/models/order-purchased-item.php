<?php

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
