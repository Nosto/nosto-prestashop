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

use Nosto\Object\Product\Sku as NostoSDKSku;

class NostoSku extends NostoSDKSku
{
    /**
     * Loads the product data from supplied context and product objects.
     *
     * @param Context Context::getContext() the context object.
     * @param Product $product magento product object
     * @param NostoProduct $nostoProduct
     * @param Combination $combination the prestashop combination object
     * @param array|null $attributesGroup
     * @return NostoSku|null
     */
    public static function loadData(
        Product $product,
        NostoProduct $nostoProduct,
        Combination $combination,
        array $attributesGroup
    ) {
        if (!Validate::isLoadedObject($combination)) {
            return null;
        }
        $langId = Context::getContext()->language->id;
        $shopId = null;
        $shopGroupId = null;
        if (Context::getContext()->shop instanceof Shop) {
            $shopId = Context::getContext()->shop->id;
        }

        $nostoSku = new NostoSku();
        $nostoSku->amendAvailability($attributesGroup);
        $nostoSku->setId($combination->id);
        $nostoSku->amendImage($combination, $nostoProduct, $langId, $shopGroupId, $shopId);
        $nostoSku->amendCustomFields($combination, $langId, $shopId);
        $nostoSku->amendPrice($combination);
        $nostoSku->amendName($combination, $langId);

        $nostoSku->setGtin($combination->ean13);
        $nostoSku->setUrl(NostoHelperUrl::getProductUrl($product, $langId, $shopId, array(), $combination->id));

        return $nostoSku;
    }

    /**
     * Amend price
     *
     * @param Combination $combination
     */
    protected function amendPrice(Combination $combination)
    {
        $base_currency = NostoHelperCurrency::getBaseCurrency(Context::getContext());
        if (Nosto::useMultipleCurrencies()) {
            $tagging_currency = $base_currency;
        } else {
            $tagging_currency = Context::getContext()->currency;
        }

        $this->setListPrice(self::getListPriceInclTax($combination, $tagging_currency));
        $this->setPrice(self::getPriceInclTax($combination, $tagging_currency));
    }

    /**
     * Amend custom fields
     *
     * @param Combination $combination
     * @param $langId
     * @param $shopId
     */
    protected function amendCustomFields(Combination $combination, $langId, $shopId)
    {
        $attributes = $combination->getAttributesName($langId);
        foreach ($attributes as $attributesInfo) {
            $attributeId = $attributesInfo['id_attribute'];
            $attribute = new Attribute($attributeId, $langId, $shopId);
            $attributeName = $attributesInfo['name'];
            $attributeGroup = new AttributeGroup($attribute->id_attribute_group, $langId, $shopId);

            $this->addCustomField($attributeGroup->name, $attributeName);
        }
    }

    /**
     * Amend sku name
     *
     * @param Combination $combination
     * @param $langId
     */
    protected function amendName(Combination $combination, $langId)
    {
        $nameArray = $combination->getAttributesName($langId);
        if ($nameArray) {
            $names = array();
            foreach ($nameArray as $nameInfo) {
                $names[] = $nameInfo['name'];
            }

            $this->setName(implode('-', $names));
        }
    }

    /**
     * Returns the absolute product image url
     *
     * @param Combination $combination the product model.
     * @param NostoProduct $nostoProduct
     * @param int $langId language id of the context
     * @param int $shopGroupId
     * @param int $shopId
     */
    protected function amendImage(
        Combination $combination,
        NostoProduct $nostoProduct,
        $langId,
        $shopGroupId,
        $shopId
    )
    {
        $images = $combination->getWsImages();
        if ($images && is_array($images)) {
            foreach ($images as $image) {
                if (!is_array($image) || !array_key_exists('id', $image)) {
                    continue;
                }

                $imageId = $image['id'];
                if ((int)$imageId > 0) {
                    $imageType = NostoHelperImage::getTaggingImageTypeName($langId, $shopGroupId, $shopId);
                    if (empty($imageType)) {
                        return;
                    }

                    $product = new Product($combination->id_product, $langId, $shopId);
                    $link = NostoHelperLink::getLink();
                    $url = $link->getImageLink(
                        $product->link_rewrite,
                        $combination->id_product . '-' . $imageId,
                        $imageType
                    );
                    if ($url) {
                        $this->setImageUrl($url);
                        //image url found, break from loop
                        break;
                    }
                }
            }
        }

        if (!$this->getImageUrl()) {
            $this->setImageUrl($nostoProduct->getImageUrl());
        }
    }

    /**
     * Get availability
     *
     * @param $attributesGroup
     */
    public function amendAvailability($attributesGroup)
    {
        if (array_key_exists('quantity', $attributesGroup)) {
            $this->setAvailable($attributesGroup['quantity'] > 0);
        } else {
            $this->setAvailable(false);
        }
    }

    /**
     * Returns the product price including discounts and taxes for the given currency.
     *
     * @param Combination $combination the product.
     * @param Currency|CurrencyCore $currency the currency.
     * @return float the price.
     */
    public static function getPriceInclTax(Combination $combination, Currency $currency)
    {
        return NostoHelperPrice::calcPrice(
            $combination->id_product,
            $currency,
            Context::getContext(),
            array('user_reduction' => true, 'id_product_attribute' => $combination->id)
        );
    }

    /**
     * Returns the product list price including taxes for the given currency.
     *
     * @param Combination $combination the product.
     * @param Currency|CurrencyCore $currency the currency.
     * @return float the price.
     */
    public static function getListPriceInclTax(Combination $combination, Currency $currency)
    {
        return NostoHelperPrice::calcPrice(
            $combination->id_product,
            $currency,
            Context::getContext(),
            array('user_reduction' => false, 'id_product_attribute' => $combination->id)
        );
    }
}
