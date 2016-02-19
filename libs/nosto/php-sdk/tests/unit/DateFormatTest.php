<?php

class DateFormatTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests that valid date formats can be created.
     */
    public function testValidDateFormats()
    {
        $this->specify('date format can be ISO 8601', function() {
                $format = new NostoDateFormat(NostoDateFormat::ISO_8601);
                $this->assertEquals('Y-m-d\TH:i:s\Z', $format->getFormat());
            });

        $this->specify('date format can YMD', function() {
                $format = new NostoDateFormat(NostoDateFormat::YMD);
                $this->assertEquals('Y-m-d', $format->getFormat());
            });
    }

    /**
     * Tests that an invalid date format cannot be created.
     */
    public function testInvalidDateFormat()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoDateFormat('unknown');
    }
}
