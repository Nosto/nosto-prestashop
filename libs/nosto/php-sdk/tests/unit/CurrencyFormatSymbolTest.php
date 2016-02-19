<?php

class CurrencySymbolTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests that a valid currency symbol can be created.
     */
    public function testValidCurrencySymbol()
    {
        $symbol = new NostoCurrencySymbol('$', NostoCurrencySymbol::SYMBOL_POS_LEFT);

        $this->specify('currency symbol is $', function() use ($symbol) {
                $this->assertEquals('$', $symbol->getSymbol());
            });

        $this->specify('currency symbol position is "left"', function() use ($symbol) {
                $this->assertEquals(NostoCurrencySymbol::SYMBOL_POS_LEFT , $symbol->getPosition());
            });

        $symbol = new NostoCurrencySymbol('$', NostoCurrencySymbol::SYMBOL_POS_RIGHT);

        $this->specify('currency symbol position is "right"', function() use ($symbol) {
                $this->assertEquals(NostoCurrencySymbol::SYMBOL_POS_RIGHT , $symbol->getPosition());
            });
    }

    /**
     * Tests that a currency symbol cannot be created with invalid symbol.
     */
    public function testInValidCurrencySymbol()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoCurrencySymbol(null, NostoCurrencySymbol::SYMBOL_POS_LEFT);
    }

    /**
     * Tests that a currency symbol cannot be created with invalid symbol position.
     */
    public function testInValidCurrencySymbolPosition()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoCurrencySymbol('$', 'unknown');
    }
}
