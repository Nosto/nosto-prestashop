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
class NostoServiceRecrawl
{
    /**
     * @var NostoAccount the Nosto account to recrawl product(s) for.
     */
    protected $account;

    /**
     * @var NostoProductCollection collection of products to re-crawl.
     */
    protected $collection;

    /**
     * Constructor.
     *
     * Accepts the Nosto account for which the service is to operate on.
     *
     * @param NostoAccount $account the Nosto account object.
     */
    public function __construct(NostoAccount $account)
    {
        $this->account = $account;
        $this->collection = new NostoProductCollection();
    }

    /**
     * Adds a product to the collection that is to be re-crawled.
     *
     * @param NostoProductInterface $product the product object.
     */
    public function addProduct(NostoProductInterface $product)
    {
        $this->collection[] = $product;
    }

    /**
     * Sends the product re-crawl request to nosto.
     *
     * @return bool true on success, false otherwise.
     * @throws NostoException if the request fails or cannot be made.
     */
    public function send()
    {
        $request = $this->initApiRequest();
        $response = $request->post($this->getCollectionAsJson());
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to send product re-crawl to Nosto.', $request, $response);
        }
        return true;
    }

    /**
     * Create and returns a new API request object initialized with:
     * - path
     * - content type
     * - auth token
     *
     * @return NostoApiRequest the newly created request object.
     * @throws NostoException if the account does not have the `products` token set.
     */
    protected function initApiRequest()
    {
        $token = $this->account->getApiToken(NostoApiToken::API_PRODUCTS);
        if (is_null($token)) {
            throw new NostoException(sprintf('No `%s` API token found for account "%s".', NostoApiToken::API_PRODUCTS, $this->account->getName()));
        }
        $request = new NostoApiRequest();
        $request->setPath(NostoApiRequest::PATH_PRODUCT_RE_CRAWL);
        $request->setContentType('application/json');
        $request->setAuthBasic('', $token->getValue());
        return $request;
    }

    /**
     * Returns the whole collection as a JSON structure.
     *
     * @return string the JSON structure.
     * @throws NostoException if the product collection is empty.
     */
    protected function getCollectionAsJson()
    {
        $data = array('products' => array());
        foreach ($this->collection->getArrayCopy() as $product) {
            /** @var NostoProductInterface $product */
            $data['products'][] = array(
                'product_id' => $product->getProductId(),
                'url' => $product->getUrl(),
            );
        }
        if (empty($data['products'])) {
            throw new NostoException('No products found in collection.');
        }
        return json_encode($data);
    }
}
