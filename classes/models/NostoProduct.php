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
class NostoProduct extends Nosto\Object\Product\Product
{
    /**
     * Loads the product data from supplied context and product objects.
     *
     * @param Context $context the context object.
     * @param Product $product the product object.
     * @return NostoProduct
     */
    public static function loadData(Context $context, Product $product)
    {
        if (!Validate::isLoadedObject($product)) {
            return null;
        }

        /** @var NostoTaggingHelperUrl $url_helper */
        $url_helper = Nosto::helper('nosto_tagging/url');
        /** @var NostoTaggingHelperCurrency $helper_currency */
        $helper_currency = Nosto::helper('nosto_tagging/currency');

        $nostoProduct = new NostoProduct();
        $base_currency = $helper_currency->getBaseCurrency($context);
        $id_lang = $context->language->id;
        $id_shop = null;
        $id_shop_group = null;
        if ($context->shop instanceof Shop) {
            $id_shop = $context->shop->id;
            $id_shop_group = $context->shop->id_shop_group;
        }

        if (NostoTaggingHelperConfig::useMultipleCurrencies($id_lang, $id_shop_group, $id_shop) === true) {
            $nostoProduct->setVariationId($base_currency->iso_code);
            $tagging_currency = $base_currency;
        } else {
            $tagging_currency = $context->currency;
        }
        $nostoProduct->setUrl($url_helper->getProductUrl($product, $id_lang, $id_shop));
        $nostoProduct->setProductId((string)$product->id);
        $nostoProduct->setName($product->name);
        $nostoProduct->setPriceCurrencyCode(Tools::strtoupper($tagging_currency->iso_code));
        $nostoProduct->setAvailability(self::checkAvailability($product));
        $nostoProduct->setTag1(self::buildTags($product, $id_lang));
        $nostoProduct->amendCategories($product, $id_lang);
        $nostoProduct->setDescription($product->description_short . $product->description);
        $nostoProduct->setInventoryLevel((int)$product->quantity);
        $nostoProduct->setPrice(self::getPriceInclTax($product, $context, $tagging_currency));
        $nostoProduct->setListPrice(self::getListPriceInclTax($product, $context, $tagging_currency));
        $nostoProduct->amendBrand($product, $id_lang);
        $nostoProduct->amendImage($product, $id_lang);
        $nostoProduct->amendAlternateImages($product, $id_lang);
        $nostoProduct->amendPrices($product);

        Hook::exec(
            'action' . str_replace('NostoTagging', 'Nosto', self::class) . 'LoadAfter',
            array(
                'nosto_product' => $nostoProduct,
                'product' => $product,
                'context' => $context
            )
        );

        return $nostoProduct;
    }

    /**
     * Sets the prices for the product
     *
     * @param Product $product
     */
    protected function amendPrices(Product $product)
    {
        $supplier_cost = NostoHelperPrice::getProductWholesalePriceInclTax($product);
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
        $images = Image::getImages((int)$id_lang, (int)$product->id);
        foreach ($images as $image) {
            $image_type = NostoHelperImage::getTaggingImageTypeName($id_lang);
            if (empty($image_type)) {
                return;
            }

            $link = NostoHelperLink::getLink();
            $url = $link->getImageLink($product->link_rewrite, $image['id_image'], $image_type);
            if ($url) {
                $this->addAlternateImageUrls($url);
            }
        }
    }

    /**
     * Returns the absolute product image url of the primary image.
     *
     * @param Product|ProductCore $product the product model.
     * @param int $id_lang language id of the context
     */
    protected function amendImage($product, $id_lang)
    {
        $image_id = $product->getCoverWs();
        if ((int)$image_id > 0) {
            $image_type = NostoHelperImage::getTaggingImageTypeName($id_lang);
            if (empty($image_type)) {
                return;
            }

            $link = NostoHelperLink::getLink();
            $url = $link->getImageLink($product->link_rewrite, $product->id . '-' . $image_id,
                $image_type);
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
     * @param Context|ContextCore $context the context.
     * @param Currency|CurrencyCore $currency the currency.
     * @return float the price.
     */
    public static function getPriceInclTax(Product $product, Context $context, Currency $currency)
    {
        return NostoHelperPrice::calcPrice($product->id, $currency, $context,
            array('user_reduction' => true));
    }

    /**
     * Returns the product list price including taxes for the given currency.
     *
     * @param Product|ProductCore $product the product.
     * @param Context|ContextCore $context the context.
     * @param Currency|CurrencyCore $currency the currency.
     * @return float the price.
     */
    public static function getListPriceInclTax(Product $product, Context $context, Currency $currency)
    {
        return NostoHelperPrice::calcPrice($product->id, $currency, $context,
            array('user_reduction' => false));
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
     * @param int $id_lang for which language ID to fetch the product tags.
     * @return array the built tags.
     */
    protected static function buildTags(Product $product, $id_lang)
    {
        $tags = array();
        if (($product_tags = $product->getTags($id_lang)) !== '') {
            $tags = explode(', ', $product_tags);
        }

        // If the product has no attributes (color, size etc.), then we mark
        // it as possible to add directly to cart.
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
     */
    protected function amendCategories(Product $product, $id_lang)
    {
        $productCategories = $product->getCategories();
        foreach ($productCategories as $category_id) {
            $category = new Category((int)$category_id, $id_lang);
            $category = NostoCategory::loadData(Context::getContext(), $category);
            if (!empty($category)) {
                $this->addCategory($category);
            }
        }
    }

    /**
     * Builds the brand name from the product's manufacturer to and returns them.
     *
     * @param Product $product the product model.
     * @param int $id_lang for which language ID to fetch the categories.
     */
    protected function amendBrand(Product $product, $id_lang)
    {
        if (empty($product->manufacturer_name) && !empty($product->id_manufacturer)) {
            $manufacturer = new Manufacturer($product->id_manufacturer, $id_lang);
            if (!empty($manufacturer)) {
                $this->setBrand($manufacturer->name);
            }
        } else {
            $this->setBrand($product->manufacturer_name);
        }
    }
}
