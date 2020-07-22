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

use Nosto\Model\Order\Buyer as NostoSDKOrderBuyer;

class NostoOrderBuyer extends NostoSDKOrderBuyer
{
    /**
     * Loads the buyer data from the customer object.
     *
     * @param Customer $customer the customer model to process
     * @param Order $order the order object
     * @return NostoOrderBuyer|null the buyer object
     */
    public static function loadData(Customer $customer, Order $order)
    {
        if (!NostoHelperConfig::isCustomerTaggingEnabled()) {
            return null;
        }
        $nostoBuyer = new NostoOrderBuyer();
        $nostoBuyer->setFirstName($customer->firstname);
        $nostoBuyer->setLastName($customer->lastname);
        $nostoBuyer->setEmail($customer->email);
        $nostoBuyer->setMarketingPermission($customer->newsletter);

        $billingAddressId = $order->id_address_invoice;
        $addresses = $customer->getAddresses($order->id_lang);
        if ($addresses) {
            foreach ($addresses as $address) {
                if (array_key_exists('id_address', $address)
                    && $address['id_address'] === $billingAddressId
                ) {
                    $addressObject = new Address($address['id_address'], $order->id_lang);
                    if ($addressObject->phone_mobile) {
                        $nostoBuyer->setPhone($addressObject->phone_mobile);
                    } elseif ($addressObject->phone) {
                        $nostoBuyer->setPhone($addressObject->phone);
                    }
                    $nostoBuyer->setPostcode($addressObject->postcode);
                    if (array_key_exists('id_country', $address)) {
                        $nostoBuyer->setCountry(Country::getIsoById($address['id_country']));
                    }
                    break;
                }
            }
        }

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoBuyer), array(
            'customer' => $customer,
            'nosto_order_buyer' => $nostoBuyer
        ));

        return $nostoBuyer;
    }
}
