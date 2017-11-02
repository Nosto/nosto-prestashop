<?php
/**
 * 2013-2017 Nosto Solutions Ltd
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
 * @copyright 2013-2017 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class NostoVariationKey
{
    /** @var  int $currencyId */
    protected $currencyId;
    /** @var  int $countryId */
    protected $countryId;
    /** @var  int $groupId */
    protected $groupId;

    /**
     * NostoVariationKey constructor.
     * @param int $currencyId
     * @param int $countryId
     * @param int $groupId
     */
    public function __construct($currencyId, $countryId, $groupId)
    {
        $this->currencyId = $currencyId;
        $this->countryId = $countryId;
        $this->groupId = $groupId;
    }

    /**
     * @return int
     */
    public function getCurrencyId()
    {
        return $this->currencyId;
    }

    /**
     * @param int $currencyId
     */
    public function setCurrencyId($currencyId)
    {
        $this->currencyId = $currencyId;
    }

    /**
     * @return int
     */
    public function getCountryId()
    {
        return $this->countryId;
    }

    /**
     * @param int $countryId
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * Get variation id from currency id, country id and group id
     * @return string variation id
     * @suppress PhanTypeMismatchArgument
     */
    public function getVariationId()
    {
        if ($this->countryId == 0) {
            $countryCode = NostoHelperVariation::ANY;
        } else {
            $country = new Country($this->countryId);
            $countryCode = $country->iso_code;
        }

        if ($this->currencyId == 0) {
            $currencyCode = NostoHelperVariation::ANY;
        } else {
            $currency = new Currency($this->currencyId);
            $currencyCode = $currency->iso_code;
        }

        $customerGroupName = '';
        if ($this->groupId == 0) {
            $customerGroupName = NostoHelperVariation::ANY;
        } else {
            $group = new Group($this->groupId);
            if (is_array($group->name) && array_key_exists(NostoHelperContext::getLanguageId(), $group->name)) {
                $customerGroupName = $group->name[NostoHelperContext::getLanguageId()];
            } elseif (is_scalar($group->name)) {
                $customerGroupName = $group->name;
            }
        }

        return strtoupper($currencyCode . '-' . $countryCode . '-' . $customerGroupName);
    }
}
