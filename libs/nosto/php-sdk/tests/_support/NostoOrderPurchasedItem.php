<?php

class NostoOrderPurchasedItem implements NostoOrderPurchasedItemInterface
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
		return 99.99;
	}
	public function getCurrencyCode()
	{
		return 'USD';
	}
}
