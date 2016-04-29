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
 * Buyer info model used bu the order model.
 */
class NostoTaggingOrderBuyer implements NostoOrderBuyerInterface
{
    /**
     * @var string the first name of the one who placed the order.
     */
    protected $first_name;

    /**
     * @var string the last name of the one who placed the order.
     */
    protected $last_name;

    /**
     * @var string the email address of the one who placed the order.
     */
    protected $email;

    /**
     * @inheritdoc
     */
    public function getFirstName()
    {
        return $this->first_name;
    }

    /**
     * @inheritdoc
     */
    public function getLastName()
    {
        return $this->last_name;
    }

    /**
     * @inheritdoc
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Loads the buyer data from the customer object.
     *
     * @param Customer $customer the customer object.
     */
    public function loadData(Customer $customer)
    {
        $this->first_name = $customer->firstname;
        $this->last_name = $customer->lastname;
        $this->email = $customer->email;
    }
}
