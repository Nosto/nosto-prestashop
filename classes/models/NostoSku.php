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

        $nostoSku = new NostoSku();
        $nostoSku->amendAvailability($attributesGroup);
        $nostoSku->setId($combination->id);
        $nostoSku->amendImage($combination, $nostoProduct);
        $nostoSku->amendCustomFields($combination);
        $nostoSku->amendPrice($combination);
        $nostoSku->amendName($combination);

        $nostoSku->setGtin($combination->ean13);
        $nostoSku->setUrl(
            NostoHelperUrl::getProductUrl(
                $product, NostoHelperContext::getLanguageIdFromContext(),
                NostoHelperContext::getShopIdFromContext(),
                array(),
                $combination->id
            )
        );

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
     */
    protected function amendCustomFields(Combination $combination)
    {
        $attributes = $combination->getAttributesName(
            NostoHelperContext::getLanguageIdFromContext()
        );
        foreach ($attributes as $attributesInfo) {
            $attributeId = $attributesInfo['id_attribute'];
            $attribute = new Attribute(
                $attributeId,
                NostoHelperContext::getLanguageIdFromContext(),
                NostoHelperContext::getShopIdFromContext());
            $attributeName = $attributesInfo['name'];
            $attributeGroup = new AttributeGroup(
                $attribute->id_attribute_group,
                NostoHelperContext::getLanguageIdFromContext(),
                NostoHelperContext::getShopIdFromContext()
            );

            $this->addCustomField($attributeGroup->name, $attributeName);
        }
    }

    /**
     * Amend sku name
     *
     * @param Combination $combination
     */
    protected function amendName(Combination $combination)
    {
        $nameArray = $combination->getAttributesName(NostoHelperContext::getLanguageIdFromContext());
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
     */
    protected function amendImage(
        Combination $combination,
        NostoProduct $nostoProduct
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
                    $imageType = NostoHelperImage::getTaggingImageTypeName(
                        NostoHelperContext::getLanguageIdFromContext(),
                        NostoHelperContext::getShopGroupIdFromContext(),
                        NostoHelperContext::getShopIdFromContext()
                    );
                    if (empty($imageType)) {
                        return;
                    }

                    $product = new Product(
                        $combination->id_product,
                        NostoHelperContext::getLanguageIdFromContext(),
                        NostoHelperContext::getShopIdFromContext()
                    );
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
