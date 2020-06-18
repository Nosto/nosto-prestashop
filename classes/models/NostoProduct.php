<?php
/**
 * 2013-2020 Nosto Solutions Ltd
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
 * @copyright 2013-2019 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\Object\Product\Product as NostoSDKProduct;

class NostoProduct extends NostoSDKProduct
{
    const NAME = 'name';
    const VALUE = 'value';

    /**
     * Loads the product data from supplied context and product objects.
     *
     * @param Product $product the product model to process
     * @return NostoProduct|null the product object
     */
    public static function loadData(Product $product)
    {
        if (!Validate::isLoadedObject($product)) {
            return null;
        }

        $nostoProduct = new NostoProduct();

        $nostoProduct->setUrl(NostoHelperUrl::getProductUrl($product));
        $nostoProduct->setProductId((string)$product->id);
        $nostoProduct->setName($product->name);
        $nostoProduct->setAvailability(self::checkAvailability($product));
        $nostoProduct->amendTags($product);
        $nostoProduct->amendCategories($product);
        $nostoProduct->setDescription($product->description_short . $product->description);
        $nostoProduct->setInventoryLevel((int)$product->quantity);
        $nostoProduct->amendBrand($product);
        $nostoProduct->amendImage($product);
        $nostoProduct->amendAlternateImages($product);
        $nostoProduct->amendSupplierCost($product);
        $nostoProduct->amendCustomFields($product);

        if (NostoHelperConfig::getVariationEnabled()) {
            $nostoProduct->amendVariation($product);
        } else {
            $taggingCurrency = NostoHelperCurrency::getBaseCurrency();
            $nostoProduct->setPriceCurrencyCode(Tools::strtoupper($taggingCurrency->iso_code));
            $nostoProduct->setPrice(self::getPriceInclTax($product, $taggingCurrency));
            $nostoProduct->setListPrice(self::getListPriceInclTax($product, $taggingCurrency));
            if (NostoHelperConfig::useMultipleCurrencies()) {
                $nostoProduct->setVariationId($taggingCurrency->iso_code);
            }
        }

        if (NostoHelperConfig::getSkuEnabled()) {
            $nostoProduct->amendSkus($product);
        }

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoProduct), array(
            'product' => $product,
            'nosto_product' => $nostoProduct
        ));

        return $nostoProduct;
    }

    protected function amendCustomFields(Product $product)
    {
        $features = $product->getFrontFeatures(NostoHelperContext::getLanguageId());
        if ($features) {
            foreach ($features as $feature) {
                if (array_key_exists(self::NAME, $feature)
                    && array_key_exists(self::VALUE, $feature)
                ) {
                    $this->addCustomField($feature[self::NAME], $feature[self::VALUE]);
                }
            }
        }
    }

    protected function amendVariation($product)
    {
        $variations = new NostoVariationCollection();
        $variations->loadData($product, $this->getAvailability());
        //Take the first variation as the default variation
        if ($variations->count() > 0) {
            $defaultVariation = $variations->shift();
            $this->setVariationId($defaultVariation->getVariationId());
            $this->setPrice($defaultVariation->getPrice());
            $this->setListPrice($defaultVariation->getListPrice());
            $this->setPriceCurrencyCode($defaultVariation->getPriceCurrencyCode());
            $this->setAvailability($defaultVariation->getAvailability());
        }
        $this->setVariations($variations);
    }

    /**
     * Sets the prices for the product
     *
     * @param Product $product
     */
    protected function amendSupplierCost(Product $product)
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
        $images = $product->getImages((int)NostoHelperContext::getLanguageId());
        foreach ($images as $image) {
            $url = NostoHelperLink::getImageLink($product->link_rewrite, $image['id_image']);
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
        $attributesGroups = $product->getAttributesGroups(NostoHelperContext::getLanguageId());
        $variants = array();
        foreach ($attributesGroups as $attributesGroup) {
            $variants[$attributesGroup['id_product_attribute']] = $attributesGroup;
        }

        $combinationIds = $product->getWsCombinations();
        foreach ($combinationIds as $combinationId) {
            //Before 1.6, Combination doesn't support language
            if (version_compare(_PS_VERSION_, '1.6') < 0) {
                $combination = new Combination($combinationId[NostoTagging::ID]);
            } else {
                $combination = new Combination($combinationId[NostoTagging::ID], NostoHelperContext::getLanguageId());
            }
            if ($combination->id === null) {
                NostoHelperLogger::info('Could not find combination with id:' . $combinationId[NostoTagging::ID]);
                continue;
            }
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
        $imageId = null;

        $defaultCombinationImages = array();
        $defaultId = $product->getDefaultIdProductAttribute();
        //Some merchants may have images associated with combination id 0.
        //Do not get the images for the combination if its id is 0
        if ($defaultId) {
            //The images for default combination
            $defaultCombinationImages = Product::_getAttributeImageAssociations($defaultId);
        }

        $coverImageId = $product->getCoverWs();
        if ((int)$coverImageId > 0) {
            //Take the cover image as the product image only if the cover image is enable for the default combination,
            //or the default combination doesn't have any image
            if (!$defaultCombinationImages || in_array($coverImageId, $defaultCombinationImages)) {
                $imageId = $coverImageId;
            }
        }

        //No image found, take the first from the default combination
        if (!$imageId && $defaultCombinationImages) {
            foreach ($defaultCombinationImages as $combinationImageId) {
                if ((int)$combinationImageId > 0) {
                    $imageId = $combinationImageId;
                    break;
                }
            }
        }

        //No image found, take the first from the product
        if (!$imageId) {
            //All the product images enabled for current shop
            $productImages = $product->getImages((int)NostoHelperContext::getLanguageId());
            if ($productImages) {
                foreach ($productImages as $productImage) {
                    if ((int)$productImage['id_image'] > 0) {
                        $imageId = $productImage['id_image'];
                        break;
                    }
                }
            }
        }

        if ((int)$imageId > 0) {
            $url = NostoHelperLink::getImageLink(
                $product->link_rewrite,
                $product->id . '-' . $imageId
            );
            $this->setImageUrl($url);
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
        if ($product->checkQty(1)) {
            return self::IN_STOCK;
        }
        // Note that for Prestashop 1.6 and below, the langID is a required parameter
        $combinations = $product->getAttributeCombinations(Context::getContext()->language->id);
        if (empty($combinations)) {
            return $product->checkQty(1) ? self::IN_STOCK : self::OUT_OF_STOCK;
        }
        foreach ($combinations as $combination) {
            if (array_key_exists('quantity', $combination) && $combination['quantity'] > 0) {
                return self::IN_STOCK;
            }
        }
        return self::OUT_OF_STOCK;
    }

    /**
     * Returns the product price including discounts and taxes for the given currency.
     *
     * @param Product $product the product.
     * @param Currency $currency the currency.
     * @return float the price.
     */
    public static function getPriceInclTax(Product $product, Currency $currency)
    {
        return NostoHelperPrice::calcPrice($product->id, $currency, true);
    }

    /**
     * Returns the product list price including taxes for the given currency.
     *
     * @param Product $product the product.
     * @param Currency $currency the currency.
     * @return float the price.
     */
    public static function getListPriceInclTax(Product $product, Currency $currency)
    {
        return NostoHelperPrice::calcPrice($product->id, $currency, false);
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
        if (($productTags = $product->getTags(NostoHelperContext::getLanguageId())) !== '') {
            $tags = explode(', ', $productTags);
            foreach ($tags as $tag) {
                $this->addTag1($tag);
            }
        }

        // If the product has no attributes (color, size etc.), then we mark
        // it as possible to add directly to cart.
        $productAttributes = $product->getAttributesGroups(NostoHelperContext::getLanguageId());
        if (empty($productAttributes)) {
            $this->addTag1(self::ADD_TO_CART);
        }
    }

    /**
     * Builds the category paths the product belongs to and returns them.
     *
     * By "path" we mean the full tree path of the products categories and sub-categories.
     *
     * @param Product $product the product model.
     *
     * @suppress PhanTypeMismatchArgument
     */
    protected function amendCategories(Product $product)
    {
        $productCategories = $product->getCategories();
        foreach ($productCategories as $categoryId) {
            $category = new Category((int)$categoryId, NostoHelperContext::getLanguageId());
            $nostoCategory = NostoCategory::loadData($category);
            if (!empty($nostoCategory)) {
                $this->addCategory($nostoCategory->getCategoryString());
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
        if (!empty($product->manufacturer_name)) {
            $this->setBrand($product->manufacturer_name);
        } elseif (!empty($product->id_manufacturer)) {
            $manufacturer = new Manufacturer($product->id_manufacturer, NostoHelperContext::getLanguageId());
            if (!empty($manufacturer) && !empty($manufacturer->name)) {
                $this->setBrand($manufacturer->name);
            }
        }
    }
}
