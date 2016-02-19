<?php
/**
 * Copyright (c) 2015, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2015 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

/**
 * Value Object representing a price.
 *
 * Implements the "Money Pattern" but does not enforce it's usage, as some e-commerce platforms require floating point
 * calculation and the "Money Pattern" deals in currency fractions which rounds the price based on it's currencies
 * fraction unit. This rounding can result in "half a cent" being rounded up to increase the result by 1 cent, which
 * is generally what you want.
 *
 * Usage WITH the "Money Pattern":
 *
 * $price = new NostoPrice(500, new NostoCurrencyCode("EUR")); // 5 euros
 * $price = $price->multiply(1.14787);
 * echo $price->getPrice(); // prints (float) 5.74
 *
 * Usage WITHOUT the "Money Pattern":
 *
 * $price = new NostoPrice(5.00); // 5 euros
 * $price = $price->multiply(1.14787);
 * echo $price->getPrice(); // prints (float) 5.73935
 */
final class NostoPrice
{
    /**
     * @var string|float|int the price value.
     */
    private $price;

    /**
     * @var NostoCurrencyCode the currency used when calculating in currency fractions instead of using float.
     */
    private $currency;

    /**
     * Constructor.
     * Sets up the Value Object with given data.
     *
     * @param string|float|int $price the price value.
     * @param NostoCurrencyCode $currency the currency (use for currency fractions instead of float).
     *
     * @throws NostoInvalidArgumentException
     */
    public function __construct($price, NostoCurrencyCode $currency = null)
    {
        if (!is_numeric($price)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.price (%s) must be a numeric value.',
                __CLASS__,
                $price
            ));
        }
        if (!is_null($currency) && !is_int($price)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.price (%s) must be an integer (currency fraction) when using a currency.',
                __CLASS__,
                $price
            ));
        }

        $this->price = $price;
        $this->currency = $currency;
    }

    /**
     * Creates a new price object from a price string.
     *
     * Used to simplify the conversion of prices to fraction units, e.g. euros to cents.
     *
     * @param string|float|int $price the price string.
     * @param NostoCurrencyCode $currency the currency.
     * @return NostoPrice the price object.
     *
     * @throws NostoInvalidArgumentException
     */
    public static function fromString($price, NostoCurrencyCode $currency)
    {
        if (!is_numeric($price)) {
            throw new NostoInvalidArgumentException(sprintf(
                'price string (%s) must be a numeric value.',
                __CLASS__,
                $price
            ));
        }

        $fractionUnit = self::getCurrencyFractionUnit($currency);
        $fractionDecimals = self::getCurrencyFractionDecimals($currency);
        return new self((int)round($fractionUnit * round($price, $fractionDecimals), 0), $currency);
    }

    /**
     * Returns a new NostoPrice object that represents the monetary value
     * of this NostoPrice object multiplied by the given factor.
     *
     * @param string|float|int $factor the factor to multiple the price with.
     * @return NostoPrice the new price object.
     *
     * @throws NostoInvalidArgumentException
     */
    public function multiply($factor)
    {
        if (!is_numeric($factor)) {
            throw new NostoInvalidArgumentException(sprintf(
                'multiply factor (%s) must be a numeric value.',
                $factor
            ));
        }

        $value = $this->price * $factor;
        // If this price is using currency fractions, it is rounded to the nearest fraction unit.
        // This will cause "half a cent" to be rounded up to the nearest cent.
        if ($this->usingFractionUnits()) {
            $value = (int)round($value, 0);
        }

        return new self($value, $this->currency);
    }

    /**
     * Returns if this price uses fraction units, e.g. cents instead of euros.
     *
     * @return bool if the price is using fraction units.
     */
    public function usingFractionUnits()
    {
        return (!is_null($this->currency) && is_int($this->price));
    }

    /**
     * Returns the price in the currencies base units, or as is if there is not currency defined.
     *
     * @return string|float|int the price.
     */
    public function getPrice()
    {
        if ($this->usingFractionUnits()) {
            $fractionUnit = self::getCurrencyFractionUnit($this->currency);
            $fractionDecimals = self::getCurrencyFractionDecimals($this->currency);
            return round($this->price / $fractionUnit, $fractionDecimals);
        } else {
            return $this->price;
        }
    }

    /**
     * Returns the price value of this price object as is.
     *
     * @return float|int|string the price.
     */
    public function getRawPrice()
    {
        return $this->price;
    }

    /**
     * Returns the currency of this price.
     *
     * @return NostoCurrencyCode|null the currency.
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Returns the currency fraction unit to use for converting prices, e.g. from euros to cents and vice versa.
     *
     * @param NostoCurrencyCode $currency the currency.
     * @return int the unit.
     */
    private static function getCurrencyFractionUnit(NostoCurrencyCode $currency)
    {
        return NostoCurrencyInfo::getFractionUnit($currency);
    }

    /**
     * Returns how many fraction decimals should be used for prices of given currency.
     *
     * @param NostoCurrencyCode $currency the currency.
     * @return int the decimals
     */
    private static function getCurrencyFractionDecimals(NostoCurrencyCode $currency)
    {
        return NostoCurrencyInfo::getFractionDecimals($currency);
    }
}
