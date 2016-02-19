<?php

class NostoOrderPurchasedItem implements NostoOrderItemInterface
{
	public function getProductId()
	{
		return 1;
	}
	public function getQuantity()
	{
		return 2;
	}
	public function getName()
	{
		return 'Test Product';
	}
	public function getUnitPrice()
	{
		return new NostoPrice(99.99);
	}
	public function getCurrency()
	{
		return new NostoCurrencyCode('USD');
	}
}
