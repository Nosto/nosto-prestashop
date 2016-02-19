<?php

class PriceFormatTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests that valid price formats can be created.
     */
    public function testValidPrice()
    {
        $format = new NostoPriceFormat(2, '.', ',');
        $this->specify('price format is like 1,000.99', function() use ($format) {
                $this->assertEquals(2, $format->getDecimals());
                $this->assertEquals('.', $format->getDecimalPoint());
                $this->assertEquals(',', $format->getThousandsSeparator());
            });
    }

    /**
     * Tests that price format cannot be created with invalid decimals.
     */
    public function testInvalidPriceFormatDecimals()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoPriceFormat(false, '.', ',');
    }

    /**
     * Tests that price format cannot be created with invalid decimal point.
     */
    public function testInvalidPriceFormatDecimalPoint()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoPriceFormat(2, false, ',');
    }

    /**
     * Tests that price format cannot be created with invalid thousand separator.
     */
    public function testInvalidPriceFormatThousandsSeparator()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoPriceFormat(2, '.', false);
    }
}
