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

/**
 * Model for tagging customers.
 */
class NostoTaggingCustomer extends NostoTaggingModel
{
    /**
     * @var string the customer first name.
     */
    public $first_name;

    /**
     * @var string the customer last name.
     */
    public $last_name;

    /**
     * @var string the customer email address.
     */
    public $email;

    /**
     * @var string the customer customer reference.
     */
    public $customer_reference;

    /**
     * Loads the customer data from supplied context and customer objects.
     *
     * @param Customer $customer the customer object.
     */
    public function loadData(Customer $customer)
    {
        if (!$this->isCustomerLoggedIn($customer)) {
            return;
        }

        $this->first_name = $customer->firstname;
        $this->last_name = $customer->lastname;
        $this->email = $customer->email;
        try {
            $this->populateCustomerReference($customer);
        } catch (Exception $e) {
            /* @var NostoTaggingHelperLogger $logger */
            $logger = Nosto::helper('nosto_tagging/logger');
            $logger->error(
                __CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
                $e->getCode()
            );
        }
    }

    /**
     * Check if the customer is logged in or not.
     *
     * @param Customer $customer the customer object to check.
     * @return bool true if the customer is logged in, false otherwise.
     */
    public function isCustomerLoggedIn(Customer $customer)
    {
        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        return $customer->isLogged();
    }

    /**
     * Populates customer reference attribute. If customer doesn't yet have
     * customer reference saved in db a new will be generated and saved
     *
     * @param Customer $customer
     */
    private function populateCustomerReference(Customer $customer)
    {
        /* @var NostoTaggingHelperCustomer $customer_helper */
        $customer_helper = Nosto::helper('nosto_tagging/customer');
        $customer_reference = $customer_helper->getCustomerReference($customer);
        if (!empty($customer_reference)) {
            $this->customer_reference = $customer_reference;
        } else {
            $customer_reference = $customer_helper->generateCustomerReference($customer);
            $customer_helper->saveCustomerReference($customer, $customer_reference);
            $this->customer_reference = $customer_reference;
        }
    }
}
