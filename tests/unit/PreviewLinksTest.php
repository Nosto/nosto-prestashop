<?php


class PreviewLinksTest extends \Codeception\TestCase\Test
{
   /**
    * @var \UnitTester
    */
    protected $tester;

	/**
	 * @inheritdoc
	 */
	protected function _before()
	{
		$this->tester->initPs();
	}

	/**
	 * @inheritdoc
	 */
	protected function _after()
	{
	}

	/**
	 * Tests the product page preview link.
	 */
	public function testProductPagePreview()
    {
		$context = $this->tester->getContext();
		$url = NostoTaggingPreviewLink::getProductPageUrl(null, $context->language->id);
		$this->assertNotEmpty($url);
    }

	/**
	 * Tests the category page preview link.
	 */
	public function testCategoryPagePreview()
	{
		$context = $this->tester->getContext();
		$url = NostoTaggingPreviewLink::getCategoryPageUrl(null, $context->language->id);
		$this->assertNotEmpty($url);
	}

	/**
	 * Tests the search page preview link.
	 */
	public function testSearchPagePreview()
	{
		$context = $this->tester->getContext();
		$url = NostoTaggingPreviewLink::getSearchPageUrl($context->language->id);
		$this->assertNotEmpty($url);
	}

	/**
	 * Tests the cart page preview link.
	 */
	public function testCartPagePreview()
	{
		$context = $this->tester->getContext();
		$url = NostoTaggingPreviewLink::getCartPageUrl($context->language->id);
		$this->assertNotEmpty($url);
	}

	/**
	 * Tests the home page preview link.
	 */
	public function testHomePagePreview()
	{
		$context = $this->tester->getContext();
		$url = NostoTaggingPreviewLink::getHomePageUrl($context->language->id);
		$this->assertNotEmpty($url);
	}
}