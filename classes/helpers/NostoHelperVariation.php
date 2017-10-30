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
            $res[] = $row[self::ID_COUNTRY];
        }

        //cache for 10 minutes
        $cache->set($cache, $res, self::CACHE_TIMEOUT);

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
        $cache->set($cache, $res, self::CACHE_TIMEOUT);

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

    /**
     * Get all the variation keys
     * @return array Each if the element is an array with 3 keys: id_currency, id_country, id_group
     */
    public static function getAllVariationKeys()
    {
        $shopId = NostoHelperContext::getShopId();
        $cache = Cache::getInstance();
        $cacheKey = 'NostoHelperPriceVariation-getVariationCountries-' . $shopId;
        if ($cache->exists($cacheKey)) {
            return $cache->get($cacheKey);
        }

        $currencyFactor = array();
        $allCurrencies = NostoHelperCurrency::getCurrencies(true);
        /** @var array $currency */
        foreach ($allCurrencies as $currency) {
            $currencyFactor[] = $currency[self::ID_CURRENCY];
        }
        if (empty($currencyFactor)) {
            $currencyFactor[] = 0;
        }

        $countryFactor = self::getVariationCountries();

        $groupFactor = self::getAllCountriesAndGroupsFromSpecificPrices()[self::GROUP];

        $variationKeys = array();
        foreach ($currencyFactor as $currencyId) {
            foreach ($countryFactor as $countryId) {
                foreach ($groupFactor as $groupId) {
                    $variationKeys[] = array(
                        self::ID_CURRENCY => $currencyId,
                        self::ID_COUNTRY => $countryId,
                        self::ID_GROUP => $groupId
                    );
                }
            }
        }

        //cache for 10 minutes
        $cache->set($cache, $variationKeys, self::CACHE_TIMEOUT);

        return $variationKeys;
    }

    /**
     * Get all the variation ids
     * @return array variation ids in string
     */
    public static function getAllVariationIds()
    {
        $variationKeys = self::getAllVariationKeys();
        $variationIds = array();
        foreach ($variationKeys as $variationKey) {
            $variationIds[] = self::getVariationId(
                $variationKey[self::ID_CURRENCY],
                $variationKey[self::ID_COUNTRY],
                $variationKey[self::ID_GROUP]
            );
        }

        return $variationIds;
    }

    /**
     * Get variation id from currency id, country id and group id
     * @param $currencyId
     * @param $countryId
     * @param $customerGroupId
     * @return string variation id
     */
    public static function getVariationId($currencyId, $countryId, $customerGroupId)
    {
        if ($countryId == 0) {
            $countryCode = self::ANY;
        } else {
            $country = new Country($countryId);
            $countryCode = $country->iso_code;
        }

        if ($currencyId == 0) {
            $currencyCode = self::ANY;
        } else {
            $currency = new Currency($currencyId);
            $currencyCode = $currency->iso_code;
        }

        if ($customerGroupId == 0) {
            $customerGroupName = self::ANY;
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
