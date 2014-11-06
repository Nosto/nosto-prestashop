<?php


class TaggingTest extends \Codeception\TestCase\Test
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
	 * Tests that the cart tagging class can be created and validated.
	 */
	public function testCartTagging()
    {
		$context = $this->tester->getContext();
		$context->cart = $this->tester->createCart();
		$nosto_cart = new \NostoTaggingCart($context, $context->cart);
		$this->assertTrue($nosto_cart->validate());
	}

	/**
	 * Tests that the category tagging class can be created and validated.
	 */
	public function testCategoryTagging()
	{
		$context = $this->tester->getContext();
		$category = $this->tester->createCategory();
		$nosto_category = new NostoTaggingCategory($context, $category);
		$this->assertTrue($nosto_category->validate());
	}

	/**
	 * Tests that the brand tagging class can be created and validated.
	 */
	public function testBrandTagging()
	{
		$context = $this->tester->getContext();
		$manufacturer = $this->tester->createManufacturer();
		$nosto_brand = new NostoTaggingBrand($context, $manufacturer);
		$this->assertTrue($nosto_brand->validate());
	}

	/**
	 * Tests that the product tagging class can be created and validated.
	 */
	public function testProductTagging()
	{
		$context = $this->tester->getContext();
		$product = $this->tester->createProduct();
		$nosto_product = new NostoTaggingProduct($context, $product);
		$this->assertTrue($nosto_product->validate());
	}

	/**
	 * Tests that the order tagging class can be created and validated.
	 */
	public function testOrderTagging()
	{
		$context = $this->tester->getContext();
		$order = $this->tester->createOrder();
		$nosto_order = new NostoTaggingOrder($context, $order);
		$this->assertTrue($nosto_order->validate());
	}

	/**
	 * Tests that the customer tagging class can be created and validated.
	 */
	public function testCustomerTagging()
	{
		$context = $this->tester->getContext();
		$nosto_customer = new NostoTaggingCustomer($context, $context->customer);
		// The current context customer should not be logged in.
		$this->assertFalse($nosto_customer->validate());
	}
}