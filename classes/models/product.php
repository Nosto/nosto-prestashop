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

/**
 * Model for tagging products.
 */
class NostoTaggingProduct extends Nosto\Object\Product\Product
{
    /**
     * Loads the product data from supplied context and product objects.
     *
     * @param Context $context the context object.
     * @param Product $product the product object.
     */
    public function loadData(Context $context, Product $product)
    {
        if (!Validate::isLoadedObject($product)) {
            return;
        }

        /** @var NostoTaggingHelperUrl $url_helper */
        $url_helper = Nosto::helper('nosto_tagging/url');
        /** @var NostoTaggingHelperCurrency $helper_currency */
        $helper_currency = Nosto::helper('nosto_tagging/currency');
        /** @var NostoTaggingHelperConfig $helper_config */
        $helper_config = Nosto::helper('nosto_tagging/config');
        /** @var NostoTaggingHelperImage $helper_image */
        $helper_image = Nosto::helper('nosto_tagging/image');
        $base_currency = $helper_currency->getBaseCurrency($context);
        $id_lang = $context->language->id;
        $id_shop = null;
        $id_shop_group = null;
        if ($context->shop instanceof Shop) {
            $id_shop = $context->shop->id;
            $id_shop_group = $context->shop->id_shop_group;
        }

        if ($helper_config->useMultipleCurrencies($id_lang, $id_shop_group, $id_shop) === true) {
            $this->setVariationId($base_currency->iso_code);
            $tagging_currency = $base_currency;
        } else {
            $this->setVariationId(false);
            $tagging_currency= $context->currency;
        }
        $this->setUrl($url_helper->getProductUrl($product, $id_lang, $id_shop));
        $link = NostoTagging::buildLinkClass();
        $this->setImageUrl($helper_image->getProductImageUrl($product, $id_lang, $link));
        $this->setProductId((int)$product->id);
        $this->setName($product->name);

        $this->setPriceCurrencyCode(Tools::strtoupper($tagging_currency->iso_code));
        $this->setAvailability($this->checkAvailability($product));
        $this->setTag1($this->buildTags($product, $id_lang));
        $this->setCategories($this->buildCategories($product, $id_lang));
        $this->setDescription($product->description_short . $product->description);
        $this->setInventoryLevel((int)$product->quantity);
        $this->setBrand($this->buildBrand($product));
        $this->amendAlternateImages($product, $id_lang);
        $this->amendPrices($product, $context, $tagging_currency);

        Hook::exec(
            'action'.str_replace('NostoTagging', 'Nosto', get_class($this)).'LoadAfter',
            array(
                'nosto_product' => $this,
                'product' => $product,
                'context' => $context
            )
        );
    }

    /**
     * Sets the prices for the product
     *
     * @param Product $product
     * @param Context $context
     * @param Currency $currency
     */
    protected function amendPrices(Product $product, Context $context, Currency $currency)
    {
        /** @var NostoTaggingHelperPrice $helper_price */
        $helper_price = Nosto::helper('nosto_tagging/price');
        $this->setPrice(
            $helper_price->getProductPriceInclTax(
                $product,
                $context,
                $currency
            )
        );
        $this->setListPrice($helper_price->getProductListPriceInclTax(
                $product,
                $context,
                $currency
        ));
        $supplier_cost = $helper_price->getProductWholesalePriceInclTax($product);
        if ($supplier_cost !== null && is_numeric($supplier_cost)) {
            $this->setSupplierCost($supplier_cost);
        }
    }

    /**
     * Sets the alternate images for the product
     *
     * @param Product $product
     * @param $id_lang
     */
    protected function amendAlternateImages(Product $product, $id_lang)
    {
        /** @var NostoTaggingHelperImage $helper_image */
        $helper_image = Nosto::helper('nosto_tagging/image');
        $images = $helper_image->getAlternateProductImageUrls($product, $id_lang);
        foreach ($images as $image_url) {
            $this->addAlternateImageUrls($image_url);
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
        $this->setProductId((int)$product->id);
    }

    /**
     * Checks the availability of the product and returns the "availability constant".
     *
     * The product is considered available if it is visible in the shop and is in stock.
     *
     * @param Product $product the product model.
     * @return string the value, i.e. self::IN_STOCK or self::OUT_OF_STOCK.
     */
    protected function checkAvailability(Product $product)
    {
        if (
            !$product->active
            || $product->visibility === 'none') {
            return self::INVISIBLE;
        } else {
            return ($product->checkQty(1)) ? self::IN_STOCK : self::OUT_OF_STOCK;
        }
    }

    /**
     * Builds the tag list for the product.
     *
     * Also includes the custom "add-to-cart" tag if the product can be added to the shopping cart directly without
     * any action from the user, e.g. the product cannot have any variations or choices. This tag is then used in the
     * recommendations to render the "Add to cart" button for the product when it is recommended to a user.
     *
     * @param Product $product the product model.
     * @param int $id_lang for which language ID to fetch the product tags.
     * @return array the built tags.
     */
    protected function buildTags(Product $product, $id_lang)
    {
        $tags = array();
        if (($product_tags = $product->getTags($id_lang)) !== '') {
            $tags = explode(', ', $product_tags);
        }

        // If the product has no attributes (color, size etc.), then we mark it as possible to add directly to cart.
        $product_attributes = $product->getAttributesGroups($id_lang);
        if (empty($product_attributes)) {
            $tags[] = self::ADD_TO_CART;
        }

        return $tags;
    }

    /**
     * Builds the category paths the product belongs to and returns them.
     *
     * By "path" we mean the full tree path of the products categories and sub-categories.
     *
     * @param Product $product the product model.
     * @param int $id_lang for which language ID to fetch the categories.
     * @return array the built category paths.
     */
    protected function buildCategories(Product $product, $id_lang)
    {
        $categories = array();
        $productCategories = $product->getCategories();
        foreach ($productCategories as $category_id) {
            $category = NostoTaggingCategory::buildCategoryString($category_id, $id_lang);
            if (!empty($category)) {
                $categories[] = $category;
            }
        }
        return $categories;
    }

    /**
     * Builds the brand name from the product's manufacturer to and returns them.
     *
     * @param Product $product the product model.
     * @return string the built brand name.
     */
    protected function buildBrand(Product $product)
    {
        $manufacturer = null;
        if (empty($product->manufacturer_name) && !empty($product->id_manufacturer)) {
            $manufacturer = Manufacturer::getNameById($product->id_manufacturer);
        } else {
            $manufacturer = $product->manufacturer_name;
        }
        return $manufacturer;
    }
}
