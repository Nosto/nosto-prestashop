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

class NostoHelperCookie
{

    /**
     * Nosto cookie name
     */
    const COOKIE_NAME = '2c_cId';

    /**
     * Global cookie scope
     */
    const GLOBAL_COOKIES = '_COOKIE';

    public static function readNostoCookie()
    {
        // We use the $GLOBALS here, instead of the Prestashop cookie class, as we are accessing a
        // nosto cookie that have been set by the JavaScript loaded from nosto.com. Accessing global $_COOKIE array
        // is not allowed by Prestashop's new validation rules effective from April 2016.
        // We read it to keep a mapping of the Nosto user ID and the Prestashop user ID so we can identify which user
        // actually completed an order. We do this for tracking whether or not to send abandoned cart emails.
        if ($GLOBALS[self::GLOBAL_COOKIES] && isset($GLOBALS[self::GLOBAL_COOKIES][self::COOKIE_NAME])) {
            return $GLOBALS[self::GLOBAL_COOKIES][self::COOKIE_NAME];
        } else {
            return null;
        }
    }
}
