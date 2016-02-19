<?php

class CurrencyInfoTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * @var array
     */
    private $info = array(
        'AED' => array(
            'name' => 'UAE Dirham',
            'numericCode' => 784,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'AFN' => array(
            'name' => 'Afghani',
            'numericCode' => 971,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'ALL' => array(
            'name' => 'Lek',
            'numericCode' => 8,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'AMD' => array(
            'name' => 'Armenian Dram',
            'numericCode' => 51,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'ANG' => array(
            'name' => 'Netherlands Antillean Guilder',
            'numericCode' => 532,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'AOA' => array(
            'name' => 'Kwanza',
            'numericCode' => 973,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'ARS' => array(
            'name' => 'Argentine Peso',
            'numericCode' => 32,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'AUD' => array(
            'name' => 'Australian Dollar',
            'numericCode' => 36,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'AWG' => array(
            'name' => 'Aruban Florin',
            'numericCode' => 533,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'AZN' => array(
            'name' => 'Azerbaijanian Manat',
            'numericCode' => 944,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BAM' => array(
            'name' => 'Convertible Mark',
            'numericCode' => 977,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BBD' => array(
            'name' => 'Barbados Dollar',
            'numericCode' => 52,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BDT' => array(
            'name' => 'Taka',
            'numericCode' => 50,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BGN' => array(
            'name' => 'Bulgarian Lev',
            'numericCode' => 975,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BHD' => array(
            'name' => 'Bahraini Dinar',
            'numericCode' => 48,
            'fractionDecimals' => 3,
            'fractionUnit' => 1000,
        ),
        'BIF' => array(
            'name' => 'Burundi Franc',
            'numericCode' => 108,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'BMD' => array(
            'name' => 'Bermudian Dollar',
            'numericCode' => 60,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BND' => array(
            'name' => 'Brunei Dollar',
            'numericCode' => 96,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BOB' => array(
            'name' => 'Boliviano',
            'numericCode' => 68,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BOV' => array(
            'name' => 'Mvdol',
            'numericCode' => 984,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BRL' => array(
            'name' => 'Brazilian Real',
            'numericCode' => 986,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BSD' => array(
            'name' => 'Bahamian Dollar',
            'numericCode' => 44,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BTN' => array(
            'name' => 'Ngultrum',
            'numericCode' => 64,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BWP' => array(
            'name' => 'Pula',
            'numericCode' => 72,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'BYR' => array(
            'name' => 'Belarussian Ruble',
            'numericCode' => 974,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'BZD' => array(
            'name' => 'Belize Dollar',
            'numericCode' => 84,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CAD' => array(
            'name' => 'Canadian Dollar',
            'numericCode' => 124,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CDF' => array(
            'name' => 'Congolese Franc',
            'numericCode' => 976,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CHE' => array(
            'name' => 'WIR Euro',
            'numericCode' => 947,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CHF' => array(
            'name' => 'Swiss Franc',
            'numericCode' => 756,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CHW' => array(
            'name' => 'WIR Franc',
            'numericCode' => 948,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CLF' => array(
            'name' => 'Unidades de fomento',
            'numericCode' => 990,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'CLP' => array(
            'name' => 'Chilean Peso',
            'numericCode' => 152,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'CNY' => array(
            'name' => 'Yuan Renminbi',
            'numericCode' => 156,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'COP' => array(
            'name' => 'Colombian Peso',
            'numericCode' => 170,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'COU' => array(
            'name' => 'Unidad de Valor Real',
            'numericCode' => 970,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CRC' => array(
            'name' => 'Costa Rican Colon',
            'numericCode' => 188,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CUC' => array(
            'name' => 'Peso Convertible',
            'numericCode' => 931,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CUP' => array(
            'name' => 'Cuban Peso',
            'numericCode' => 192,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CVE' => array(
            'name' => 'Cape Verde Escudo',
            'numericCode' => 132,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'CZK' => array(
            'name' => 'Czech Koruna',
            'numericCode' => 203,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'DJF' => array(
            'name' => 'Djibouti Franc',
            'numericCode' => 262,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'DKK' => array(
            'name' => 'Danish Krone',
            'numericCode' => 208,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'DOP' => array(
            'name' => 'Dominican Peso',
            'numericCode' => 214,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'DZD' => array(
            'name' => 'Algerian Dinar',
            'numericCode' => 12,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'EGP' => array(
            'name' => 'Egyptian Pound',
            'numericCode' => 818,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'ERN' => array(
            'name' => 'Nakfa',
            'numericCode' => 232,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'ETB' => array(
            'name' => 'Ethiopian Birr',
            'numericCode' => 230,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'EUR' => array(
            'name' => 'Euro',
            'numericCode' => 978,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'FJD' => array(
            'name' => 'Fiji Dollar',
            'numericCode' => 242,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'FKP' => array(
            'name' => 'Falkland Islands Pound',
            'numericCode' => 238,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'GBP' => array(
            'name' => 'Pound Sterling',
            'numericCode' => 826,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'GEL' => array(
            'name' => 'Lari',
            'numericCode' => 981,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'GHS' => array(
            'name' => 'Ghana Cedi',
            'numericCode' => 936,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'GIP' => array(
            'name' => 'Gibraltar Pound',
            'numericCode' => 292,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'GMD' => array(
            'name' => 'Dalasi',
            'numericCode' => 270,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'GNF' => array(
            'name' => 'Guinea Franc',
            'numericCode' => 324,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'GTQ' => array(
            'name' => 'Quetzal',
            'numericCode' => 320,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'GYD' => array(
            'name' => 'Guyana Dollar',
            'numericCode' => 328,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'HKD' => array(
            'name' => 'Hong Kong Dollar',
            'numericCode' => 344,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'HNL' => array(
            'name' => 'Lempira',
            'numericCode' => 340,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'HRK' => array(
            'name' => 'Croatian Kuna',
            'numericCode' => 191,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'HTG' => array(
            'name' => 'Gourde',
            'numericCode' => 332,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'HUF' => array(
            'name' => 'Forint',
            'numericCode' => 348,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'IDR' => array(
            'name' => 'Rupiah',
            'numericCode' => 360,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'ILS' => array(
            'name' => 'New Israeli Sheqel',
            'numericCode' => 376,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'INR' => array(
            'name' => 'Indian Rupee',
            'numericCode' => 356,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'IQD' => array(
            'name' => 'Iraqi Dinar',
            'numericCode' => 368,
            'fractionDecimals' => 3,
            'fractionUnit' => 1000,
        ),
        'IRR' => array(
            'name' => 'Iranian Rial',
            'numericCode' => 364,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'ISK' => array(
            'name' => 'Iceland Krona',
            'numericCode' => 352,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'JMD' => array(
            'name' => 'Jamaican Dollar',
            'numericCode' => 388,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'JOD' => array(
            'name' => 'Jordanian Dinar',
            'numericCode' => 400,
            'fractionDecimals' => 3,
            'fractionUnit' => 100,
        ),
        'JPY' => array(
            'name' => 'Yen',
            'numericCode' => 392,
            'fractionDecimals' => 0,
            'fractionUnit' => 1,
        ),
        'KES' => array(
            'name' => 'Kenyan Shilling',
            'numericCode' => 404,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'KGS' => array(
            'name' => 'Som',
            'numericCode' => 417,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'KHR' => array(
            'name' => 'Riel',
            'numericCode' => 116,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'KMF' => array(
            'name' => 'Comoro Franc',
            'numericCode' => 174,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'KPW' => array(
            'name' => 'North Korean Won',
            'numericCode' => 408,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'KRW' => array(
            'name' => 'Won',
            'numericCode' => 410,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'KWD' => array(
            'name' => 'Kuwaiti Dinar',
            'numericCode' => 414,
            'fractionDecimals' => 3,
            'fractionUnit' => 1000,
        ),
        'KYD' => array(
            'name' => 'Cayman Islands Dollar',
            'numericCode' => 136,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'KZT' => array(
            'name' => 'Tenge',
            'numericCode' => 398,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'LAK' => array(
            'name' => 'Kip',
            'numericCode' => 418,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'LBP' => array(
            'name' => 'Lebanese Pound',
            'numericCode' => 422,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'LKR' => array(
            'name' => 'Sri Lanka Rupee',
            'numericCode' => 144,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'LRD' => array(
            'name' => 'Liberian Dollar',
            'numericCode' => 430,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'LSL' => array(
            'name' => 'Loti',
            'numericCode' => 426,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'LYD' => array(
            'name' => 'Libyan Dinar',
            'numericCode' => 434,
            'fractionDecimals' => 3,
            'fractionUnit' => 1000,
        ),
        'MAD' => array(
            'name' => 'Moroccan Dirham',
            'numericCode' => 504,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MDL' => array(
            'name' => 'Moldovan Leu',
            'numericCode' => 498,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MGA' => array(
            'name' => 'Malagasy Ariary',
            'numericCode' => 969,
            'fractionDecimals' => 2,
            'fractionUnit' => 5,
        ),
        'MKD' => array(
            'name' => 'Denar',
            'numericCode' => 807,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MMK' => array(
            'name' => 'Kyat',
            'numericCode' => 104,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MNT' => array(
            'name' => 'Tugrik',
            'numericCode' => 496,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MOP' => array(
            'name' => 'Pataca',
            'numericCode' => 446,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MRO' => array(
            'name' => 'Ouguiya',
            'numericCode' => 478,
            'fractionDecimals' => 2,
            'fractionUnit' => 5,
        ),
        'MUR' => array(
            'name' => 'Mauritius Rupee',
            'numericCode' => 480,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MVR' => array(
            'name' => 'Rufiyaa',
            'numericCode' => 462,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MWK' => array(
            'name' => 'Kwacha',
            'numericCode' => 454,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MXN' => array(
            'name' => 'Mexican Peso',
            'numericCode' => 484,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MXV' => array(
            'name' => 'Mexican Unidad de Inversion (UDI)',
            'numericCode' => 979,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MYR' => array(
            'name' => 'Malaysian Ringgit',
            'numericCode' => 458,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'MZN' => array(
            'name' => 'Mozambique Metical',
            'numericCode' => 943,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'NAD' => array(
            'name' => 'Namibia Dollar',
            'numericCode' => 516,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'NGN' => array(
            'name' => 'Naira',
            'numericCode' => 566,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'NIO' => array(
            'name' => 'Cordoba Oro',
            'numericCode' => 558,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'NOK' => array(
            'name' => 'Norwegian Krone',
            'numericCode' => 578,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'NPR' => array(
            'name' => 'Nepalese Rupee',
            'numericCode' => 524,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'NZD' => array(
            'name' => 'New Zealand Dollar',
            'numericCode' => 554,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'OMR' => array(
            'name' => 'Rial Omani',
            'numericCode' => 512,
            'fractionDecimals' => 3,
            'fractionUnit' => 1000,
        ),
        'PAB' => array(
            'name' => 'Balboa',
            'numericCode' => 590,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'PEN' => array(
            'name' => 'Nuevo Sol',
            'numericCode' => 604,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'PGK' => array(
            'name' => 'Kina',
            'numericCode' => 598,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'PHP' => array(
            'name' => 'Philippine Peso',
            'numericCode' => 608,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'PKR' => array(
            'name' => 'Pakistan Rupee',
            'numericCode' => 586,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'PLN' => array(
            'name' => 'Zloty',
            'numericCode' => 985,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'PYG' => array(
            'name' => 'Guarani',
            'numericCode' => 600,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'QAR' => array(
            'name' => 'Qatari Rial',
            'numericCode' => 634,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'RON' => array(
            'name' => 'New Romanian Leu',
            'numericCode' => 946,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'RSD' => array(
            'name' => 'Serbian Dinar',
            'numericCode' => 941,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'RUB' => array(
            'name' => 'Russian Ruble',
            'numericCode' => 643,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'RWF' => array(
            'name' => 'Rwanda Franc',
            'numericCode' => 646,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'SAR' => array(
            'name' => 'Saudi Riyal',
            'numericCode' => 682,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SBD' => array(
            'name' => 'Solomon Islands Dollar',
            'numericCode' => 90,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SCR' => array(
            'name' => 'Seychelles Rupee',
            'numericCode' => 690,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SDG' => array(
            'name' => 'Sudanese Pound',
            'numericCode' => 938,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SEK' => array(
            'name' => 'Swedish Krona',
            'numericCode' => 752,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SGD' => array(
            'name' => 'Singapore Dollar',
            'numericCode' => 702,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SHP' => array(
            'name' => 'Saint Helena Pound',
            'numericCode' => 654,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SLL' => array(
            'name' => 'Leone',
            'numericCode' => 694,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SOS' => array(
            'name' => 'Somali Shilling',
            'numericCode' => 706,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SRD' => array(
            'name' => 'Surinam Dollar',
            'numericCode' => 968,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SSP' => array(
            'name' => 'South Sudanese Pound',
            'numericCode' => 728,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'STD' => array(
            'name' => 'Dobra',
            'numericCode' => 678,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SYP' => array(
            'name' => 'Syrian Pound',
            'numericCode' => 760,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'SZL' => array(
            'name' => 'Lilangeni',
            'numericCode' => 748,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'THB' => array(
            'name' => 'Baht',
            'numericCode' => 764,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'TJS' => array(
            'name' => 'Somoni',
            'numericCode' => 972,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'TMT' => array(
            'name' => 'Turkmenistan New Manat',
            'numericCode' => 934,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'TND' => array(
            'name' => 'Tunisian Dinar',
            'numericCode' => 788,
            'fractionDecimals' => 3,
            'fractionUnit' => 1000,
        ),
        'TOP' => array(
            'name' => 'Pa’anga',
            'numericCode' => 776,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'TRY' => array(
            'name' => 'Turkish Lira',
            'numericCode' => 949,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'TTD' => array(
            'name' => 'Trinidad and Tobago Dollar',
            'numericCode' => 780,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'TWD' => array(
            'name' => 'New Taiwan Dollar',
            'numericCode' => 901,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'TZS' => array(
            'name' => 'Tanzanian Shilling',
            'numericCode' => 834,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'UAH' => array(
            'name' => 'Hryvnia',
            'numericCode' => 980,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'UGX' => array(
            'name' => 'Uganda Shilling',
            'numericCode' => 800,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'USD' => array(
            'name' => 'US Dollar',
            'numericCode' => 840,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'USN' => array(
            'name' => 'US Dollar (Next day)',
            'numericCode' => 997,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'USS' => array(
            'name' => 'US Dollar (Same day)',
            'numericCode' => 998,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'UYI' => array(
            'name' => 'Uruguay Peso en Unidades Indexadas (URUIURUI)',
            'numericCode' => 940,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'UYU' => array(
            'name' => 'Peso Uruguayo',
            'numericCode' => 858,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'UZS' => array(
            'name' => 'Uzbekistan Sum',
            'numericCode' => 860,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'VEF' => array(
            'name' => 'Bolivar',
            'numericCode' => 937,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'VND' => array(
            'name' => 'Dong',
            'numericCode' => 704,
            'fractionDecimals' => 0,
            'fractionUnit' => 10,
        ),
        'VUV' => array(
            'name' => 'Vatu',
            'numericCode' => 548,
            'fractionDecimals' => 0,
            'fractionUnit' => 1,
        ),
        'WST' => array(
            'name' => 'Tala',
            'numericCode' => 882,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'XAF' => array(
            'name' => 'CFA Franc BEAC',
            'numericCode' => 950,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XAG' => array(
            'name' => 'Silver',
            'numericCode' => 961,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XAU' => array(
            'name' => 'Gold',
            'numericCode' => 959,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XBA' => array(
            'name' => 'Bond Markets Unit European Composite Unit (EURCO)',
            'numericCode' => 955,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XBB' => array(
            'name' => 'Bond Markets Unit European Monetary Unit (E.M.U.-6)',
            'numericCode' => 956,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XBC' => array(
            'name' => 'Bond Markets Unit European Unit of Account 9 (E.U.A.-9)',
            'numericCode' => 957,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XBD' => array(
            'name' => 'Bond Markets Unit European Unit of Account 17 (E.U.A.-17)',
            'numericCode' => 958,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XCD' => array(
            'name' => 'East Caribbean Dollar',
            'numericCode' => 951,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'XDR' => array(
            'name' => 'SDR (Special Drawing Right)',
            'numericCode' => 960,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XFU' => array(
            'name' => 'UIC-Franc',
            'numericCode' => 958,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XOF' => array(
            'name' => 'CFA Franc BCEAO',
            'numericCode' => 952,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XPD' => array(
            'name' => 'Palladium',
            'numericCode' => 964,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XPF' => array(
            'name' => 'CFP Franc',
            'numericCode' => 953,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XPT' => array(
            'name' => 'Platinum',
            'numericCode' => 962,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XSU' => array(
            'name' => 'Sucre',
            'numericCode' => 994,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XTS' => array(
            'name' => 'Codes specifically reserved for testing purposes',
            'numericCode' => 963,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XUA' => array(
            'name' => 'ADB Unit of Account',
            'numericCode' => 965,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'XXX' => array(
            'name' => 'The codes assigned for transactions where no currency is involved',
            'numericCode' => 999,
            'fractionDecimals' => 0,
            'fractionUnit' => 100,
        ),
        'YER' => array(
            'name' => 'Yemeni Rial',
            'numericCode' => 886,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'ZAR' => array(
            'name' => 'Rand',
            'numericCode' => 710,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        ),
        'ZMW' => array(
            'name' => 'Zambian Kwacha',
            'numericCode' => 967,
            'fractionDecimals' => 2,
            'fractionUnit' => 100,
        )
    );

    /**
     * @inheritdoc
     */
    protected function _after()
    {
        \AspectMock\test::clean();
    }

    /**
     * Tests all currency fraction units.
     */
    public function testCurrencyFractionUnits()
    {
        foreach ($this->info as $currencyCode => $info) {
            $expectedFractionUnit = $info['fractionUnit'];
            $this->specify("currency {$currencyCode} fraction unit is {$expectedFractionUnit}", function() use ($currencyCode, $expectedFractionUnit) {
                    $fractionUnit = NostoCurrencyInfo::getFractionUnit(new NostoCurrencyCode($currencyCode));
                    $this->assertEquals($expectedFractionUnit, $fractionUnit);
                });
        }
    }

    /**
     * Tests that you cannot get an unsupported currencies fraction units.
     */
    public function testCurrencyFractionUnitsWithInvalidCurrencyCode()
    {
        \AspectMock\test::double('NostoCurrencyCode', ['getCode' => 'FOO']);

        $this->setExpectedException('NostoInvalidArgumentException');
        NostoCurrencyInfo::getFractionUnit(new NostoCurrencyCode('USD')); // USD will be replaced by FOO by the mock.
    }

    /**
     * Tests all currency fraction decimals.
     */
    public function testCurrencyFractionDecimals()
    {
        foreach ($this->info as $currencyCode => $info) {
            $expectedFractionDecimals = $info['fractionDecimals'];
            $this->specify("currency {$currencyCode} fraction decimals is {$expectedFractionDecimals}", function() use ($currencyCode, $expectedFractionDecimals) {
                    $fractionDecimals= NostoCurrencyInfo::getFractionDecimals(new NostoCurrencyCode($currencyCode));
                    $this->assertEquals($expectedFractionDecimals, $fractionDecimals);
                });
        }
    }

    /**
     * Tests that you cannot get an unsupported currencies fraction decimals.
     */
    public function testCurrencyFractionDecimalsWithInvalidCurrencyCode()
    {
        \AspectMock\test::double('NostoCurrencyCode', ['getCode' => 'FOO']);

        $this->setExpectedException('NostoInvalidArgumentException');
        NostoCurrencyInfo::getFractionDecimals(new NostoCurrencyCode('USD')); // USD will be replaced by FOO by the mock.
    }
}
