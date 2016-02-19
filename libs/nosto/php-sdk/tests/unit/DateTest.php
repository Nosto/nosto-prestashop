<?php

class DateTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests that invalid dates cannot be created.
     */
    public function testInvalidDate()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoDate('2015-01-01 00:00:00');
    }

    /**
     * Tests that valid dates can be created.
     */
    public function testValidDate()
    {
        $date = new NostoDate(strtotime('2015-01-01 00:00:00'));

        $this->specify('date is 2015-01-01 00:00:00', function() use ($date) {
                $this->assertTrue($date->getTimestamp() === strtotime('2015-01-01 00:00:00'));
            });
    }
}
