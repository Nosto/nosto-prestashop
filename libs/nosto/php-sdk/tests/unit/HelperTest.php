<?php

class HelperTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * Tests that you can create all valid helpers through the Nosto main class.
     */
    public function testValidHelperCreation()
    {
        $helper = Nosto::helper('currency');

        $this->specify('currency helper could be created', function() use ($helper) {
                $this->assertInstanceOf('NostoHelperCurrency', $helper);
            });

        $helper = Nosto::helper('iframe');

        $this->specify('iframe helper could be created', function() use ($helper) {
                $this->assertInstanceOf('NostoHelperIframe', $helper);
            });
    }

    /**
     * Tests that you cannot create an invalid helper through the Nosto main class.
     */
    public function testInvalidValidHelperCreation()
    {
        $this->setExpectedException('NostoException');

        Nosto::helper('unknown');
    }

    /**
     * Tests that you can create all valid formatters through the Nosto main class.
     */
    public function testValidFormatterCreation()
    {
        $helper = Nosto::formatter('date');

        $this->specify('date formatter could be created', function() use ($helper) {
                $this->assertInstanceOf('NostoFormatterDate', $helper);
            });

        $helper = Nosto::formatter('price');

        $this->specify('price formatter could be created', function() use ($helper) {
                $this->assertInstanceOf('NostoFormatterPrice', $helper);
            });
    }

    /**
     * Tests that you cannot create an invalid formatter through the Nosto main class.
     */
    public function testInvalidValidFormatterCreation()
    {
        $this->setExpectedException('NostoException');

        Nosto::formatter('unknown');
    }
}
