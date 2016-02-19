<?php

class PriceVariationTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests that valid price variation ID can be created.
     */
    public function testValidPriceVariation()
    {
        $variation = new NostoPriceVariation('TEST');
        $this->specify('price variation is TEST', function() use ($variation) {
                $this->assertEquals('TEST', $variation->getId());
            });
    }

    /**
     * Tests that invalid variation ID's cannot be created.
     */
    public function testInvalidPriceVariation()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoPriceVariation(123);
    }
}
