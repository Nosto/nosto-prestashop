<?php

class CurrencyTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var array
     */
    private static $validIsoCodes = array(
        'AED', 'AFN', 'ALL', 'AMD', 'ANG', 'AOA', 'ARS', 'AUD', 'AWG', 'AZN', 'BAM', 'BBD', 'BDT', 'BGN', 'BHD', 'BIF',
        'BMD', 'BND', 'BOB', 'BOV', 'BRL', 'BSD', 'BTN', 'BWP', 'BYR', 'BZD', 'CAD', 'CDF', 'CHE', 'CHF', 'CHW', 'CLF',
        'CLP', 'CNY', 'COP', 'COU', 'CRC', 'CUC', 'CUP', 'CVE', 'CZK', 'DJF', 'DKK', 'DOP', 'DZD', 'EGP', 'ERN', 'ETB',
        'EUR', 'FJD', 'FKP', 'GBP', 'GEL', 'GHS', 'GIP', 'GMD', 'GNF', 'GTQ', 'GYD', 'HKD', 'HNL', 'HRK', 'HTG', 'HUF',
        'IDR', 'ILS', 'INR', 'IQD', 'IRR', 'ISK', 'JMD', 'JOD', 'JPY', 'KES', 'KGS', 'KHR', 'KMF', 'KPW', 'KRW', 'KWD',
        'KYD', 'KZT', 'LAK', 'LBP', 'LKR', 'LRD', 'LSL', 'LYD', 'MAD', 'MDL', 'MGA', 'MKD', 'MMK', 'MNT', 'MOP', 'MRO',
        'MUR', 'MVR', 'MWK', 'MXN', 'MXV', 'MYR', 'MZN', 'NAD', 'NGN', 'NIO', 'NOK', 'NPR', 'NZD', 'OMR', 'PAB', 'PEN',
        'PGK', 'PHP', 'PKR', 'PLN', 'PYG', 'QAR', 'RON', 'RSD', 'RUB', 'RWF', 'SAR', 'SBD', 'SCR', 'SDG', 'SEK', 'SGD',
        'SHP', 'SLL', 'SOS', 'SRD', 'SSP', 'STD', 'SYP', 'SZL', 'THB', 'TJS', 'TMT', 'TND', 'TOP', 'TRY', 'TTD', 'TWD',
        'TZS', 'UAH', 'UGX', 'USD', 'USN', 'USS', 'UYI', 'UYU', 'UZS', 'VEF', 'VND', 'VUV', 'WST', 'XAF', 'XAG', 'XAU',
        'XBA', 'XBB', 'XBC', 'XBD', 'XCD', 'XDR', 'XFU', 'XOF', 'XPD', 'XPF', 'XPT', 'XSU', 'XTS', 'XUA', 'XXX', 'YER',
        'ZAR', 'ZMW',
    );

    /**
     * Tests the currency object.
     */
    public function testCurrency()
    {
        $code = 'EUR';
        $symbol = '€';
        $symbolPosition = 'right';
        $groupSymbol = ' ';
        $decimalSymbol = '.';
        $groupLength = 3;
        $precision = 2;

        $currency = new NostoCurrency(
            new NostoCurrencyCode($code),
            new NostoCurrencySymbol($symbol, $symbolPosition),
            new NostoCurrencyFormat($groupSymbol, $groupLength, $decimalSymbol, $precision)
        );

        $this->specify('currency code is EUR', function() use ($currency, $code) {
                $this->assertTrue($currency->getCode()->getCode() === $code);
            });

        $this->specify('currency symbol is €', function() use ($currency, $symbol) {
                $this->assertTrue($currency->getSymbol()->getSymbol() === $symbol);
            });

        $this->specify('currency symbol position is right', function() use ($currency, $symbolPosition) {
                $this->assertTrue($currency->getSymbol()->getPosition() === $symbolPosition);
            });

        $this->specify('currency group symbol is empty string', function() use ($currency, $groupSymbol) {
                $this->assertTrue($currency->getFormat()->getGroupSymbol() === $groupSymbol);
            });

        $this->specify('currency decimal symbol is dot', function() use ($currency, $decimalSymbol) {
                $this->assertTrue($currency->getFormat()->getDecimalSymbol() === $decimalSymbol);
            });

        $this->specify('currency group length is 3', function() use ($currency, $groupLength) {
                $this->assertTrue($currency->getFormat()->getGroupLength() === $groupLength);
            });

        $this->specify('currency decimal precision is 2', function() use ($currency, $precision) {
                $this->assertTrue($currency->getFormat()->getPrecision() === $precision);
            });

        $this->specify('currency fraction-unit is 100', function() use ($currency) {
                $this->assertTrue($currency->getFractionUnit() === 100);
            });

        $this->specify('currency fraction-decimals is 2', function() use ($currency) {
                $this->assertTrue($currency->getDefaultFractionDecimals() === 2);
            });
    }

    /**
     * Tests that all valid currency codes can be created.
     */
    public function testValidCurrencyCodes()
    {
        foreach (self::$validIsoCodes as $currencyCode) {
            $this->specify("currency code is {$currencyCode}", function() use ($currencyCode) {
                    $currency = new NostoCurrencyCode($currencyCode);
                    $this->assertEquals($currencyCode, $currency->getCode());
                });
        }
    }

    /**
     * Tests that invalid currency codes cannot be created.
     */
    public function testInvalidCurrencyCode()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoCurrencyCode('eur');
    }
}
