<?php
/**
 * Copyright (c) 2016, Nosto Solutions Ltd
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
 * @copyright 2016 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

/**
 * Product array serializer.
 */
class NostoProductSerializerArray
{
    /**
     * Serializes the product into an array structure.
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
     *     'date_published' => '2011-12-31',
     *     'variation_id' => 'EUR'
     * )
     *
     * @param NostoProductInterface $product the product to serialize.
     * @return array the serialized product array.
     */
    public function serialize(NostoProductInterface $product)
    {
        /** @var NostoFormatterDate $dateFormatter */
        $dateFormatter = Nosto::formatter('date');
        /** @var NostoFormatterPrice $priceFormatter */
        $priceFormatter = Nosto::formatter('price');

        $data = array(
            'url' => $product->getUrl(),
            'product_id' => $product->getProductId(),
            'name' => $product->getName(),
            'image_url' => $product->getImageUrl(),
            'categories' => array(),
        );

        if ($product->getAvailability() instanceof NostoProductAvailability) {
            $data['availability'] = $product->getAvailability()->getAvailability();
        } elseif (is_string($product->getAvailability())) {
            $data['availability'] = $product->getAvailability();
        } else {
            $data['availability'] = '';
        }

        if ($product->getPrice() instanceof NostoPrice) {
            $data['price'] = $priceFormatter->format($product->getPrice());
        } elseif (is_numeric($product->getPrice())) {
            $data['price'] = $product->getPrice();
        } else {
            $data['price'] = '';
        }

        if ($product->getCurrency() instanceof NostoCurrencyCode) {
            $data['price_currency_code'] = $product->getCurrency()->getCode();
        } elseif (is_string($product->getCurrency())) {
            $data['price_currency_code'] = $product->getCurrency();
        } else {
            $data['price_currency_code'] = '';
        }

        foreach ($product->getCategories() as $category) {
            if ($category instanceof NostoCategoryInterface) {
                $data['categories'][] = $category->getPath();
            } elseif (is_string($category) || is_numeric($category)) {
                $data['categories'][] = $category;
            }
        }

        // Optional properties.
        if ($product->getThumbUrl()) {
            $data['thumb_url'] = $product->getThumbUrl();
        }
        if ($product->getDescription()) {
            $data['description'] = $product->getDescription();
        }
        if ($product->getListPrice() instanceof NostoPrice) {
            $data['list_price'] = $priceFormatter->format($product->getListPrice());
        } elseif (is_numeric($product->getListPrice())) {
            $data['list_price'] = $product->getListPrice();
        } else {
            $data['list_price'] = '';
        }
        if ($product->getBrand()) {
            $data['brand'] = $product->getBrand();
        }
        foreach ($product->getTags() as $type => $tags) {
            if (is_array($tags) && count($tags) > 0) {
                $data[$type] = $tags;
            }
        }
        if ($product->getDatePublished() instanceof NostoDate) {
            $data['date_published'] = $dateFormatter->format($product->getDatePublished());
        }
        if ($product->getVariationId()) {
            $data['variation_id'] = $product->getVariationId();
        }
        if (count($product->getVariations()) > 0) {
            $data['variations'] = array();
            foreach ($product->getVariations() as $variation) {
                $variationData = array();
                if ($variation->getCurrency()) {
                    $variationData['price_currency_code'] = $variation->getCurrency()->getCode();
                }
                if ($variation->getPrice() instanceof NostoPrice) {
                    $variationData['price'] = $priceFormatter->format($variation->getPrice());
                }
                if ($variation->getListPrice() instanceof NostoPrice) {
                    $variationData['list_price'] = $priceFormatter->format($variation->getListPrice());
                }
                if ($variation->getAvailability() instanceof NostoProductAvailability) {
                    $variationData['availability'] = $variation->getAvailability()->getAvailability();
                } elseif (is_string($variation->getAvailability())) {
                    $variationData['availability'] = $variation->getAvailability();
                }
                if ($variation->getId()) {
                    $data['variations'][$variation->getId()] = $variationData;
                } else {
                    $data['variations'][] = $variationData;
                }
            }
        }

        return $data;
    }
}
