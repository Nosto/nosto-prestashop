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

use Nosto\Object\Notification as NostoSDKNotification;

class NostoCheckMulticurrencyNotification extends NostoNotification
{
    /**
     * Checks if some of the stores use multiple currencies but Nosto multi-currency is not enabled
     * and returns a notification if it isn't
     *
     * @param Shop $shop the shop for which to check the notification
     * @param Language $language the language for which to check the notification
     * @return NostoNotification|null a notification or null if no notification is needed
     */
    public static function check(Shop $shop, Language $language)
    {
        $connected = NostoHelperAccount::existsAndIsConnected($language->id, $shop->id_shop_group, $shop->id);
        if ($connected) {
            if (!Nosto::useMultipleCurrencies($language->id, $shop)) {
                return NostoHelperContext::runInContext(
                    $language->id,
                    $shop->id,
                    function ($context) use ($shop, $language) {
                        $currencies = NostoHelperCurrency::getCurrencies($context, true);
                        if (count($currencies) > 1) {
                            return new NostoNotification(
                                $shop,
                                $language,
                                NostoSDKNotification::TYPE_MULTI_CURRENCY_DISABLED,
                                NostoSDKNotification::SEVERITY_WARNING,
                                'Your shop %s with language %s is using multiple currencies but' .
                                ' the multi-currency feature for Nosto is disabled'
                            );
                        }
                    }
                );
            }
        }

        return null;
    }
}
