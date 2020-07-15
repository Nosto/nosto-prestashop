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

use Nosto\Model\Notification as NostoSDKNotification;

class NostoNotificationManager
{
    /**
     * Checks all notifications for all the stores and languages in the Prestashop installation and
     * displays them in the admin depending upon the severity.
     *
     * @param NostoTagging $module the instance of the module for displaying notifications
     */
    public static function checkAndDisplay(NostoTagging $module)
    {
        $notifications = array();

        NostoHelperContext::runInContextForEachLanguageEachShop(function () use (&$notifications) {
            $notification = NostoCheckAccountNotification::check();
            if ($notification != null) {
                $notifications[] = $notification;
            }
            $notification = NostoCheckMulticurrencyNotification::check();
            if ($notification != null) {
                $notifications[] = $notification;
            }
            $notification = NostoCheckTokenNotification::check();
            if ($notification != null) {
                $notifications[] = $notification;
            }
        });

        foreach ($notifications as $notification) {
            if ($notification->getNotificationType() === NostoSDKNotification::TYPE_MISSING_INSTALLATION
                && !NostoHelperController::isController('AdminModules')
            ) {
                continue;
            }
            switch ($notification->getNotificationSeverity()) {
                case NostoSDKNotification::SEVERITY_INFO:
                    $module->adminDisplayInformation($notification->getFormattedMessage());
                    break;
                case NostoSDKNotification::SEVERITY_WARNING:
                    $module->adminDisplayWarning($notification->getFormattedMessage());
                    break;
                default:
            }
        }
    }
}
