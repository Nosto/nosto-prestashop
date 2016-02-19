<?php

require_once(dirname(__FILE__) . '/../_support/NostoProduct.php');

class ServiceRecrawlTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var NostoAccount
     */
    private $account;

    /**
     * @var NostoServiceRecrawl
     */
    private $service;

    /**
     * @inheritdoc
     */
    protected function _before()
    {
        $this->account = new NostoAccount('platform-00000000');
        foreach (NostoApiToken::getApiTokenNames() as $tokenName) {
            $this->account->addApiToken(new NostoApiToken($tokenName, '123'));
        }
        $this->service = new NostoServiceRecrawl($this->account);
    }

    /**
     * @inheritdoc
     */
    protected function _after()
    {
        \AspectMock\test::clean();
    }

	/**
	 * Tests that product re-crawl API requests can be made.
	 */
	public function testProductReCrawl()
    {
        $this->service->addProduct(new NostoProduct());
        $result = $this->service->send();

		$this->specify('successful product re-crawl', function() use ($result) {
			$this->assertTrue($result);
		});
    }

    /**
     * Tests that product re-crawl API requests cannot be made without any products.
     */
    public function testProductReCrawlWithoutProducts()
    {
        $this->setExpectedException('NostoException');
        $this->service->send();
    }

    /**
     * Tests that product re-crawl API requests cannot be made without an API token.
     */
    public function testProductReCrawlWithoutToken()
    {
        $this->setExpectedException('NostoException');
        $service = new NostoServiceRecrawl(new NostoAccount('platform-00000000'));
        $service->addProduct(new NostoProduct());
        $service->send();
    }

    /**
     * Tests that the service fails correctly.
     */
    public function testProductReCrawlHttpFailure()
    {
        \AspectMock\test::double('NostoHttpResponse', ['getCode' => 404]);

        $this->setExpectedException('NostoHttpException');
        $this->service->addProduct(new NostoProduct());
        $this->service->send();
    }
}
