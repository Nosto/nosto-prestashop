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

/**
 * Helper class for price rule related tasks.
 */
class NostoHelperVariation
{
    const ANY = 'ANY';
    const ID_COUNTRY = 'id_country';
    const ID_GROUP = 'id_group';
    const ID_CURRENCY = 'id_currency';
    const GROUP = 'group';

    /**
     * Returns an array of country ids being used in tax rule groups that are assigned to any product
     *
     * @return array of country ids
     */
    public static function getCountriesBeingUsedInTaxRules()
    {
        $shopId = NostoHelperContext::getShopId();
        $cacheKey = 'NostoHelperPriceVariation-getCountriesBeingUsedInTaxRules-' . $shopId;
        if (Cache::isStored($cacheKey)) {
            return Cache::retrieve($cacheKey);
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
                'AND (trgs.id_shop = %s OR trgs.id_shop = 0)',
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

        /** @noinspection PhpParamsInspection */
        // @phan-suppress-next-line PhanTypeMismatchArgument
        Cache::store($cacheKey, $res);

        return $res;
    }

    /**
     * Get groups are used in the specific prices, including
     * product specific prices and catalog price rules
     * @return array of group ids
     */
    public static function getGroupsBeingUsedInSpecificPrices()
    {
        $shopId = NostoHelperContext::getShopId();
        $cacheKey = 'NostoHelperPriceVariation-getGroupsBeingUsedInSpecificPrices-' . $shopId;
        if (Cache::isStored($cacheKey)) {
            return Cache::retrieve($cacheKey);
        }

        $filter = $shopId ? ' WHERE (`id_shop` = ' . $shopId . ' OR id_shop = 0)' : '';
        $result = Db::getInstance()->executeS(
            sprintf('SELECT DISTINCT id_group FROM `%sspecific_price` ', _DB_PREFIX_)
            . $filter
        );
        $groupIds = array();
        if ($result && is_array($result)) {
            foreach ($result as $row) {
                $groupIds[] = $row[self::ID_GROUP];
            }
        }
        //make sure it always has 'any'
        if (!in_array(0, $groupIds)) {
            $groupIds[] = 0;
        }

        /** @noinspection PhpParamsInspection */
        // @phan-suppress-next-line PhanTypeMismatchArgument
        Cache::store($cacheKey, $groupIds);

        return $groupIds;
    }

    /**
     * Get countries are used in the specific prices, including
     * product specific prices and catalog price rules
     * @return array of country ids
     */
    public static function getCountriesBeingUsedInSpecificPrices()
    {
        $shopId = NostoHelperContext::getShopId();
        $cacheKey = 'NostoHelperPriceVariation-getCountriesBeingUsedInSpecificPrices-' . $shopId;
        if (Cache::isStored($cacheKey)) {
            return Cache::retrieve($cacheKey);
        }

        $filter = $shopId ? ' and (sp.id_shop = ' . $shopId . ' OR sp.id_shop = 0)' : '';
        $result = Db::getInstance()->executeS(
            sprintf(
                "SELECT DISTINCT sp.id_country FROM `%sspecific_price` sp
                INNER JOIN `%scountry` c ON c.id_country = sp.id_country
                WHERE c.active = TRUE
                $filter
                ",
                _DB_PREFIX_,
                _DB_PREFIX_
            )
        );
        $countryIds = array();
        if (is_array($result)) {
            foreach ($result as $row) {
                $countryIds[] = $row[self::ID_COUNTRY];
            }
        }
        //make sure it always has 'any'
        if (!in_array(0, $countryIds)) {
            $countryIds[] = 0;
        }

        /** @noinspection PhpParamsInspection */
        // @phan-suppress-next-line PhanTypeMismatchArgument
        Cache::store($cacheKey, $countryIds);

        return $countryIds;
    }

    /**
     * Get all the countries are used in tax rules and specific price
     * @return array of country ids
     */
    public static function getVariationCountries()
    {
        $shopId = NostoHelperContext::getShopId();
        $cacheKey = 'NostoHelperPriceVariation-getVariationCountries-' . $shopId;
        if (Cache::isStored($cacheKey)) {
            return Cache::retrieve($cacheKey);
        }

        $countryIds = self::getCountriesBeingUsedInSpecificPrices();
        if (NostoHelperConfig::getVariationTaxRuleEnabled()) {
            $countryIdsFromTaxRules = self::getCountriesBeingUsedInTaxRules();
            $countryIds = array_unique(array_merge($countryIds, $countryIdsFromTaxRules));
        }

        /** @noinspection PhpParamsInspection */
        // @phan-suppress-next-line PhanTypeMismatchArgument
        Cache::store($cacheKey, $countryIds);

        return $countryIds;
    }
}
