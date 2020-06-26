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

use \Nosto\Object\MarkupableString as NostoSDKMarkupableString;

class NostoCurrentVariation extends NostoSDKMarkupableString
{
    /**
     * Constructor
     *
     * @param string $variationId
     */
    public function __construct($variationId)
    {
        parent::__construct($variationId, 'nosto_variation');
    }

    /** @noinspection PhpUnhandledExceptionInspection */
    public static function loadData()
    {
        $nostoVariation = null;
        if (NostoHelperConfig::getVariationEnabled()) {
            $groupId = Group::getCurrent() ? Group::getCurrent()->id : 0;
            $currentVariationKey = new NostoVariationKey(
                NostoHelperContext::getCurrencyId(),
                NostoHelperContext::getCountryId(),
                $groupId
            );

            $keyCollection = new NostoVariationKeyCollection();
            $keyCollection->loadData();

            if (!$keyCollection->contains($currentVariationKey)) {
                $currentVariationKey->setCountryId(0);
                if (!$keyCollection->contains($currentVariationKey)) {
                    $currentVariationKey->setCountryId(NostoHelperContext::getCountryId());
                    $currentVariationKey->setGroupId(0);
                    if (!$keyCollection->contains($currentVariationKey)) {
                        $currentVariationKey->setCountryId(0);
                    }
                }
            }

            $nostoVariation = new NostoCurrentVariation($currentVariationKey->getVariationId());

            NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoVariation), array(
                'nosto_variation' => $nostoVariation
            ));

            return $nostoVariation;

        } elseif (NostoHelperConfig::useMultipleCurrencies()) {
            $nostoVariation = new NostoCurrentVariation(NostoHelperContext::getCurrency()->iso_code);

            NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoVariation), array(
                'nosto_variation' => $nostoVariation
            ));

            return $nostoVariation;
        }
        return null;
    }

    /**
     * @return mixed
     */
    public function getVariationId()
    {
        return $this->getValue();
    }
}
