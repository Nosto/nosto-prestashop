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
 * Product DTO (Data Transfer Object).
 */
class NostoProduct implements NostoProductInterface
{
    const PRODUCT_ADD_TO_CART = 'add-to-cart';

    /**
     * @var string the absolute url to the product page in the shop frontend.
     */
    private $url;

    /**
     * @var int|string the product's unique identifier.
     */
    private $productId;

    /**
     * @var string the name of the product.
     */
    private $name;

    /**
     * @var string the absolute url the one of the product images in frontend.
     */
    private $imageUrl;

    /**
     * @var string the absolute url the one of the product thumbnails in frontend.
     */
    private $thumbUrl;

    /**
     * @var NostoPrice the product price including possible discounts and taxes.
     */
    private $price;

    /**
     * @var NostoPrice the product list price without discounts but incl taxes.
     */
    private $listPrice;

    /**
     * @var NostoCurrencyCode the currency code the product is sold in.
     */
    private $currency;

    /**
     * @var NostoProductAvailability the availability of the product.
     */
    private $availability;

    /**
     * @var array the tags for the product.
     */
    private $tags = array(
        'tag1' => array(),
        'tag2' => array(),
        'tag3' => array(),
    );

    /**
     * @var NostoCategoryInterface[] the categories the product belongs to.
     */
    private $categories = array();

    /**
     * @var string the product description.
     */
    private $description;

    /**
     * @var string the product brand name.
     */
    private $brand;

    /**
     * @var NostoDate the product publication date in the shop.
     */
    private $datePublished;

    /**
     * @var string the variation ID currently in use.
     */
    private $variationId;

    /**
     * @var NostoProductPriceVariationInterface[] the product variations.
     */
    private $variations = array();

    /**
     * @inheritdoc
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @inheritdoc
     */
    public function getProductId()
    {
        return $this->productId;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getImageUrl()
    {
        return $this->imageUrl;
    }

    /**
     * @inheritdoc
     */
    public function getThumbUrl()
    {
        return $this->thumbUrl;
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @inheritdoc
     */
    public function getListPrice()
    {
        return $this->listPrice;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * @inheritdoc
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @inheritdoc
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @inheritdoc
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @inheritdoc
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @inheritdoc
     */
    public function getDatePublished()
    {
        return $this->datePublished;
    }

    /**
     * @inheritdoc
     */
    public function getVariationId()
    {
        return $this->variationId;
    }

    /**
     * @inheritdoc
     */
    public function getVariations()
    {
        return $this->variations;
    }

    /**
     * Sets the URL for the product page in the shop that shows this product.
     *
     * The URL must be absolute, i.e. must include the protocol http or https.
     *
     * Usage:
     * $product->setUrl("http://my.shop.com/products/example.html");
     *
     * @param string $url the url.
     */
    public function setUrl($url)
    {
        $this->url = $url;
    }

    /**
     * Sets the product ID from given product.
     *
     * The ID must be either an integer above zero, or a non-empty string.
     *
     * Usage:
     * $product->setProductId(1);
     *
     * @param int|string $id the product ID.
     */
    public function setProductId($id)
    {
        $this->productId = $id;
    }

    /**
     * Sets the product name.
     *
     * The name must be a non-empty string.
     *
     * Usage:
     * $product->setName('Example');
     *
     * @param string $name the name.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the image URL for the product.
     *
     * The URL must be absolute, i.e. must include the protocol http or https.
     *
     * Usage:
     * $product->setImageUrl("http://my.shop.com/media/example.jpg");
     *
     * @param string $imageUrl the url.
     */
    public function setImageUrl($imageUrl)
    {
        $this->imageUrl = $imageUrl;
    }

    /**
     * Sets the thumbnail URL for the product.
     *
     * The URL must be absolute, i.e. must include the protocol http or https.
     *
     * Usage:
     * $product->setThumbUrl("http://my.shop.com/media/example.jpg");
     *
     * @param string $thumbUrl the url.
     */
    public function setThumbUrl($thumbUrl)
    {
        $this->thumbUrl = $thumbUrl;
    }

    /**
     * Sets the product price.
     *
     * The price must be a numeric value, represented as a value object of class `NostoPrice`.
     *
     * Usage:
     * $product->setPrice(new NostoPrice(99.99));
     *
     * @param NostoPrice $price the price.
     */
    public function setPrice(NostoPrice $price)
    {
        $this->price = $price;
    }

    /**
     * Sets the product list price.
     *
     * The price must be a numeric value, represented as a value object of class `NostoPrice`.
     *
     * Usage:
     * $product->setListPrice(new NostoPrice(99.99));
     *
     * @param NostoPrice $listPrice the price.
     */
    public function setListPrice(NostoPrice $listPrice)
    {
        $this->listPrice = $listPrice;
    }

    /**
     * Sets the currency code (ISO 4217) the product is sold in.
     *
     * The currency must be in ISO 4217 format, represented as a value object of class `NostoCurrencyCode`.
     *
     * Usage:
     * $product->setCurrency(new NostoCurrencyCode('USD'));
     *
     * @param NostoCurrencyCode $currency the currency code.
     */
    public function setCurrency(NostoCurrencyCode $currency)
    {
        $this->currency = $currency;
    }

    /**
     * Sets the availability state of the product.
     *
     * The availability of the product must be either "InStock" or "OutOfStock", represented as a value object of
     * class `NostoProductAvailability`.
     *
     * Usage:
     * $product->setAvailability(new NostoProductAvailability(NostoProductAvailability::IN_STOCK));
     *
     * @param NostoProductAvailability $availability the availability.
     */
    public function setAvailability(NostoProductAvailability $availability)
    {
        $this->availability = $availability;
    }

    /**
     * Sets all the tags to the `tag1` field.
     *
     * The tags must be an array of non-empty string values.
     *
     * Usage:
     * $product->setTag1(array('customTag1', 'customTag2'));
     *
     * @param array $tags the tags.
     */
    public function setTag1(array $tags)
    {
        $this->tags['tag1'] = array();
        foreach ($tags as $tag) {
            $this->addTag1($tag);
        }
    }

    /**
     * Adds a new tag to the `tag1` field.
     *
     * The tag must be a non-empty string value.
     *
     * Usage:
     * $product->addTag1('customTag');
     *
     * @param string $tag the tag to add.
     */
    public function addTag1($tag)
    {
        $this->tags['tag1'][] = $tag;
    }

    /**
     * Sets all the tags to the `tag2` field.
     *
     * The tags must be an array of non-empty string values.
     *
     * Usage:
     * $product->setTag2(array('customTag1', 'customTag2'));
     *
     * @param array $tags the tags.
     */
    public function setTag2(array $tags)
    {
        $this->tags['tag2'] = array();
        foreach ($tags as $tag) {
            $this->addTag2($tag);
        }
    }

    /**
     * Adds a new tag to the `tag2` field.
     *
     * The tag must be a non-empty string value.
     *
     * Usage:
     * $product->addTag2('customTag');
     *
     * @param string $tag the tag to add.
     */
    public function addTag2($tag)
    {
        $this->tags['tag2'][] = $tag;
    }

    /**
     * Sets all the tags to the `tag3` field.
     *
     * The tags must be an array of non-empty string values.
     *
     * Usage:
     * $product->setTag3(array('customTag1', 'customTag2'));
     *
     * @param array $tags the tags.
     */
    public function setTag3(array $tags)
    {
        $this->tags['tag3'] = array();
        foreach ($tags as $tag) {
            $this->addTag3($tag);
        }
    }

    /**
     * Adds a new tag to the `tag3` field.
     *
     * The tag must be a non-empty string value.
     *
     * Usage:
     * $product->addTag3('customTag');
     *
     * @param string $tag the tag to add.
     */
    public function addTag3($tag)
    {
        $this->tags['tag3'][] = $tag;
    }

    /**
     * Sets the product categories.
     *
     * The categories must be an array of objects implementing the `NostoCategoryInterface` interface.
     *
     * Usage:
     * $product->setCategories(array(NostoCategoryInterface $category [, ... ] ));
     *
     * @param NostoCategoryInterface[] $categories the categories.
     */
    public function setCategories(array $categories)
    {
        $this->categories = array();
        foreach ($categories as $category) {
            $this->addCategory($category);
        }
    }

    /**
     * Adds a category to the product.
     *
     * The category must implement the `NostoCategoryInterface` interface.
     *
     * Usage:
     * $product->addCategory(NostoCategoryInterface $category);
     *
     * @param NostoCategoryInterface $category the category.
     */
    public function addCategory(NostoCategoryInterface $category)
    {
        $this->categories[] = $category;
    }

    /**
     * Sets the product description.
     *
     * The description must be a non-empty string.
     *
     * Usage:
     * $product->setDescription('Lorem ipsum dolor sit amet... ');
     *
     * @param string $description the description.
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Sets the brand name of the product manufacturer.
     *
     * The name must be a non-empty string.
     *
     * Usage:
     * $product->setBrand('Example');
     *
     * @param string $brand the brand name.
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
    }

    /**
     * Sets the products published date.
     *
     * The date must be a UNIX timestamp, represented as a value object of class `NostoDate`.
     *
     * Usage:
     * $product->setDatePublished(new NostoDate(strtotime('2015-01-01 00:00:00')));
     *
     * @param NostoDate $date the date.
     */
    public function setDatePublished(NostoDate $date)
    {
        $this->datePublished = $date;
    }

    /**
     * Sets the product variation ID.
     *
     * The ID must be a non-empty string.
     *
     * Usage:
     * $product->setVariationId('USD');
     *
     * @param string $variationId the variation ID.
     */
    public function setVariationId($variationId)
    {
        $this->variationId = $variationId;
    }

    /**
     * Sets the product variations.
     *
     * The variations represent the possible product prices in different currencies and must implement the
     * `NostoProductPriceVariationInterface` interface.
     * This is only used in multi currency environments when the multi currency method is set to "priceVariations".
     *
     * Usage:
     * $product->setVariations(array(NostoProductPriceVariationInterface $variation [, ... ]))
     *
     * @param NostoProductPriceVariationInterface[] $variations the variations.
     */
    public function setVariations(array $variations)
    {
        $this->variations = array();
        foreach ($variations as $variation) {
            $this->addVariation($variation);
        }
    }

    /**
     * Adds a product variation.
     *
     * The variation represents the product price in another currency than the base currency, and must implement the
     * `NostoProductPriceVariationInterface` interface.
     * This is only used in multi currency environments when the multi currency method is set to "priceVariations".
     *
     * Usage:
     * $product->addVariation(NostoProductPriceVariationInterface $variation);
     *
     * @param NostoProductPriceVariationInterface $variation the variation.
     */
    public function addVariation(NostoProductPriceVariationInterface $variation)
    {
        $this->variations[] = $variation;
    }
}
