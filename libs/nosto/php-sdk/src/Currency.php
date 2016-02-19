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
 * Class representing a currency with all it's formatting details Nosto needs.
 */
final class NostoCurrency
{
    /**
     * @var NostoCurrencyCode the currency ISO 4217 code.
     */
    private $code;

    /**
     * @var NostoCurrencySymbol the currency symbol.
     */
    private $symbol;

    /**
     * @var NostoCurrencyFormat the currency format.
     */
    private $format;

    /**
     * Constructor.
     * Assigns the currency properties.
     *
     * @param NostoCurrencyCode $code the currency ISO 4217 code.
     * @param NostoCurrencySymbol $symbol the currency symbol.
     * @param NostoCurrencyFormat $format the currency formatting.
     */
    public function __construct(NostoCurrencyCode $code, NostoCurrencySymbol $symbol, NostoCurrencyFormat $format)
    {
        $this->code = $code;
        $this->symbol = $symbol;
        $this->format = $format;
    }

    /**
     * Getter for the currency code.
     *
     * @return NostoCurrencyCode the currency ISO 4217 code.
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Getter for the currency symbol.
     *
     * @return NostoCurrencySymbol the currency symbol.
     */
    public function getSymbol()
    {
        return $this->symbol;
    }

    /**
     * Getter for the currency format.
     *
     * @return NostoCurrencyFormat the format.
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Returns the currency sub-unit.
     *
     * @return int the sub-unit.
     */
    public function getFractionUnit()
    {
        return NostoCurrencyInfo::getFractionUnit($this->code);
    }

    /**
     * Returns the currency default fraction decimals.
     *
     * @return int the fraction digits.
     */
    public function getDefaultFractionDecimals()
    {
        return NostoCurrencyInfo::getFractionDecimals($this->code);
    }
}
