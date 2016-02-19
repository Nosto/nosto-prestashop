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
 * API request class for making API requests to Nosto.
 */
class NostoApiRequest extends NostoHttpRequest
{
    const PATH_ORDER_TAGGING = '/visits/order/confirm/{m}/{cid}';
    const PATH_UNMATCHED_ORDER_TAGGING = '/visits/order/unmatched/{m}';
    const PATH_SIGN_UP = '/accounts/create/{lang}';
    const PATH_PRODUCT_RE_CRAWL = '/products/recrawl';
    const PATH_PRODUCTS_CREATE = '/v1/products/create';
    const PATH_PRODUCTS_UPDATE = '/v1/products/update';
    const PATH_PRODUCTS_UPSERT = '/v1/products/upsert';
    const PATH_PRODUCTS_DISCONTINUE = '/v1/products/discontinue';
    const PATH_CURRENCY_EXCHANGE_RATE = '/exchangerates';
    const PATH_SETTINGS = '/settings';

    /**
     * @var string base url for the nosto api.
     */
    public static $baseUrl = 'https://api.nosto.com';

    /**
     * Setter for the end point path, e.g. one of the PATH_ constants.
     * The API base url is always prepended.
     *
     * @param string $path the endpoint path (use PATH_ constants).
     */
    public function setPath($path)
    {
        $this->setUrl(self::$baseUrl.$path);
    }
}
