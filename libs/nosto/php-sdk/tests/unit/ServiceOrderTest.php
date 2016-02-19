<?php

require_once(dirname(__FILE__) . '/../_support/NostoOrderBuyer.php');
require_once(dirname(__FILE__) . '/../_support/NostoOrderPurchasedItem.php');
require_once(dirname(__FILE__) . '/../_support/NostoOrderStatus.php');
require_once(dirname(__FILE__) . '/../_support/NostoOrder.php');

class ServiceOrderTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

	/**
	 * @var NostoOrder
	 */
    private $order;

	/**
	 * @var NostoAccount
	 */
    private $account;

    /**
     * @var NostoServiceOrder
     */
    private $service;

	/**
	 * @inheritdoc
	 */
	protected function _before()
	{
		$this->order = new NostoOrder();
		$this->account = new NostoAccount('platform-00000000');
        $this->service = new NostoServiceOrder($this->account);
	}

    /**
     * @inheritdoc
     */
    protected function _after()
    {
        \AspectMock\test::clean();
    }

	/**
	 * Tests the matched order confirmation API call.
	 */
	public function testMatchedOrderConfirmation()
    {
        $result = $this->service->confirm($this->order, 'test123');

		$this->specify('successful matched order confirmation', function() use ($result) {
			$this->assertTrue($result);
		});
    }

	/**
	 * Tests the un-matched order confirmation API call.
	 */
	public function testUnMatchedOrderConfirmation()
	{
        $result = $this->service->confirm($this->order);

		$this->specify('successful un-matched order confirmation', function() use ($result) {
			$this->assertTrue($result);
		});
	}

    /**
     * Tests that the service fails correctly.
     */
    public function testMatchedOrderConfirmationHttpFailure()
    {
        \AspectMock\test::double('NostoHttpResponse', ['getCode' => 404]);

        $this->setExpectedException('NostoHttpException');
        $this->service->confirm($this->order, 'test123');
    }
}
