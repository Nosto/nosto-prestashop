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
     *
     * @param Customer $customer event parameters
     * @return bool
     */
    public function customerUpdated(Customer $customer)
    {
        if (!$customer->email
            || !NostoTagging::isEnabled(NostoTagging::MODULE_NAME)
        ) {
            return false;
        }
        // Try to update marketing permission to all store views that has share_customer flag on
        $updatedAccounts = array();
        NostoHelperContext::runInContextForEachLanguageEachShop(function () use ($customer, &$updatedAccounts) {
            $shopGroup = Shop::getContextShopGroup();
            if ($shopGroup !== null
                && (bool)$shopGroup->active
                && (bool)$shopGroup->share_customer
            ) {
                try {
                    $account = NostoHelperAccount::getAccount();
                    if ($account instanceof NostoSDKAccount && $account->isConnectedToNosto()) {
                        $updatedAccounts[$account->getName()] =
                            self::updateMarketingPermissionInCurrentContext($customer, $account);
                    }
                } catch (Exception $e) {
                    NostoHelperLogger::error($e);
                }
            }
        });
        // Update marketing permission for the current context, in case it's a single store
        try {
            $account = NostoHelperAccount::getAccount();
            if ($account instanceof NostoSDKAccount
                && $account->isConnectedToNosto()
                && !array_key_exists($account->getName(), $updatedAccounts)
            ) {
                $updatedAccounts[$account->getName()] =
                    self::updateMarketingPermissionInCurrentContext($customer, $account);
            }
        } catch (Exception $e) {
            NostoHelperLogger::error($e);
        }
        return $this->isAllUpdated($updatedAccounts);
    }

    /**
     * Fires the update for the given customer and account
     * returns true if the update was successful
     *
     * @param Customer $customer
     * @param NostoSDKAccount $account
     * @return bool
     */
    private static function updateMarketingPermissionInCurrentContext(Customer $customer, NostoSDKAccount $account)
    {
        if (!$account->getApiToken(NostoSDKToken::API_EMAIL)) {
            NostoHelperLogger::info(
                sprintf(
                    'API_EMAIL api token is missing (%s). Please reconnect Nosto account to create API_EMAIL token',
                    $account->getName()
                )
            );
            return false;
        }
        $newsletter = $customer->newsletter;
        $email = $customer->email;
        $service = new NostoSDKMarketingPermission($account);
        return $service->update($email, $newsletter);
    }

    /**
     * Check if all store views that the customer belongs to
     * were updated. Logs stores that failed to update.
     *
     * @param $updatedAccounts
     * @return bool
     */
    private function isAllUpdated(array $updatedAccounts)
    {
        $success = true;
        foreach ($updatedAccounts as $accountName => $isUpdated) {
            if ($isUpdated === false) {
                NostoHelperLogger::info(
                    sprintf(
                        'Failed to update marketing permission for the account (%s) ',
                        $accountName
                    )
                );
                $success = false;
                break;
            }
        }
        return $success;
    }
}
