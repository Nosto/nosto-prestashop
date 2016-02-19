<?php

class ServiceCurrencyExchangeRateTest extends \Codeception\TestCase\Test
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
     * @var NostoServiceCurrencyExchangeRate
     */
    private $service;

    /**
     * @var NostoCurrencyExchangeRateCollection
     */
    private $collection;

    /**
     * @var NostoCurrencyExchangeRate
     */
    private $rate;

    /**
     * @inheritdoc
     */
    protected function _before()
    {
        $this->account = new NostoAccount('platform-00000000');
        foreach (NostoApiToken::getApiTokenNames() as $tokenName) {
            $this->account->addApiToken(new NostoApiToken($tokenName, '123'));
        }
        $this->service = new NostoServiceCurrencyExchangeRate($this->account);
        $this->collection = new NostoCurrencyExchangeRateCollection();
        $this->collection->setValidUntil(new NostoDate(time() + (7 * 24 * 60 * 60)));
        $this->rate = new NostoCurrencyExchangeRate(new NostoCurrencyCode('EUR'), '0.706700000000');
    }

    /**
     * @inheritdoc
     */
    protected function _after()
    {
        \AspectMock\test::clean();
    }

    /**
     * Tests that the currency exchange rate API request cannot be made without an API token.
     */
    public function testCurrencyExchangeRateUpdateWithoutApiToken()
    {
        $this->setExpectedException('NostoException');
        $this->collection[] = $this->rate;
        $service = new NostoServiceCurrencyExchangeRate(new NostoAccount('platform-00000000'));
        $service->update($this->collection);
    }

    /**
     * Tests that the currency exchange rate API request cannot be made without any rates.
     */
    public function testCurrencyExchangeRateUpdateWithoutRates()
    {
        $this->setExpectedException('NostoException');
        $this->service->update($this->collection);
    }

    /**
     * Tests that the currency exchange rate API request can be made.
     */
    public function testCurrencyExchangeRateUpdate()
    {
        $this->collection[] = $this->rate;
        $result = $this->service->update($this->collection);

        $this->specify('successful currency exchange rate update', function() use ($result) {
                $this->assertTrue($result);
            });
    }

    /**
     * Tests that the service fails correctly.
     */
    public function testCurrencyExchangeRateUpdateHttpFailure()
    {
        \AspectMock\test::double('NostoHttpResponse', ['getCode' => 404]);

        $this->setExpectedException('NostoHttpException');
        $this->collection[] = $this->rate;
        $this->service->update($this->collection);
    }
}
