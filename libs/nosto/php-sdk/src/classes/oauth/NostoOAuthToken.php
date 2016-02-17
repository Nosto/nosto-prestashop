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
 * Helper class that represents a oauth2 access token.
 */
class NostoOAuthToken
{
    /**
     * @var string the access token string.
     */
    public $accessToken;

    /**
     * @var string the merchant name string.
     */
    public $merchantName;

    /**
     * @var string the type of token, e.g. "bearer".
     */
    public $tokenType;

    /**
     * @var int the amount of time this token is valid for.
     */
    public $expiresIn;

    /**
     * Creates a new token instance and populates it with the given data.
     *
     * @param array $data the data to put in the token.
     * @return NostoOAuthToken
     */
    public static function create(array $data)
    {
        $token = new self();
        foreach ($data as $key => $value) {
            $key = self::underscore2CamelCase($key);
            if (property_exists($token, $key)) {
                $token->{$key} = $value;
            }
        }
        return $token;
    }

    /**
     * Converts string from underscore format to camel case format, e.g. variable_name => variableName.
     *
     * @param string $str the underscore formatted string to convert.
     * @return string the converted string.
     */
    protected static function underscore2CamelCase($str)
    {
        // Non-alpha and non-numeric characters become spaces.
        $str = preg_replace('/[^a-z0-9]+/i', ' ', $str);
        // Uppercase the first character of each word.
        $str = ucwords(trim($str));
        // Remove all spaces.
        $str = str_replace(" ", "", $str);
        // Lowercase the first character of the result.
        $str[0] = strtolower($str[0]);

        return $str;
    }
}
