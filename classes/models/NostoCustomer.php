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

use Nosto\Model\Customer as NostoSDKCustomer;

class NostoCustomer extends NostoSDKCustomer
{
    /**
     * Loads the customer data from supplied context and customer objects.
     *
     * @param Customer $customer the customer model to process
     * @return NostoCustomer|null the customer object
     * @throws PrestaShopException
     */
    public static function loadData(Customer $customer)
    {
        if (!NostoHelperConfig::isCustomerTaggingEnabled()) {
            return null;
        }
        $nostoCustomer = new NostoCustomer();
        $nostoCustomer->setFirstName($customer->firstname);
        $nostoCustomer->setLastName($customer->lastname);
        $nostoCustomer->setEmail($customer->email);
        $nostoCustomer->setMarketingPermission($customer->newsletter);
        try {
            $nostoCustomer->populateCustomerReference($customer);
        } catch (Exception $e) {
            NostoHelperLogger::error($e);
        }

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoCustomer), array(
            'nosto_customer' => $nostoCustomer
        ));
        return $nostoCustomer;
    }

    /**
     * Populates customer reference attribute. If customer doesn't yet have
     * customer reference saved in db a new will be generated and saved
     *
     * @param Customer $customer
     * @throws PrestaShopDatabaseException
     */
    private function populateCustomerReference(Customer $customer)
    {
        $customerReference = NostoCustomerManager::getCustomerReference($customer);
        if (!empty($customerReference)) {
            $this->setCustomerReference($customerReference);
        } else {
            $customerReference = NostoCustomerManager::generateCustomerReference($customer);
            NostoCustomerManager::saveCustomerReference($customer, $customerReference);
            $this->setCustomerReference($customerReference);
        }
    }
}
