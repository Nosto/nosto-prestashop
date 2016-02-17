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
 * Handles product re-crawl requests to Nosto via the API.
 */
class NostoProductReCrawl
{
    /**
     * Sends a product re-crawl request to nosto.
     *
     * @param NostoProductInterface $product the product to re-crawl.
     * @param NostoAccountInterface $account the account to re-crawl the product for.
     * @return bool true on success, false otherwise.
     * @throws NostoException if the request fails or cannot be made.
     */
    public static function send(NostoProductInterface $product, NostoAccountInterface $account)
    {
        return self::sendRequest(
            $account,
            array(
                'products' => array(
                    array(
                        'product_id' => $product->getProductId(),
                        'url' => $product->getUrl(),
                    )
                ),
            )
        );
    }

    /**
     * Sends a batch product re-crawl request to nosto.
     *
     * @param NostoExportProductCollection $collection the product collection to re-crawl.
     * @param NostoAccountInterface $account the account to re-crawl the products for.
     * @return bool true on success, false otherwise.
     * @throws NostoException if the request fails or cannot be made.
     */
    public static function sendBatch(NostoExportProductCollection $collection, NostoAccountInterface $account)
    {
        if ($collection->count() === 0) {
            throw new NostoException('Failed to send product re-crawl to Nosto. No products in collection.');
        }
        $payload = array(
            'products' => array()
        );
        foreach ($collection->getArrayCopy() as $product) {
            /** @var NostoProductInterface $product */
            $payload['products'][] = array(
                'product_id' => $product->getProductId(),
                'url' => $product->getUrl(),
            );
        }
        return self::sendRequest($account, $payload);
    }

    /**
     * Sends the re-crawl API request to Nosto.
     *
     * @param NostoAccountInterface $account the account to re-crawl the product(s) for.
     * @param array $payload the request payload as an array that will be json encoded.
     * @return bool true on success.
     * @throws NostoException if the request fails or cannot be made.
     */
    protected static function sendRequest(NostoAccountInterface $account, array $payload)
    {
        $token = $account->getApiToken('products');
        if ($token === null) {
            throw new NostoException('Failed to send product re-crawl to Nosto. No `products` API token found for account.');
        }
        $request = new NostoApiRequest();
        $request->setPath(NostoApiRequest::PATH_PRODUCT_RE_CRAWL);
        $request->setContentType('application/json');
        $request->setAuthBasic('', $token->getValue());
        $response = $request->post(json_encode($payload));
        if ($response->getCode() !== 200) {
            Nosto::throwHttpException('Failed to send product re-crawl to Nosto.', $request, $response);
        }
        return true;
    }
}
