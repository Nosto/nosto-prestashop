<?php

class NostoAccountMetaDataIframe implements NostoAccountMetaIframeInterface
{
	public function getFirstName()
	{
		return 'James';
	}
	public function getLastName()
	{
		return 'Kirk';
	}
	public function getEmail()
	{
		return 'james.kirk@example.com';
	}
	public function getLanguage()
	{
		return new NostoLanguageCode('en');
	}
	public function getShopLanguage()
	{
		return new NostoLanguageCode('en');
	}
	public function getUniqueId()
	{
		return '123';
	}
	public function getPlatform()
	{
		return 'platform';
	}
	public function getVersionPlatform()
	{
		return '1.0.0';
	}
	public function getVersionModule()
	{
		return '1.0.0';
	}
	public function getPreviewUrlProduct()
	{
		return 'http://my.shop.com/products/product123?nostodebug=true';
	}
	public function getPreviewUrlCategory()
	{
		return 'http://my.shop.com/products/category123?nostodebug=true';
	}
	public function getPreviewUrlSearch()
	{
		return 'http://my.shop.com/search?query=red?nostodebug=true';
	}
	public function getPreviewUrlCart()
	{
		return 'http://my.shop.com/cart?nostodebug=true';
	}
	public function getPreviewUrlFront()
	{
		return 'http://my.shop.com?nostodebug=true';
	}
	public function getShopName()
	{
		return 'Shop Name';
	}
}
