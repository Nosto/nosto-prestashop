<?php

require_once(dirname(__FILE__) . '/../_support/Zend/Exception.php');
require_once(dirname(__FILE__) . '/../_support/Zend/Cache.php');
require_once(dirname(__FILE__) . '/../_support/Zend/Currency.php');
require_once(dirname(__FILE__) . '/../_support/Zend/Locale.php');
require_once(dirname(__FILE__) . '/../_support/Zend/Xml/Exception.php');
require_once(dirname(__FILE__) . '/../_support/Zend/Xml/Security.php');

class CurrencyHelperTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var NostoHelperCurrency
     */
    private $helper;

    /**
     * @inheritdoc
     */
    protected function _before()
    {
        $this->helper = Nosto::helper('currency');
    }

    /**
     * @inheritdoc
     */
    protected function _after()
    {
        \AspectMock\test::clean();
    }

    /**
     * Tests that the currency helper can parse the a zend currency format correctly.
     */
    public function testZendCurrencyFormatParser()
    {
        // Format: $ #,##0.00
        $currency = $this->helper->parseZendCurrencyFormat('USD', new Zend_Currency('USD', 'en_US'));
        $this->specify('parsed USD for locale en_US', function() use ($currency) {
            $this->assertInstanceOf('NostoCurrency', $currency);
            $this->assertEquals('USD', $currency->getCode()->getCode());
            $this->assertEquals('$', $currency->getSymbol()->getSymbol());
            $this->assertEquals('left', $currency->getSymbol()->getPosition());
            $this->assertEquals(',', $currency->getFormat()->getGroupSymbol());
            $this->assertEquals('.', $currency->getFormat()->getDecimalSymbol());
            $this->assertEquals(2, $currency->getFormat()->getPrecision());
        });
    }

    /**
     * Tests that the currency helper can parse a standard zend currency format correctly.
     */
    public function testZendCurrencyFormatParserWithStandardFormat()
    {
        $mock = \AspectMock\test::double('Zend_Locale_Data', ['getContent' => function($locale, $path) { return ($path === 'currencynumber') ? '#,##0.00 ¤' : '$'; }]);

        $currency = $this->helper->parseZendCurrencyFormat('USD', new Zend_Currency('USD', 'en_US'));
        $mock->verifyInvoked('getContent');
        $this->specify('parsed format "#,##0.00 ¤"', function() use ($currency) {
            $this->assertInstanceOf('NostoCurrency', $currency);
            $this->assertEquals('USD', $currency->getCode()->getCode());
            $this->assertEquals('$', $currency->getSymbol()->getSymbol());
            $this->assertEquals('right', $currency->getSymbol()->getPosition());
            $this->assertEquals(',', $currency->getFormat()->getGroupSymbol());
            $this->assertEquals('.', $currency->getFormat()->getDecimalSymbol());
            $this->assertEquals(2, $currency->getFormat()->getPrecision());
        });
    }

    /**
     * Tests that the currency helper can parse an accounting zend currency format correctly.
     */
    public function testZendCurrencyFormatParserWithAccountingFormat()
    {
        $mock = \AspectMock\test::double('Zend_Locale_Data', ['getContent' => function($locale, $path) { return ($path === 'currencynumber') ? '¤ #,##0.00; (¤ #,##0.00)' : '$'; }]);

        $currency = $this->helper->parseZendCurrencyFormat('USD', new Zend_Currency('USD', 'en_US'));
        $mock->verifyInvoked('getContent');
        $this->specify('parsed format "¤ #,##0.00; (¤ #,##0.00)"', function() use ($currency) {
            $this->assertInstanceOf('NostoCurrency', $currency);
            $this->assertEquals('USD', $currency->getCode()->getCode());
            $this->assertEquals('$', $currency->getSymbol()->getSymbol());
            $this->assertEquals('left', $currency->getSymbol()->getPosition());
            $this->assertEquals(',', $currency->getFormat()->getGroupSymbol());
            $this->assertEquals('.', $currency->getFormat()->getDecimalSymbol());
            $this->assertEquals(2, $currency->getFormat()->getPrecision());
        });
    }

    /**
     * Tests that the currency helper can parse a currency format that does not define any decimal precision.
     */
    public function testZendCurrencyFormatParserWithNoPrecision()
    {
        $mock = \AspectMock\test::double('Zend_Locale_Data', ['getContent' => function($locale, $path) { return ($path === 'currencynumber') ? '¤ #,##0' : '$'; }]);

        $currency = $this->helper->parseZendCurrencyFormat('USD', new Zend_Currency('USD', 'en_US'));
        $mock->verifyInvoked('getContent');
        $this->specify('parsed format "¤ #,##0"', function() use ($currency) {
            $this->assertInstanceOf('NostoCurrency', $currency);
            $this->assertEquals('USD', $currency->getCode()->getCode());
            $this->assertEquals('$', $currency->getSymbol()->getSymbol());
            $this->assertEquals('left', $currency->getSymbol()->getPosition());
            $this->assertEquals(',', $currency->getFormat()->getGroupSymbol());
            $this->assertEquals('.', $currency->getFormat()->getDecimalSymbol());
            $this->assertEquals(0, $currency->getFormat()->getPrecision());
        });
    }

    /**
     * Tests that the currency helper can parse a currency format that has generated decimal precision.
     */
    public function testZendCurrencyFormatParserWithGeneratedPrecision()
    {
        $mock = \AspectMock\test::double('Zend_Locale_Data', ['getContent' => function($locale, $path) { return ($path === 'currencynumber') ? '¤ #0.#' : '$'; }]);

        $currency = $this->helper->parseZendCurrencyFormat('USD', new Zend_Currency('USD', 'en_US'));
        $mock->verifyInvoked('getContent');
        $this->specify('parsed format "¤ #0.#"', function() use ($currency) {
                $this->assertInstanceOf('NostoCurrency', $currency);
                $this->assertEquals('USD', $currency->getCode()->getCode());
                $this->assertEquals('$', $currency->getSymbol()->getSymbol());
                $this->assertEquals('left', $currency->getSymbol()->getPosition());
                $this->assertEquals(',', $currency->getFormat()->getGroupSymbol());
                $this->assertEquals('.', $currency->getFormat()->getDecimalSymbol());
                $this->assertEquals(0, $currency->getFormat()->getPrecision());
            });
    }

    /**
     * Tests that the currency helper can parse a currency format that does not define any group length.
     */
    public function testZendCurrencyFormatParserWithNoGroupLength()
    {
        $mock = \AspectMock\test::double('Zend_Locale_Data', ['getContent' => function($locale, $path) { return ($path === 'currencynumber') ? '¤ #0.00' : '$'; }]);

        $currency = $this->helper->parseZendCurrencyFormat('USD', new Zend_Currency('USD', 'en_US'));
        $mock->verifyInvoked('getContent');
        $this->specify('parsed format "¤ #0.00"', function() use ($currency) {
            $this->assertInstanceOf('NostoCurrency', $currency);
            $this->assertEquals('USD', $currency->getCode()->getCode());
            $this->assertEquals('$', $currency->getSymbol()->getSymbol());
            $this->assertEquals('left', $currency->getSymbol()->getPosition());
            $this->assertEquals(',', $currency->getFormat()->getGroupSymbol());
            $this->assertEquals('.', $currency->getFormat()->getDecimalSymbol());
            $this->assertEquals(2, $currency->getFormat()->getPrecision());
        });
    }

    /**
     * Tests that the currency helper can parse a zend currency format without currency symbol correctly.
     */
    public function testZendCurrencyFormatParserWithNoSymbol()
    {
        $mock = \AspectMock\test::double('Zend_Currency', ['getSymbol' => null]);

        $currency = $this->helper->parseZendCurrencyFormat('USD', new Zend_Currency('USD', 'en_US'));
        $mock->verifyInvoked('getSymbol');
        $this->specify('parsed format "¤ #,##0.00"', function() use ($currency) {
            $this->assertInstanceOf('NostoCurrency', $currency);
            $this->assertEquals('USD', $currency->getCode()->getCode());
            $this->assertEquals('USD', $currency->getSymbol()->getSymbol());
            $this->assertEquals('left', $currency->getSymbol()->getPosition());
            $this->assertEquals(',', $currency->getFormat()->getGroupSymbol());
            $this->assertEquals('.', $currency->getFormat()->getDecimalSymbol());
            $this->assertEquals(2, $currency->getFormat()->getPrecision());
        });
    }

    /**
     * Tests that the currency helper throws the correct exception if something goes wrong inside the zend components.
     */
    public function testZendCurrencyFormatParserException()
    {
        \AspectMock\test::double('Zend_Locale_Data', ['getList' => function() { throw new Zend_Locale_Exception(); }]);

        $this->setExpectedException('NostoInvalidArgumentException');
        $this->helper->parseZendCurrencyFormat('USD', new Zend_Currency('USD', 'en_US'));
    }
}
