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
use Nosto\Model\Product\VariationCollection as NostoSDKVariationCollection;
use Nosto\Types\Product\ProductInterface as NostoSDKProductInterface;

class NostoVariationCollection extends NostoSDKVariationCollection
{
    /**
     * Build price variations.
     *
     * @param Product $product
     * @param string $productAvailability
     * @throws NostoException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     */
    public function loadData(
        Product $product,
        $productAvailability
    ) {
        $keyCollection = new NostoVariationKeyCollection();
        $keyCollection->loadData();
        $defaultVariationKey = $keyCollection->getDefaultVariationKey();

        $hasTaxRules = false;
        if (!NostoHelperConfig::getVariationTaxRuleEnabled()) {
            $taxRuleGroupId = $product->getIdTaxRulesGroup();
            if ($taxRuleGroupId) {
                $taxRules = TaxRule::getTaxRulesByGroupId(NostoHelperContext::getLanguageId(), $taxRuleGroupId);
                $hasTaxRules = is_array($taxRules) && count($taxRules) > 0;
            }
        }

        //Always put the default variation to first one
        $this->append(
            $this->buildVariation($product, $productAvailability, $hasTaxRules, $defaultVariationKey)
        );

        /** @var NostoVariationKey $variationKey */
        foreach ($keyCollection as $variationKey) {
            //skip the default
            if ($defaultVariationKey !== $variationKey) {
                $this->append(
                    $this->buildVariation($product, $productAvailability, $hasTaxRules, $variationKey)
                );
            }
        }
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    private function buildVariation(
        Product $product,
        $productAvailability,
        $hasTaxRules,
        NostoVariationKey $variationKey
    ) {
        $variation = NostoVariation::loadData($product, $variationKey, $productAvailability);
        //For those products having tax rules and tax rules are not being used for the variations,
        //set the variations with "ANY" country to OutOfStock because the prices are variant for other countries
        if ($hasTaxRules && $variationKey->getCountryId() === 0) {
            $variation->setAvailability(NostoSDKProductInterface::OUT_OF_STOCK);
        }

        return $variation;
    }

    /**
     * Take the first of the collection and return it
     * @return NostoVariation return the first value, or null if collection is empty
     */
    public function shift()
    {
        return array_shift($this->var);
    }
}
