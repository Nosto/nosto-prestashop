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
    const ANY = 'ANY';
    const ID_COUNTRY = 'id_country';
    const ID_GROUP = 'id_group';
    const ID_CURRENCY = 'id_currency';
    const COUNTRY = 'country';
    const GROUP = 'group';
    /** cache timeout in second */
    const CACHE_TIMEOUT = 600;

    /**
     * Returns an array of country ids being used in tax rule groups that are assigned to any product
     *
     * @return array of country ids
     */
    public static function getCountriesBeingUsedInTaxRules()
    {
        $shopId = NostoHelperContext::getShopId();
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
                'INNER JOIN %stax_rules_group_shop trgs ON '
                . '(trg.id_tax_rules_group = trgs.id_tax_rules_group)',
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
            $res[] = $row[self::ID_COUNTRY];
        }

        //cache for 10 minutes
        $cache->set($cacheKey, $res, self::CACHE_TIMEOUT);

        return $res;
    }

    /**
     * Get countries and groups are used in the specific prices, including
     * product specific prices and catalog price rules
     * @return array with 2 keys: 'country', 'group'
     */
    public static function getAllCountriesAndGroupsFromSpecificPrices()
    {
        $shopId = NostoHelperContext::getShopId();
        $cache = Cache::getInstance();
        $cacheKey = 'NostoHelperPriceVariation-getAllCountriesAndGroupsFromSpecificPrices-' . $shopId;
        if ($cache->exists($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $res = array(
            self::COUNTRY => array(0),
            '' . self::GROUP . '' => array(0)
        );

        $filter = $shopId ? ' WHERE `id_shop` = ' . (int)$shopId : '';
        $result = Db::getInstance()->executeS(
            sprintf('SELECT DISTINCT id_country, id_group FROM `%sspecific_price` ', _DB_PREFIX_)
            . $filter
        );
        if ($result && is_array($result)) {
            $countryIds = array();
            $groupIds = array();
            foreach ($result as $row) {
                $countryIds[] = $row[self::ID_COUNTRY];
                $groupIds[] = $row[self::ID_GROUP];

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
                self::COUNTRY => $countryIds,
                self::GROUP => $groupIds
            );
        }

        //cache for 10 minutes
        $cache->set($cacheKey, $res, self::CACHE_TIMEOUT);

        return $res;
    }

    /**
     * Get all the countries are used in tax rules and specific price
     * @return array of country ids
     */
    public static function getVariationCountries()
    {
        $shopId = NostoHelperContext::getShopId();
        $cache = Cache::getInstance();
        $cacheKey = 'NostoHelperPriceVariation-getVariationCountries-' . $shopId;
        if ($cache->exists($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $countryIds = self::getAllCountriesAndGroupsFromSpecificPrices()[self::COUNTRY];
        $countryIdsFromTaxRules = self::getCountriesBeingUsedInTaxRules();
        $countryIds = array_unique(array_merge($countryIds, $countryIdsFromTaxRules));
        //cache for 10 minutes
        $cache->set($cache, $countryIds, self::CACHE_TIMEOUT);

        return $countryIds;
    }
}
