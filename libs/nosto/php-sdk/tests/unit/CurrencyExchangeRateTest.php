<?php

class CurrencyExchangeRateTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests that valid exchange rates can be created.
     */
    public function testCurrencyExchange()
    {
        $exchangeRate = new NostoCurrencyExchangeRate(new NostoCurrencyCode('USD'), 1.14787);

        $this->specify('rate is 1.14787 in USD', function() use ($exchangeRate) {
                $this->assertEquals('1.14787', $exchangeRate->getExchangeRate());
                $this->assertEquals('USD', $exchangeRate->getCurrency()->getCode());
            });
    }

    /**
     * Tests that invalid exchange rates cannot be created.
     */
    public function testInvalidCurrencyExchange()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoCurrencyExchangeRate(new NostoCurrencyCode('USD'), null);
    }
}
