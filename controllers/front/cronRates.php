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
 * @copyright 2013-2019 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once(dirname(__FILE__) . '/cron.php');

use Nosto\NostoException as NostoSDKException;

/**
 * Cron controller to update currency exchange rates in Nosto.
 *
 * This controller can be called directly from the servers cron tab or, using the PS "cronjob"
 * module. The authentication is
 */
class NostoTaggingCronRatesModuleFrontController extends NostoTaggingCronModuleFrontController
{
    /**
     * @inheritdoc
     */
    public function initContent()
    {
        NostoHelperLogger::info('Exchange rate sync started');
        try {
            $operation = new NostoRatesService();
            $operation->updateExchangeRatesForAllStores();
        } catch (NostoSDKException $e) {
            NostoHelperLogger::error($e, 'Exchange rate sync failed with error');
        }
        NostoHelperLogger::info('Exchange rate sync finished');
        exit;
    }
}
