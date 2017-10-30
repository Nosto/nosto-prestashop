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

use Nosto\Object\AbstractCollection as NostoSDKAbstractCollection;

class NostoVariationKeyCollection extends NostoSDKAbstractCollection
{
    /**
     * Appends item to the collection of variationIds
     *
     * @param NostoVariationKey $variationId
     */
    public function append(NostoVariationKey $variationId)
    {
        $this->var[] = $variationId;
    }

    /**
     * Load variation Ids
     */
    public function loadData()
    {
        $shopId = NostoHelperContext::getShopId();
        $cache = Cache::getInstance();
        $cacheKey = 'NostoHelperPriceVariation-getVariationCountries-' . $shopId;
        if ($cache->exists($cacheKey)) {
            $this->var = $cache->get($cacheKey);
        } else {
            $currencyFactor = array();
            $allCurrencies = NostoHelperCurrency::getCurrencies(true);
            /** @var array $currency */
            foreach ($allCurrencies as $currency) {
                $currencyFactor[] = $currency[NostoHelperVariation::ID_CURRENCY];
            }
            if (empty($currencyFactor)) {
                $currencyFactor[] = 0;
            }

            $countryFactor = NostoHelperVariation::getVariationCountries();

            $groupFactor = NostoHelperVariation::getAllCountriesAndGroupsFromSpecificPrices()[
                NostoHelperVariation::GROUP
            ];

            $this->var = array();
            foreach ($currencyFactor as $currencyId) {
                foreach ($countryFactor as $countryId) {
                    foreach ($groupFactor as $groupId) {
                        $this->append(new NostoVariationKey($currencyId, $countryId, $groupId));
                    }
                }
            }
        }

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($this), array(
            'nosto_variation_key_collection' => $this
        ));

        //cache for 10 minutes
        $cache->set($cache, $this->var, NostoHelperVariation::CACHE_TIMEOUT);
    }

    /**
     * @param NostoVariationKey $variationKey
     * @return bool
     */
    public function contains(NostoVariationKey $variationKey)
    {
        return in_array($variationKey, $this->var);
    }

    /**
     * @param array $variationIds
     */
    public function setVariationIds($variationIds)
    {
        $this->var = $variationIds;
    }
}
