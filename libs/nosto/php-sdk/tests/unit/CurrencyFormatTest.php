<?php

class CurrencyFormatTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests that a valid currency format can be created.
     */
    public function testValidCurrencyFormat()
    {
        $format = new NostoCurrencyFormat(' ', 3, '.', 2);

        $this->specify('currency group symbol is empty string', function() use ($format) {
                $this->assertEquals(' ' , $format->getGroupSymbol());
            });

        $this->specify('currency decimal symbol is dot', function() use ($format) {
                $this->assertEquals('.' , $format->getDecimalSymbol());
            });

        $this->specify('currency group length is 3', function() use ($format) {
                $this->assertEquals(3 , $format->getGroupLength());
            });

        $this->specify('currency decimal precision is 2', function() use ($format) {
                $this->assertEquals(2 , $format->getPrecision());
            });
    }

    /**
     * Tests that a currency format cannot be created with invalid group symbol.
     */
    public function testInValidCurrencyFormatGroupSymbol()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoCurrencyFormat(null, 3, '.', 2);
    }

    /**
     * Tests that a currency format cannot be created with invalid decimal symbol.
     */
    public function testInValidCurrencyFormatDecimalSymbol()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoCurrencyFormat(' ', 3, null, 2);
    }

    /**
     * Tests that a currency format cannot be created with invalid group length.
     */
    public function testInValidCurrencyFormatGroupLength()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoCurrencyFormat(' ', null, '.', 2);
    }

    /**
     * Tests that a currency format cannot be created with invalid precision.
     */
    public function testInValidCurrencyFormatPrecision()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoCurrencyFormat(' ', 3, '.', null);
    }
}
