<?php

class AvailabilityTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests that valid availability can be created.
     */
    public function testValidAvailability()
    {
        $availability = new NostoProductAvailability(NostoProductAvailability::IN_STOCK);

        $this->specify('availability is InStock', function() use ($availability) {
                $this->assertEquals(NostoProductAvailability::IN_STOCK, $availability->getAvailability());
            });

        $availability = new NostoProductAvailability(NostoProductAvailability::OUT_OF_STOCK);

        $this->specify('availability is OutOfStock', function() use ($availability) {
                $this->assertEquals(NostoProductAvailability::OUT_OF_STOCK, $availability->getAvailability());
            });
    }

    /**
     * Tests that invalid availability cannot be created.
     */
    public function testInvalidAvailability()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoProductAvailability('Unknown');
    }
}
