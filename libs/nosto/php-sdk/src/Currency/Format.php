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
 * Value Object representing a currency formatting, e.g. "1.234,56".
 */
final class NostoCurrencyFormat
{
    /**
     * @var string the grouping symbol/char.
     */
    private $groupSymbol;

    /**
     * @var int the length of the group.
     */
    private $groupLength;

    /**
     * @var string the decimal symbol/char.
     */
    private $decimalSymbol;

    /**
     * @var int the value precision.
     */
    private $precision;

    /**
     * Constructor.
     * Sets up this Value Object with given data.
     *
     * @param string $groupSymbol the grouping symbol/char.
     * @param int $groupLength the length of the group.
     * @param string $decimalSymbol the decimal symbol/char.
     * @param int $precision the value precision.
     *
     * @throws NostoInvalidArgumentException
     */
    public function __construct($groupSymbol, $groupLength, $decimalSymbol, $precision)
    {
        if (!is_string($groupSymbol) || empty($groupSymbol)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.groupSymbol (%s) must be a non-empty value.',
                __CLASS__,
                $groupSymbol
            ));
        }
        if (!is_int($groupLength)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.groupLength (%s) must be an integer.',
                __CLASS__,
                $groupLength
            ));
        }
        if (!is_string($decimalSymbol) || empty($decimalSymbol)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.decimalSymbol (%s) must be a non-empty value.',
                __CLASS__,
                $decimalSymbol
            ));
        }
        if (!is_int($precision)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.precision (%s) must be an integer.',
                __CLASS__,
                $precision
            ));
        }

        $this->groupSymbol = (string)$groupSymbol;
        $this->groupLength = (int)$groupLength;
        $this->decimalSymbol = (string)$decimalSymbol;
        $this->precision = (int)$precision;
    }

    /**
     * Returns the decimal symbol/char.
     *
     * @return string the decimal symbol/char.
     */
    public function getDecimalSymbol()
    {
        return $this->decimalSymbol;
    }

    /**
     * Returns the length of the group.
     *
     * @return int the length of the group.
     */
    public function getGroupLength()
    {
        return $this->groupLength;
    }

    /**
     * Returns the grouping symbol/char.
     *
     * @return string the grouping symbol/char.
     */
    public function getGroupSymbol()
    {
        return $this->groupSymbol;
    }

    /**
     * Returns the value precision.
     *
     * @return int the value precision.
     */
    public function getPrecision()
    {
        return $this->precision;
    }
}
