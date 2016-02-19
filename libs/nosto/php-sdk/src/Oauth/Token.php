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
 * Value Object that represents an OAuth access token.
 */
final class NostoOAuthToken
{
    /**
     * @var string the merchant name.
     */
    private $merchantName;

    /**
     * @var string the access token.
     */
    private $accessToken;

    /**
     * Constructor.
     * Sets up the Value Object with given data.
     *
     * @param string $merchantName the merchant name.
     * @param string $accessToken the access token.
     *
     * @throws NostoInvalidArgumentException
     */
    public function __construct($merchantName, $accessToken)
    {
        if (!is_string($merchantName) || empty($merchantName)) {
            throw new NostoInvalidArgumentException(sprintf('%s.merchantName (%s) must be a non-empty string value', __CLASS__, $merchantName));
        }
        if (!is_string($accessToken) || empty($accessToken)) {
            throw new NostoInvalidArgumentException(sprintf('%s.accessToken (%s) must be a non-empty string value', __CLASS__, $accessToken));
        }

        $this->merchantName = $merchantName;
        $this->accessToken = $accessToken;
    }

    /**
     * Returns the merchant name.
     *
     * @return string the name.
     */
    public function getMerchantName()
    {
        return $this->merchantName;
    }

    /**
     * Returns the access token.
     *
     * @return string the token.
     */
    public function getAccessToken()
    {
        return $this->accessToken;
    }
}
