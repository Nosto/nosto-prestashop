<?php


class HooksTest extends \Codeception\TestCase\Test
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
		$context = $this->tester->getContext();
		$context->controller = new FrontController();
		$context->currency = $this->tester->createCurrency();
		$context->cart = $this->tester->createCart();
	}

	/**
	 * @inheritdoc
	 */
	protected function _after()
	{
	}

	/**
	 * Tests that the header hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testHeaderHook()
    {
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayHeader();
		$this->assertStringStartsWith('<script type="text/javascript">', $result1);
		$result2 = $module->hookHeader();
		$this->assertStringStartsWith('<script type="text/javascript">', $result2);
		$this->assertEquals($result1, $result2);
    }

	/**
	 * Tests that the top hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testTopHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayTop();
		$this->assertContains('<div class="nosto_element" id="nosto-page-top"></div>', $result1);
		$result2 = $module->hookTop();
		$this->assertContains('<div class="nosto_element" id="nosto-page-top"></div>', $result2);
		$this->assertEquals($result1, $result2);
	}

	/**
	 * Tests that the footer hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testFooterHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayFooter();
		$this->assertContains('<div class="nosto_element" id="nosto-page-footer"></div>', $result1);
		$result2 = $module->hookFooter();
		$this->assertContains('<div class="nosto_element" id="nosto-page-footer"></div>', $result2);
		$this->assertEquals($result1, $result2);
	}

	/**
	 * Tests that the left column hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testLeftColumnHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayLeftColumn();
		$this->assertContains('<div class="nosto_element" id="nosto-column-left"></div>', $result1);
		$result2 = $module->hookLeftColumn();
		$this->assertContains('<div class="nosto_element" id="nosto-column-left"></div>', $result2);
		$this->assertEquals($result1, $result2);
	}

	/**
	 * Tests that the right column hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testRightColumnHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayRightColumn();
		$this->assertContains('<div class="nosto_element" id="nosto-column-right"></div>', $result1);
		$result2 = $module->hookRightColumn();
		$this->assertContains('<div class="nosto_element" id="nosto-column-right"></div>', $result2);
		$this->assertEquals($result1, $result2);
	}

	/**
	 * Tests that the product footer hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	/* todo: fails on creating the product
	public function testProductFooterHook()
	{
		$product = $this->tester->createProduct();
		$category = $this->tester->createCategory();
		$params = array('product' => $product, 'category' => $category);
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayFooterProduct($params);
		$this->assertContains('<div class="nosto_element" id="nosto-page-product1"></div>', $result1);
		$this->assertContains('<div class="nosto_element" id="nosto-page-product2"></div>', $result1);
		$this->assertContains('<div class="nosto_element" id="nosto-page-product3"></div>', $result1);
		$result2 = $module->hookProductFooter($params);
		$this->assertContains('<div class="nosto_element" id="nosto-page-product1"></div>', $result2);
		$this->assertContains('<div class="nosto_element" id="nosto-page-product2"></div>', $result2);
		$this->assertContains('<div class="nosto_element" id="nosto-page-product3"></div>', $result2);
		$this->assertEquals($result1, $result2);
	}
	*/

	/**
	 * Tests that the cart footer hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testCartFooterHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayShoppingCartFooter();
		$this->assertContains('<div class="nosto_element" id="nosto-page-cart1"></div>', $result1);
		$this->assertContains('<div class="nosto_element" id="nosto-page-cart2"></div>', $result1);
		$this->assertContains('<div class="nosto_element" id="nosto-page-cart3"></div>', $result1);
		$result2 = $module->hookShoppingCart();
		$this->assertContains('<div class="nosto_element" id="nosto-page-cart1"></div>', $result2);
		$this->assertContains('<div class="nosto_element" id="nosto-page-cart2"></div>', $result2);
		$this->assertContains('<div class="nosto_element" id="nosto-page-cart3"></div>', $result2);
		$this->assertEquals($result1, $result2);
	}

	/**
	 * Tests that the order confirmation hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testOrderConfirmationHook()
	{
		$order = $this->tester->createOrder();
		$params = array('objOrder' => $order);
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayOrderConfirmation($params);
		$this->assertContains('<div class="nosto_purchase_order" style="display:none">', $result1);
		$result2 = $module->hookOrderConfirmation($params);
		$this->assertContains('<div class="nosto_purchase_order" style="display:none">', $result2);
		$this->assertEquals($result1, $result2);
	}

	/**
	 * Tests that the category top hook return something.
	 */
	public function testCategoryTopHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayCategoryTop();
		$this->assertContains('<div class="nosto_element" id="nosto-page-category1"></div>', $result1);
	}

	/**
	 * Tests that the category footer hook return something.
	 */
	public function testCategoryFooterHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayCategoryFooter();
		$this->assertContains('<div class="nosto_element" id="nosto-page-category2"></div>', $result1);
	}

	/**
	 * Tests that the search top hook return something.
	 */
	public function testSearchTopHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplaySearchTop();
		$this->assertContains('<div class="nosto_element" id="nosto-page-search1"></div>', $result1);
	}

	/**
	 * Tests that the search footer hook return something.
	 */
	public function testSearchFooterHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplaySearchFooter();
		$this->assertContains('<div class="nosto_element" id="nosto-page-search2"></div>', $result1);
	}

	/**
	 * Tests that the payment top hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testPaymentTopHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayPaymentTop();
		$this->assertNull($result1);
		$result2 = $module->hookPaymentTop();
		$this->assertNull($result2);
	}

	/**
	 * Tests that the payment confirmation hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testPaymentConfirmationHook()
	{
		$params = array('id_order' => null);
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookActionPaymentConfirmation($params);
		$this->assertNull($result1);
		$result2 = $module->hookPaymentConfirm($params);
		$this->assertNull($result2);
	}

	/**
	 * Tests that the home page hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testHomeHook()
	{
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookDisplayHome();
		$this->assertContains('<div class="nosto_element" id="frontpage-nosto-1"></div>', $result1);
		$this->assertContains('<div class="nosto_element" id="frontpage-nosto-2"></div>', $result1);
		$this->assertContains('<div class="nosto_element" id="frontpage-nosto-3"></div>', $result1);
		$this->assertContains('<div class="nosto_element" id="frontpage-nosto-4"></div>', $result1);
		$result2 = $module->hookHome();
		$this->assertContains('<div class="nosto_element" id="frontpage-nosto-1"></div>', $result2);
		$this->assertContains('<div class="nosto_element" id="frontpage-nosto-2"></div>', $result2);
		$this->assertContains('<div class="nosto_element" id="frontpage-nosto-3"></div>', $result2);
		$this->assertContains('<div class="nosto_element" id="frontpage-nosto-4"></div>', $result2);
		$this->assertEquals($result1, $result2);
	}

	/**
	 * Tests that the object update hooks return something and both PS 1.4 and PS >= 1.5 hooks return the same thing.
	 */
	public function testObjectUpdateHook()
	{
		$params = array('object' => null);
		$module = $this->tester->getNostoTagging();
		$result1 = $module->hookActionObjectUpdateAfter($params);
		$this->assertNull($result1);
		$result2 = $module->hookUpdateProduct($params);
		$this->assertNull($result2);
		$result3 = $module->hookDeleteProduct($params);
		$this->assertNull($result3);
		$result4 = $module->hookUpdateQuantity($params);
		$this->assertNull($result4);
	}
}