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

require_once 'NostoBaseController.php';

use Nosto\Request\Api\Token as NostoSDKAPIToken;

class NostoUpdateExchangeRateController extends NostoBaseController
{
    /**
     * @inheritdoc
     *
     * @suppress PhanDeprecatedFunction
     */
    public function execute()
    {
        $nostoAccount = NostoHelperAccount::getAccount();
        $operation = new NostoRatesService();
        if ($nostoAccount && $operation->updateCurrencyExchangeRates($nostoAccount)
        ) {
            NostoHelperFlash::add(
                'success',
                $this->l('Exchange rates successfully updated to Nosto')
            );
        } else {
            if (!$nostoAccount->getApiToken(NostoSDKAPIToken::API_EXCHANGE_RATES)) {
                $message = 'Failed to update exchange rates to Nosto due to a missing API token. 
                            Please, reconnect your account with Nosto';
            } else {
                $message = 'There was an error updating the exchange rates. 
                            See Prestashop logs for more information.';
            }
            NostoHelperFlash::add('error', $this->l($message));
        }

        return true;
    }
}
