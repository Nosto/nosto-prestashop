<?php
/**
 * 2013-2016 Nosto Solutions Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@nosto.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\Object\Product\Product as NostoSDKProduct;

class NostoProduct extends NostoSDKProduct
{
    /**
     * Loads the product data from supplied context and product objects.
     *
     * @param Product $product the product model to process
     * @return NostoProduct the product object
     */
    public static function loadData(Product $product)
    {
        if (!Validate::isLoadedObject($product)) {
            return null;
        }


        $nostoProduct = new NostoProduct();
        $base_currency = NostoHelperCurrency::getBaseCurrency();

        if (Nosto::useMultipleCurrencies()) {
            $nostoProduct->setVariationId($base_currency->iso_code);
            $tagging_currency = $base_currency;
        } else {
            $tagging_currency = Context::getContext()->currency;
        }
        $nostoProduct->setUrl(NostoHelperUrl::getProductUrl($product));
        $nostoProduct->setProductId((string)$product->id);
        $nostoProduct->setName($product->name);
        $nostoProduct->setPriceCurrencyCode(Tools::strtoupper($tagging_currency->iso_code));
        $nostoProduct->setAvailability(self::checkAvailability($product));
        $nostoProduct->amendTags($product);
        $nostoProduct->amendCategories($product);
        $nostoProduct->setDescription($product->description_short . $product->description);
        $nostoProduct->setInventoryLevel((int)$product->quantity);
        $nostoProduct->setPrice(self::getPriceInclTax($product, $tagging_currency));
        $nostoProduct->setListPrice(self::getListPriceInclTax($product, $tagging_currency));
        $nostoProduct->amendBrand($product);
        $nostoProduct->amendImage($product);
        $nostoProduct->amendAlternateImages($product);
        $nostoProduct->amendSkus($product);
        if (NostoHelperConfig::getSkuEnabled()) {
            $nostoProduct->amendPrices($product);
        }

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoProduct), array(
            'product' => $product,
            'nosto_product' => $nostoProduct
        ));

        return $nostoProduct;
    }

    /**
     * Sets the prices for the product
     *
     * @param Product $product
     */
    protected function amendPrices(Product $product)
    {
        $supplierCost = NostoHelperPrice::getProductWholesalePriceInclTax($product);
        if ($supplierCost !== null && is_numeric($supplierCost)) {
            $this->setSupplierCost($supplierCost);
        }
    }

    /**
     * Sets the alternate images for the product
     *
     * @param Product $product
     */
    protected function amendAlternateImages(Product $product)
    {
        $images = Image::getImages((int)NostoHelperContext::getLanguageId(), (int)$product->id);
        foreach ($images as $image) {
            $imageType = NostoHelperImage::getTaggingImageTypeName();
            if (empty($imageType)) {
                return;
            }

            $link = NostoHelperLink::getLink();
            $url = $link->getImageLink($product->link_rewrite, $image['id_image'], $imageType);
            if ($url) {
                $this->addAlternateImageUrls($url);
            }
        }
    }

    /**
     * Amend skus
     *
     * @param Product $product
     */
    protected function amendSkus(Product $product)
    {
        $attributes_groups = $product->getAttributesGroups(NostoHelperContext::getLanguageId());
        $variants = array();
        foreach ($attributes_groups as $attributes_group) {
            $variants[$attributes_group['id_product_attribute']] = $attributes_group;
        }

        $combinationIds = $product->getWsCombinations();
        foreach ($combinationIds as $combinationId) {
            $combination = new Combination($combinationId['id'], NostoHelperContext::getLanguageId());
            $this->addSku(NostoSku::loadData($product, $this, $combination, $variants[$combination->id]));
        }
    }

    /**
     * Returns the absolute product image url of the primary image.
     *
     * @param Product|ProductCore $product the product model.
     */
    protected function amendImage($product)
    {
        $image_id = $product->getCoverWs();
        if ((int)$image_id > 0) {
            $image_type = NostoHelperImage::getTaggingImageTypeName();
            if (empty($image_type)) {
                return;
            }

            $link = NostoHelperLink::getLink();
            $url = $link->getImageLink(
                $product->link_rewrite,
                $product->id . '-' . $image_id,
                $image_type
            );
            if ($url) {
                $this->setImageUrl($url);
            }
        }
    }

    /**
     * Assigns the product ID from given product.
     *
     * This method exists in order to expose a public API to change the ID.
     *
     * @param Product $product the product object.
     */
    public function assignId(Product $product)
    {
        $this->setProductId((string)$product->id);
    }

    /**
     * Checks the availability of the product and returns the "availability constant".
     *
     * The product is considered available if it is visible in the shop and is in stock.
     *
     * @param Product $product the product model.
     * @return string the value, i.e. self::IN_STOCK or self::OUT_OF_STOCK.
     */
    protected static function checkAvailability(Product $product)
    {
        if (!$product->active || $product->visibility === 'none') {
            return self::INVISIBLE;
        }
        return ($product->checkQty(1)) ? self::IN_STOCK : self::OUT_OF_STOCK;
    }

    /**
     * Returns the product price including discounts and taxes for the given currency.
     *
     * @param Product|ProductCore $product the product.
     * @param Currency|CurrencyCore $currency the currency.
     * @return float the price.
     */
    public static function getPriceInclTax(Product $product, Currency $currency)
    {
        return NostoHelperPrice::calcPrice(
            $product->id,
            $currency,
            array('user_reduction' => true)
        );
    }

    /**
     * Returns the product list price including taxes for the given currency.
     *
     * @param Product|ProductCore $product the product.
     * @param Currency|CurrencyCore $currency the currency.
     * @return float the price.
     */
    public static function getListPriceInclTax(Product $product, Currency $currency)
    {
        return NostoHelperPrice::calcPrice(
            $product->id,
            $currency,
            array('user_reduction' => false)
        );
    }

    /**
     * Builds the tag list for the product.
     *
     * Also includes the custom "add-to-cart" tag if the product can be added to the shopping cart
     * directly without any action from the user, e.g. the product cannot have any variations or
     * choices. This tag is then used in the recommendations to render the "Add to cart" button for
     * the product when it is recommended to a user.
     *
     * @param Product $product the product model.
     */
    protected function amendTags(Product $product)
    {
        if (($product_tags = $product->getTags(NostoHelperContext::getLanguageId())) !== '') {
            $tags = explode(', ', $product_tags);
            foreach ($tags as $tag) {
                $this->addTag1($tag);
            }
        }

        // If the product has no attributes (color, size etc.), then we mark
        // it as possible to add directly to cart.
        $product_attributes = $product->getAttributesGroups(NostoHelperContext::getLanguageId());
        if (empty($product_attributes)) {
            $this->addTag1(self::ADD_TO_CART);
        }
    }

    /**
     * Builds the category paths the product belongs to and returns them.
     *
     * By "path" we mean the full tree path of the products categories and sub-categories.
     *
     * @param Product $product the product model.
     */
    protected function amendCategories(Product $product)
    {
        $productCategories = $product->getCategories();
        foreach ($productCategories as $category_id) {
            $category = new Category((int)$category_id, NostoHelperContext::getLanguageId());
            $category = NostoCategory::loadData($category);
            if (!empty($category)) {
                $this->addCategory($category->getValue());
            }
        }
    }

    /**
     * Builds the brand name from the product's manufacturer to and returns them.
     *
     * @param Product $product the product model.
     */
    protected function amendBrand(Product $product)
    {
        if (empty($product->manufacturer_name) && !empty($product->id_manufacturer)) {
            $manufacturer = new Manufacturer($product->id_manufacturer, NostoHelperContext::getLanguageId());
            if (!empty($manufacturer)) {
                $this->setBrand($manufacturer->name);
            }
        } else {
            $this->setBrand($product->manufacturer_name);
        }
    }
}
