<?php

class CurrencyExchangeTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests converting price from one currency to another.
     */
    public function testCurrencyExchange()
    {
        $price = new NostoPrice(500, new NostoCurrencyCode('EUR')); // 5 euro
        $exchange = new NostoCurrencyExchange();
        $exchangeRate = new NostoCurrencyExchangeRate(new NostoCurrencyCode('USD'), 1.14787);

        $this->specify('converted price is 5.74 USD', function() use ($price, $exchange, $exchangeRate) {
                $newPrice = $exchange->convert($price, $exchangeRate);
                $this->assertEquals(5.74, $newPrice->getPrice());
                $this->assertEquals('USD', $newPrice->getCurrency()->getCode());
            });
    }
}
