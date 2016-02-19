<?php

class NostoProduct implements NostoProductInterface
{
	public function getUrl()
	{
		return 'http://my.shop.com/products/test_product.html';
	}
	public function getProductId()
	{
		return 1;
	}
	public function getName()
	{
		return 'Test Product';
	}
	public function getImageUrl()
	{
		return 'http://my.shop.com/images/test_product.jpg';
	}
	public function getThumbUrl()
	{
		return 'http://my.shop.com/images/thumbnails/test_product200x200.jpg';
	}
	public function getPrice()
	{
		return new NostoPrice(99.99);
	}
	public function getListPrice()
	{
		return new NostoPrice(110.99);
	}
	public function getCurrency()
	{
		return new NostoCurrencyCode('USD');
	}
    public function getPriceVariationId()
    {
        $variation = new NostoPriceVariation('USD');
        return $variation->getId();
    }
    public function getAvailability()
	{
		return new NostoProductAvailability('InStock');
	}
	public function getTags()
	{
		return array(
            'tag1' => array('test1', 'test2'),
            'tag2' => array('test3')
        );
	}
	public function getCategories()
	{
		return array('/a/b', '/a/b/c');
	}
    public function getShortDescription()
    {
        return 'Lorem ipsum dolor sit amet';
    }
	public function getDescription()
	{
		return 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris imperdiet ligula eu facilisis dignissim.';
	}
    public function getFullDescription()
    {
        return $this->getShortDescription().' '.$this->getDescription();
    }
	public function getBrand()
	{
		return 'Super Brand';
	}
	public function getDatePublished()
	{
		return new NostoDate(strtotime('2013-01-05'));
	}
    public function getPriceVariations()
    {
        return array(
            new NostoTestPriceVariation()
        );
    }
}
