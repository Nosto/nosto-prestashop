<?php
/**
 * 2013-2022 Nosto Solutions Ltd
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
 * @copyright 2013-2022 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\NostoException;
use Nosto\Model\Product\Sku as NostoSDKSku;

class NostoSku extends NostoSDKSku
{
    /**
     * Loads the product data from supplied context and product objects.
     *
     * @param Product $product magento product object
     * @param NostoProduct $nostoProduct
     * @param Combination $combination the prestashop combination object
     * @param array|null $attributesGroup
     * @return NostoSku|null
     * @throws NostoException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public static function loadData(
        Product $product,
        NostoProduct $nostoProduct,
        Combination $combination,
        $attributesGroup,
        $productImages
    ) {
        if (!Validate::isLoadedObject($combination)) {
            return null;
        }

        $nostoSku = new NostoSku();
        $nostoSku->amendAvailability($attributesGroup);
        $nostoSku->setId($combination->id);
        $nostoSku->amendImage($product, $combination, $nostoProduct, $productImages);
        $nostoSku->amendCustomFields($combination);
        $nostoSku->amendPrice($combination);
        $nostoSku->amendName($combination);

        $nostoSku->setGtin($combination->ean13);
        $nostoSku->setUrl(NostoHelperUrl::getProductUrl($product, array(), $combination->id));

        return $nostoSku;
    }

    /**
     * Amend price
     *
     * @param Combination $combination
     * @throws NostoException
     * @throws PrestaShopException
     */
    protected function amendPrice(Combination $combination)
    {
        $taggingCurrency = NostoHelperCurrency::getBaseCurrency();
        $this->setListPrice(self::getListPriceInclTax($combination, $taggingCurrency));
        $this->setPrice(self::getPriceInclTax($combination, $taggingCurrency));
    }

    /**
     * Amend custom fields
     *
     * @param Combination $combination
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    protected function amendCustomFields(Combination $combination)
    {
        $attributes = $combination->getAttributesName(
            NostoHelperContext::getLanguageId()
        );
        foreach ($attributes as $attributesInfo) {
            $attributeId = $attributesInfo['id_attribute'];

            if (version_compare(_PS_VERSION_, '8') < 0) {
                $attribute = new Attribute(
                    $attributeId,
                    NostoHelperContext::getLanguageId(),
                    NostoHelperContext::getShopId()
                );
            } else {
                /** @phan-suppress-next-next-line PhanUndeclaredClassMethod */
                /** @noinspection PhpUndefinedClassInspection */
                $attribute = new ProductAttribute(
                    $attributeId,
                    NostoHelperContext::getLanguageId(),
                    NostoHelperContext::getShopId()
                );
            }

            $attributeName = $attributesInfo['name'];
            $attributeGroup = new AttributeGroup(
            /** @phan-suppress-next-line PhanUndeclaredClassProperty */
                $attribute->id_attribute_group,
                NostoHelperContext::getLanguageId(),
                NostoHelperContext::getShopId()
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
        $nameArray = $combination->getAttributesName(NostoHelperContext::getLanguageId());
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
     * @param Product $product
     * @param Combination $combination the product model.
     * @param NostoProduct $nostoProduct
     */
    protected function amendImage(
        Product $product,
        Combination $combination,
        NostoProduct $nostoProduct,
        $productImages
    ) {
        $combinationImages = $combination->getWsImages();

        if ($productImages && is_array($productImages) && $combinationImages && is_array($combinationImages)) {

            foreach ($productImages as $productImage) {
                if (!is_array($productImage) || !array_key_exists(NostoTagging::ID, $productImage)) {
                    continue;
                }

                $productImageId = $productImage[NostoTagging::ID];

                foreach ($combinationImages as $image) {
                    if (!is_array($image) || !array_key_exists(NostoTagging::ID, $image)) {
                        continue;
                    }

                    $imageId = $image[NostoTagging::ID];

                    if ((int)$productImageId === (int)$imageId) {
                        $url = NostoHelperLink::getImageLink(
                            $product->link_rewrite,
                            $combination->id_product . '-' . $imageId
                        );
                        if ($url) {
                            $this->setImageUrl($url);
                            //image url found, break from both loops
                            break 2;
                        }
                    }
                }
            }
        }

        if (!$this->getImageUrl()) {

            $firstImageId = $productImages[0][NostoTagging::ID] ?? null;
            $url = $firstImageId
                ? NostoHelperLink::getImageLink($product->link_rewrite, $combination->id_product . '-' . $firstImageId)
                : $nostoProduct->getImageUrl();

            $this->setImageUrl($url);
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
     * @param Currency $currency the currency.
     * @return float the price.
     * @throws PrestaShopException
     */
    public static function getPriceInclTax(Combination $combination, Currency $currency)
    {
        return NostoHelperPrice::calcPrice($combination->id_product, $currency, true, $combination->id);
    }

    /**
     * Returns the product list price including taxes for the given currency.
     *
     * @param Combination $combination the product.
     * @param Currency $currency the currency.
     * @return float the price.
     * @throws PrestaShopException
     */
    public static function getListPriceInclTax(Combination $combination, Currency $currency)
    {
        return NostoHelperPrice::calcPrice($combination->id_product, $currency, false, $combination->id);
    }
}
