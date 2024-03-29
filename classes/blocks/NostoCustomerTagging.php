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

class NostoCustomerTagging
{
    /**
     * Renders the customer tagging by checking if the customer if currently logged in
     * @return string|null the tagging
     * @throws PrestaShopException
     */
    public static function get()
    {
        if (!self::isCustomerLoggedIn(Context::getContext()->customer)) {
            return '';
        }

        $cid = NostoHelperCookie::readNostoCookie();
        $hcid = $cid ? hash(NostoTagging::VISITOR_HASH_ALGO, $cid) : '';

        $nostoCustomer = NostoCustomer::loadData(Context::getContext()->customer);
        if (!$nostoCustomer instanceof  NostoCustomer) {
            return null;
        }
        $nostoCustomer->setHcid($hcid);

        return $nostoCustomer->toHtml();
    }

    /**
     * Check if the customer is logged in or not.
     *
     * @param Customer $customer the customer object to check.
     * @return bool true if the customer is logged in, false otherwise.
     */
    public static function isCustomerLoggedIn(Customer $customer)
    {
        if (!Validate::isLoadedObject($customer)) {
            return false;
        }

        return $customer->isLogged();
    }
}
