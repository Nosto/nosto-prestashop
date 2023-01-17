<?php
/**
 * 2013-2022 Nosto Solutions Ltd
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
 * @copyright 2013-2022 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class NostoHelperCron
{

    /**
     * Returns the access token needed to validate requests to the cron controllers.
     * The access token is stored in the db config, and will be renewed if the module
     * is re-installed or the db entry is removed.
     *
     * @return string the access token.
     */
    public static function getCronAccessToken()
    {
        $token = NostoHelperConfig::getCronAccessToken();
        if (empty($token)) {
            // Running bin2hex() will make the string length 32 characters.
            $token = bin2hex(phpseclib\Crypt\Random::string(16));
            NostoHelperConfig::saveCronAccessToken($token);
        }
        return $token;
    }
}
