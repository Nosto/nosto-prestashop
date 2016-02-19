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
 * Handles create/update/delete of products through the Nosto API.
 */
class NostoServiceProduct
{
    /**
     * @var NostoAccount the account to perform the operation on.
     */
    protected $account;

    /**
     * @var NostoProductCollection collection object of products to perform the operation on.
     */
    protected $collection;

    /**
     * Constructor.
     *
     * Accepts the account for which the product operation is to be performed on.
     *
     * @param NostoAccount $account the account object.
     */
    public function __construct(NostoAccount $account)
    {
        $this->account = $account;
        $this->collection = new NostoProductCollection();
    }

    /**
     * Adds a product to the collection on which the operation is the performed.
     *
     * @param NostoProductInterface $product
     */
    public function addProduct(NostoProductInterface $product)
    {
        $this->collection[] = $product;
    }

    /**
     * Sends a POST request to create or update all the products currently in the collection.
     *
     * @return bool if the request was successful.
     * @throws NostoException on failure.
     */
    public function upsert()
    {
        $request = $this->initApiRequest();
        $request->setPath(NostoApiRequest::PATH_PRODUCTS_UPSERT);
        $response = $request->post($this->getCollectionAsJson());
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to upsert Nosto product(s).', $request, $response);
        }
        return true;
    }

    /**
     * Sends a POST request to create all the products currently in the collection.
     *
     * @return bool if the request was successful.
     * @throws NostoException on failure.
     */
    public function create()
    {
        $request = $this->initApiRequest();
        $request->setPath(NostoApiRequest::PATH_PRODUCTS_CREATE);
        $response = $request->post($this->getCollectionAsJson());
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to create Nosto product(s).', $request, $response);
        }
        return true;
    }

    /**
     * Sends a PUT request to update all the products currently in the collection.
     *
     * @return bool if the request was successful.
     * @throws NostoException on failure.
     */
    public function update()
    {
        $request = $this->initApiRequest();
        $request->setPath(NostoApiRequest::PATH_PRODUCTS_UPDATE);
        $response = $request->put($this->getCollectionAsJson());
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to update Nosto product(s).', $request, $response);
        }
        return true;
    }

    /**
     * Sends a POST request to delete all the products currently in the collection.
     *
     * @return bool if the request was successful.
     * @throws NostoException on failure.
     */
    public function delete()
    {
        $request = $this->initApiRequest();
        $request->setPath(NostoApiRequest::PATH_PRODUCTS_DISCONTINUE);
        $response = $request->post($this->getCollectionIdsAsJson());
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to delete Nosto product(s).', $request, $response);
        }
        return true;
    }

    /**
     * Create and returns a new API request object initialized with:
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
        $request->setContentType('application/json');
        $request->setAuthBasic('', $token->getValue());
        return $request;
    }

    /**
     * Converts the product object into an array and returns it.
     *
     * Example:
     *
     * array(
     *     'url' => 'http://www.example.com/product/CANOE123',
     *     'product_id' => 'CANOE123',
     *     'name' => 'ACME Foldable Canoe',
     *     'image_url' => 'http://www.example.com/product/images/CANOE123.jpg',
     *     'price' => '1269.00',
     *     'price_currency_code' => 'EUR',
     *     'availability' => 'InStock',
     *     'categories' => array('/Outdoor/Boats/Canoes', '/Sales/Boats'),
     *     'description' => 'This foldable canoe is easy to travel with.',
     *     'list_price' => '1299.00',
     *     'brand' => 'ACME',
     *     'tag1' => array('Men'),
     *     'tag2' => array('Foldable'),
     *     'tag3' => array('Brown', 'Black', 'Orange'),
     *     'date_published' => '2011-12-31'
     * )
     *
     * @param NostoProductInterface $product the object.
     * @return array the newly created array.
     */
    protected function getProductAsArray(NostoProductInterface $product)
    {
        /** @var NostoFormatterDate $dateFormatter */
        $dateFormatter = Nosto::formatter('date');
        /** @var NostoFormatterPrice $priceFormatter */
        $priceFormatter = Nosto::formatter('price');

        $dateFormat = new NostoDateFormat(NostoDateFormat::YMD);
        $priceFormat = new NostoPriceFormat(2, '.', '');

        $data = array(
            'url' => $product->getUrl(),
            'product_id' => $product->getProductId(),
            'name' => $product->getName(),
            'image_url' => $product->getImageUrl(),
            'price' => $priceFormatter->format($product->getPrice(), $priceFormat),
            'price_currency_code' => $product->getCurrency()->getCode(),
            'availability' => $product->getAvailability()->getAvailability(),
            'categories' => $product->getCategories(),
        );

        // Optional properties.

        if ($product->getThumbUrl()) {
            $data['thumb_url'] = $product->getThumbUrl();
        }
        if ($product->getFullDescription()) {
            $data['description'] = $product->getFullDescription();
        }
        if ($product->getListPrice()) {
            $data['list_price'] = $priceFormatter->format($product->getListPrice(), $priceFormat);
        }
        if ($product->getBrand()) {
            $data['brand'] = $product->getBrand();
        }
        foreach ($product->getTags() as $type => $tags) {
            if (is_array($tags) && count($tags) > 0) {
                $data[$type] = $tags;
            }
        }
        if ($product->getDatePublished()) {
            $data['date_published'] = $dateFormatter->format($product->getDatePublished(), $dateFormat);
        }

        if ($product->getPriceVariationId()) {
            $data['variation_id'] = $product->getPriceVariationId();
        }

        return $data;
    }

    /**
     * Returns the whole collection in JSON format.
     *
     * @return string the json.
     * @throws NostoException if the collection is empty.
     */
    protected function getCollectionAsJson()
    {
        $data = array();
        foreach ($this->collection->getArrayCopy() as $item) {
            /** @var NostoProductInterface $item */
            $data[] = $this->getProductAsArray($item);
        }
        if (empty($data)) {
            throw new NostoException('No products found in collection.');
        }
        return json_encode($data);
    }

    /**
     * Returns all the product IDs of the items in the collection in JSON format.
     *
     * @return string the json.
     * @throws NostoException if the collection is empty.
     */
    protected function getCollectionIdsAsJson()
    {
        $data = array();
        foreach ($this->collection->getArrayCopy() as $item) {
            /** @var NostoProductInterface $item */
            $data[] = $item->getProductId();
        }
        if (empty($data)) {
            throw new NostoException('No products found in collection.');
        }
        return json_encode($data);
    }
}
