<?php

require_once(dirname(__FILE__) . '/../_support/NostoOrderBuyer.php');
require_once(dirname(__FILE__) . '/../_support/NostoOrderPurchasedItem.php');
require_once(dirname(__FILE__) . '/../_support/NostoOrderStatus.php');
require_once(dirname(__FILE__) . '/../_support/NostoOrder.php');

class OrderConfirmationTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

	/**
	 * @var NostoOrder
	 */
	protected $order;

	/**
	 * @var NostoAccount
	 */
	protected $account;

	/**
	 * @inheritdoc
	 */
	protected function _before()
	{
		$this->order = new NostoOrder();
		$this->account = new NostoAccount('platform-00000000');
	}

	/**
	 * Tests the matched order confirmation API call.
	 */
	public function testMatchedOrderConfirmation()
    {
		$result = NostoOrderConfirmation::send($this->order, $this->account, 'test123');

		$this->specify('successful matched order confirmation', function() use ($result) {
			$this->assertTrue($result);
		});
    }

	/**
	 * Tests the un-matched order confirmation API call.
	 */
	public function testUnMatchedOrderConfirmation()
	{
		$result = NostoOrderConfirmation::send($this->order, $this->account);

		$this->specify('successful un-matched order confirmation', function() use ($result) {
			$this->assertTrue($result);
		});
	}
}
