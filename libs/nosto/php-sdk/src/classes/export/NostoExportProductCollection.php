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
 * Product collection for historical data exports.
 * Supports only items implementing "NostoProductInterface".
 */
class NostoExportProductCollection extends NostoProductCollection implements NostoExportCollectionInterface
{
    /**
     * @inheritdoc
     */
    public function getJson()
    {
        $array = array();
        /** @var NostoProductInterface $item */
        foreach ($this->getArrayCopy() as $item) {
            $array[] = array(
                'url' => $item->getUrl(),
                'product_id' => $item->getProductId(),
                'name' => $item->getName(),
                'image_url' => $item->getImageUrl(),
                'price' => Nosto::helper('price')->format($item->getPrice()),
                'list_price' => Nosto::helper('price')->format($item->getListPrice()),
                'price_currency_code' => strtoupper($item->getCurrencyCode()),
                'availability' => $item->getAvailability(),
                'tags' => $item->getTags(),
                'categories' => $item->getCategories(),
                'description' => $item->getShortDescription().'<br/>'.$item->getDescription(),
                'brand' => $item->getBrand(),
                'date_published' => Nosto::helper('date')->format($item->getDatePublished()),
            );
        }
        return json_encode($array);
    }
}
