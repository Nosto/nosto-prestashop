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

class NostoCheckAccountNotification extends NostoNotification
{
    /**
     * Checks if Nosto is installed to a given store and language and returns a notification if it
     * isn't
     *
     * @return NostoNotification|null a notification or null if no notification is needed
     */
    public static function check()
    {
        $connected = NostoHelperAccount::existsAndIsConnected();
        if (!$connected) {
            return new NostoCheckAccountNotification(
                NostoHelperContext::getShop(),
                NostoHelperContext::getLanguage(),
                NostoSDKNotification::TYPE_MISSING_INSTALLATION,
                NostoSDKNotification::SEVERITY_INFO,
                'Nosto account is not installed to shop %s and language %s'
            );
        }

        return null;
    }
}
