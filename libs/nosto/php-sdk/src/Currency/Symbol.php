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
 * Value Object representing a currency symbol and it's position.
 */
final class NostoCurrencySymbol
{
    const SYMBOL_POS_LEFT = 'left';
    const SYMBOL_POS_RIGHT = 'right';

    /**
     * @var string the currency symbol, e.g. "$".
     */
    private $symbol;

    /**
     * @var string the position of the symbol when displaying the currency.
     */
    private $position;

    /**
     * Constructor.
     * Sets up this Value Object with given data.
     *
     * @param string $symbol the currency symbol.
     * @param string $position the position of the symbol when displaying the currency.
     *
     * @throws NostoInvalidArgumentException
     */
    public function __construct($symbol, $position)
    {
        if (!is_string($symbol) || empty($symbol)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.symbol (%s) must be a non-empty value.',
                __CLASS__,
                $symbol
            ));
        }
        if (!is_string($position) || !in_array($position, array(self::SYMBOL_POS_LEFT, self::SYMBOL_POS_RIGHT))) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.position (%s) must be one of the following: "%s".',
                __CLASS__,
                $position,
                implode('", "', array(self::SYMBOL_POS_LEFT, self::SYMBOL_POS_RIGHT))
            ));
        }

        $this->symbol = (string)$symbol;
        $this->position = (string)$position;
    }

    /**
     * Returns the position of the symbol when displaying the currency.
     *
     * @return string the position of the symbol when displaying the currency.
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Returns the currency symbol.
     *
     * @return string the currency symbol.
     */
    public function getSymbol()
    {
        return $this->symbol;
    }
}
