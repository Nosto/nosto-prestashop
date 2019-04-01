<?php
/**
 * 2013-2019 Nosto Solutions Ltd
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

use Nosto\Object\Product\Variation as NostoSDKVariation;

class NostoVariation extends NostoSDKVariation
{
    /**
     * Loads the Variation info from a prestashop product model.
     *
     * @param Product $product the product model.
     * @param NostoVariationKey $variationKey
     * @param string $productAvailability
     * @return NostoVariation
     */
    public static function loadData(
        Product $product,
        $variationKey,
        $productAvailability
    ) {
        return NostoHelperContext::runInContext(
            function () use ($product, $variationKey, $productAvailability) {
                $product = new Product(
                    $product->id,
                    true,
                    NostoHelperContext::getLanguageId(),
                    NostoHelperContext::getShopId()
                );
                $nostoVariation = new NostoVariation();
                $variationId = $variationKey->getVariationId();
                $nostoVariation->setVariationId($variationId);
                $nostoVariation->setAvailability($productAvailability);
                $nostoVariation->setPriceCurrencyCode(
                    Tools::strtoupper(NostoHelperContext::getCurrency()->iso_code)
                );

                $nostoVariation->setListPrice(
                    NostoHelperPrice::getProductPriceForGroup(
                        $product->id,
                        $variationKey->getGroupId(),
                        false
                    )
                );
                $nostoVariation->setPrice(
                    NostoHelperPrice::getProductPriceForGroup(
                        $product->id,
                        $variationKey->getGroupId()
                    )
                );

                return $nostoVariation;
            },
            false,
            false,
            $variationKey->getCurrencyId(),
            false,
            $variationKey->getCountryId()
        );
    }
}
