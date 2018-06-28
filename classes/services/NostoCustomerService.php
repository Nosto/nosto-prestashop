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

use Nosto\Object\Signup\Account as NostoSDKAccount;
use Nosto\Operation\MarketingPermission as NostoSDKMarketingPermission;
use Nosto\Request\Api\Token as NostoSDKToken;

/**
 * Helper class for sending Customer data to Nosto.
 */
class NostoCustomerService extends AbstractNostoService
{
    /**
     * Customer updated event handler. It is called after Customer updated or created.
     * @param Customer $customer event parameters
     * @return bool
     */
    public function customerUpdated($customer)
    {
        if (!$customer->email) {
            return false;
        }
        try {
            if (!NostoTagging::isEnabled(NostoTagging::MODULE_NAME)) {
                return false;
            }

            $account = NostoHelperAccount::getAccount();
            if (!$account instanceof NostoSDKAccount || !$account->isConnectedToNosto()) {
                return false;
            }

            if (!$account->getApiToken(NostoSDKToken::API_EMAIL)) {
                NostoHelperLogger::info(
                    sprintf(
                        "API_EMAIL api token is missing (%s). Please reconnect nosto account to create API_EMAIL token",
                        $account->getName()
                    )
                );

                return false;
            }

            $newsletter = $customer->newsletter;
            $email = $customer->email;
            $service = new NostoSDKMarketingPermission($account);

            return $service->update($email, $newsletter);
        } catch (\Exception $e) {
            NostoHelperLogger::error($e);
        }
    }
}
