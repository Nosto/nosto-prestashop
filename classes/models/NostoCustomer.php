<?php

/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\Object\Customer as NostoSDKCustomer;

class NostoCustomer extends NostoSDKCustomer
{
    const NOSTO_TAGGING_RESTORE_CART_ATTRIBUTE_LENGTH = 64;

    /**
     * Loads the customer data from supplied context and customer objects.
     *
     * @param Customer $customer the customer model to process
     * @return NostoCustomer the customer object
     */
    public static function loadData(Customer $customer)
    {
        $nostoCustomer = new NostoCustomer();
        $nostoCustomer->setFirstName($customer->firstname);
        $nostoCustomer->setLastName($customer->lastname);
        $nostoCustomer->setEmail($customer->email);
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

    /**
     * Returns restore cart url
     *
     * @param string $hash
     * @return string
     */
    private function generateRestoreCartUrl($hash)
    {
        $params = array(
            'restoreCartHash' => $hash
        );

        $this->context->link->getModuleLink('nostotagging','restoreCart',array_of_params);
        $params['h'] = $hash;
        $url = $store->getUrl(NostoHelperUrl::NOSTO_PATH_RESTORE_CART, $params);

        return $url;
    }
}
