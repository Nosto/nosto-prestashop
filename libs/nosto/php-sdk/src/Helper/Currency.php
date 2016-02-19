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
 * Currency helper class for currency related actions.
 */
class NostoHelperCurrency extends NostoHelper
{
    /**
     * Parses a Zend_Currency & Zend_Locale into a NostoCurrency object.
     *
     * REQUIRES Zend Framework (version 1) to be available.
     *
     * @param string $currencyCode the 3-letter ISO 4217 currency code.
     * @param Zend_Currency $zendCurrency the zend currency object.
     * @return NostoCurrency the parsed nosto currency object.
     *
     * @throws NostoInvalidArgumentException
     */
    public function parseZendCurrencyFormat($currencyCode, Zend_Currency $zendCurrency)
    {
        try {
            $format = Zend_Locale_Data::getContent($zendCurrency->getLocale(), 'currencynumber');
            $symbols = Zend_Locale_Data::getList($zendCurrency->getLocale(), 'symbols');
            // Remove extra part, e.g. "¤ #,##0.00; (¤ #,##0.00)" => "¤ #,##0.00".
            if (($pos = strpos($format, ';')) !== false) {
                $format = substr($format, 0, $pos);
            }
            // Check if the currency symbol is before or after the amount.
            $symbolPosition = (strpos(trim($format), '¤') === 0)
                ? NostoCurrencySymbol::SYMBOL_POS_LEFT
                : NostoCurrencySymbol::SYMBOL_POS_RIGHT;
            // Remove all other characters than "0", "#", "." and ",",
            $format = preg_replace('/[^0\#\.,]/', '', $format);
            // Calculate the decimal precision.
            $precision = 0;
            if (($decimalPos = strpos($format, '.')) !== false) {
                $precision = (strlen($format) - (strrpos($format, '.') + 1));
            } else {
                $decimalPos = strlen($format);
            }
            $decimalFormat = substr($format, $decimalPos);
            if (($pos = strpos($decimalFormat, '#')) !== false) {
                $precision = strlen($decimalFormat) - $pos - $precision;
            }
            // Calculate the group length.
            if (strrpos($format, ',') !== false) {
                $groupLength = ($decimalPos - strrpos($format, ',') - 1);
            } else {
                $groupLength = strrpos($format, '.');
            }
            // If the symbol is missing for the current locale, use the ISO code.
            $currencySymbol = $zendCurrency->getSymbol();
            if (is_null($currencySymbol)) {
                $currencySymbol = $currencyCode;
            }
            return new NostoCurrency(
                new NostoCurrencyCode($currencyCode),
                new NostoCurrencySymbol($currencySymbol, $symbolPosition),
                new NostoCurrencyFormat(
                    $symbols['group'],
                    $groupLength,
                    $symbols['decimal'],
                    $precision
                )
            );
        } catch (Zend_Exception $e) {
            throw new NostoInvalidArgumentException($e);
        }
    }
}
