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
 * Helper class for price rule related tasks.
 */
class NostoHelperVariation
{
    const CURRENCY = 'id_currency';
    const COUNTRY = 'id_country';
    const GROUP = 'id_group';

    /**
     * Returns an array of tax rule groups that are assigned to any product
     *
     * @return array
     */
    public static function getCountriesBeingUsedInTaxRules($shopId = null)
    {
        $cache = Cache::getInstance();
        $cacheKey = 'NostoHelperPriceVariation-getCountriesBeingUsedInTaxRules-' . $shopId;
        if ($cache->exists($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $res = array();
        $innerJoinShop = '';
        $shopFilter = '';
        if ($shopId) {
            $innerJoinShop = sprintf(
                'INNER JOIN %stax_rules_group trg ON (p.id_tax_rules_group = trg.id_tax_rules_group)',
                _DB_PREFIX_
            );
            $shopFilter = sprintf(
                'AND trgs.id_shop = %s',
                $shopId
            );
        }

        $sql = sprintf(
            "
                SELECT 
                    DISTINCT tr.id_country
                FROM 
                    %sproduct p
                INNER JOIN
					%stax_rules_group trg ON (p.id_tax_rules_group = trg.id_tax_rules_group)
				$innerJoinShop
                INNER JOIN
					%stax_rule tr ON trg.id_tax_rules_group = tr.id_tax_rules_group					
				INNER JOIN 
				    %scountry c ON c.id_country = tr.id_country
                WHERE
                    trg.active = 1 AND c.active = 1 $shopFilter
           ",
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_,
            _DB_PREFIX_
        );

        $rows = Db::getInstance()->executeS($sql);
        foreach ($rows as $row) {
            $res[] = $row['id_country'];
        }

        //cache for 10 minutes
        $cache->set($cache, $res, 600);
        
        return $res;
    }

    public static function getAllCountriesAndGroupFromSpecificPrices($shopId = null)
    {
        $cache = Cache::getInstance();
        $cacheKey = 'NostoHelperPriceVariation-getAllCountriesAndGroupFromSpecificPrices-' . $shopId;
        if ($cache->exists($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $res = array(
            'country' => array(0),
            'group' => array(0)
        );

        $result = Db::getInstance()->executeS(
            'SELECT DISTINCT id_country, id_group FROM `' . _DB_PREFIX_ . 'specific_price` '
            . ($shopId != null ? ' WHERE `id_shop` = ' . (int)$shopId : '')
        );
        if ($result && is_array($result)) {
            $countryIds = array();
            $groupIds = array();
            foreach ($result as $row) {
                $countryIds[] = $row['id_country'];
                $groupIds[] = $row['id_group'];

            }
            $countryIds = array_unique($countryIds);
            $groupIds = array_unique($groupIds);
            //make sure it always has 'any'
            if (!in_array(0, $countryIds)) {
                $countryIds[] = 0;
            }
            //make sure it always has 'any'
            if (!in_array(0, $groupIds)) {
                $groupIds[] = 0;
            }

            $res = array(
                'country' => $countryIds,
                'group' => $groupIds
            );
        }

        //cache for 10 minutes
        $cache->set($cache, $res, 600);

        return $res;
    }

    public static function getVariationCountries($shopId)
    {
        $cache = Cache::getInstance();
        $cacheKey = 'NostoHelperPriceVariation-getVariationCountries-' . $shopId;
        if ($cache->exists($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $countryIds = self::getAllCountriesAndGroupFromSpecificPrices($shopId)['country'];
        $countryIdsFromTaxRules = self::getCountriesBeingUsedInTaxRules($shopId);
        $countryIds = array_unique(array_merge($countryIds, $countryIdsFromTaxRules));
        //cache for 10 minutes
        $cache->set($cache, $countryIds, 600);

        return $countryIds;
    }

    public static function getAllVariationKeys($shopId = null)
    {
        $cache = Cache::getInstance();
        $cacheKey = 'NostoHelperPriceVariation-getVariationCountries-' . $shopId;
        if ($cache->exists($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $currencyFactor = array();
        $allCurrencies = NostoHelperCurrency::getCurrencies(true);
        /** @var array $currency */
        foreach ($allCurrencies as $currency) {
            $currencyFactor[] = $currency['id_currency'];
        }
        if (empty($currencyFactor)) {
            $currencyFactor[] = 0;
        }

        $countryFactor = self::getVariationCountries($shopId = null);

        $groupFactor = self::getAllCountriesAndGroupFromSpecificPrices($shopId)['group'];

        $variationKeys = array();
        foreach ($currencyFactor as $currencyId) {
            foreach ($countryFactor as $countryId) {
                foreach ($groupFactor as $groupId) {
                    $variationKeys[] = array(
                        'id_currency' => $currencyId,
                        'id_country' => $countryId,
                        'id_group' => $groupId
                    );
                }
            }
        }

        //cache for 10 minutes
        $cache->set($cache, $variationKeys, 600);

        return $variationKeys;
    }

    public static function getAllVariationIds($shopId = null)
    {
        $variationKeys = self::getAllVariationKeys($shopId);
        $variationIds = array();
        foreach ($variationKeys as $variationKey) {
            $variationIds[] = self::getVariationIdFromCountryCurrency(
                $variationKey['id_currency'],
                $variationKey['id_country'],
                $variationKey['id_group']
            );
        }

        return $variationIds;
    }

    public static function getVariationIdFromCountryCurrency($currencyId, $countryId, $customerGroupId)
    {
        if ($countryId == 0) {
            $countryCode = 'ANY';
        } else {
            $country = new Country($countryId);
            $countryCode = $country->iso_code;
        }

        if ($currencyId == 0) {
            $currencyCode = 'ANY';
        } else {
            $currency = new Currency($currencyId);
            $currencyCode = $currency->iso_code;
        }

        if ($customerGroupId == 0) {
            $customerGroupName = 'ANY';
        } else {
            $group = new Group($customerGroupId);
            if (is_array($group->name) && array_key_exists(NostoHelperContext::getLanguageId(), $group->name)) {
                $customerGroupName = $group->name[NostoHelperContext::getLanguageId()];
            } else {
                $customerGroupName = $group->name;
            }
        }

        return strtoupper($currencyCode . '-' . $countryCode . '-' . $customerGroupName);
    }

}
