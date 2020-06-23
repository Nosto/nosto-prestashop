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
 * @copyright 2013-2020 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\NostoException;
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
     * @throws PrestaShopException
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopDatabaseException
     */
    public function loadData()
    {
        $shopId = NostoHelperContext::getShopId();
        $cacheKey = 'NostoVariationKeyCollection-loadData-' . $shopId;

        if (Cache::isStored($cacheKey)) {
            $this->var = Cache::retrieve($cacheKey);
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
            $groupFactor = NostoHelperVariation::getGroupsBeingUsedInSpecificPrices();

            $this->var = array();
            foreach ($currencyFactor as $currencyId) {
                foreach ($countryFactor as $countryId) {
                    foreach ($groupFactor as $groupId) {
                        $this->append(new NostoVariationKey($currencyId, $countryId, $groupId));
                    }
                }
            }

            NostoHelperHook::dispatchHookActionLoadAfter(get_class($this), array(
                'nosto_variation_key_collection' => $this
            ));

            /** @noinspection PhpParamsInspection */
            // @phan-suppress-next-line PhanTypeMismatchArgument
            Cache::store($cacheKey, $this->var);
        }
    }

    /**
     * Get default variation key
     * @return NostoVariationKey
     * @throws NostoException
     */
    public function getDefaultVariationKey()
    {
        /** @noinspection PhpUndefinedFieldInspection */
        $defaultVariationKey = new NostoVariationKey(
            NostoHelperCurrency::getBaseCurrency()->id,
            0,
            0
        );

        //In case the base currency is disabled in this shop, take the first one
        if (!$this->contains($defaultVariationKey)
            && $this->count() > 0
        ) {
            $defaultVariationKey = $this->var[0];
        }

        return $defaultVariationKey;
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
