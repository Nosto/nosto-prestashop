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
 * Value Object representing a Nosto date format.
 */
final class NostoPriceFormat
{
    /**
     * @var int the number of decimals.
     */
    private $decimals;

    /**
     * @var string the decimal point.
     */
    private $decimalPoint;

    /**
     * @var string the thousands separator.
     */
    private $thousandsSeparator;

    /**
     * Constructor.
     * Sets up the Value Object with given data.
     *
     * @param int $decimals the number of decimals.
     * @param string $decimalPoint the decimal point.
     * @param string $thousandsSeparator the thousands separator.
     *
     * @throws NostoInvalidArgumentException
     */
    public function __construct($decimals, $decimalPoint, $thousandsSeparator)
    {
        if (!is_int($decimals)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.decimals (%s) must be an integer value.',
                __CLASS__,
                $decimals
            ));
        }
        if (!is_string($decimalPoint)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.decimalPoint (%s) must be an string value.',
                __CLASS__,
                $decimalPoint
            ));
        }
        if (!is_string($thousandsSeparator)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.thousandsSeparator (%s) must be an string value.',
                __CLASS__,
                $thousandsSeparator
            ));
        }

        $this->decimals = $decimals;
        $this->decimalPoint = $decimalPoint;
        $this->thousandsSeparator = $thousandsSeparator;
    }

    /**
     * Returns the number of decimals.
     *
     * @return int the decimals.
     */
    public function getDecimals()
    {
        return $this->decimals;
    }

    /**
     * Returns the decimal point.
     *
     * @return string the decimal point.
     */
    public function getDecimalPoint()
    {
        return $this->decimalPoint;
    }

    /**
     * Returns the thousands separator.
     *
     * @return string the thousands separator.
     */
    public function getThousandsSeparator()
    {
        return $this->thousandsSeparator;
    }
}
