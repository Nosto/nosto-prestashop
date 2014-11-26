<?php
/**
 * 2013-2014 Nosto Solutions Ltd
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
 * @copyright 2013-2014 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Block for tagging customers.
 */
class NostoTaggingCustomer extends NostoTaggingBlock
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
	 * @inheritdoc
	 */
	public function getRequiredItems()
	{
		return array(
			'first_name',
			'last_name',
			'email',
		);
	}

	/**
	 * @inheritdoc
	 */
	public function populate()
	{
		/** @var Customer $customer */
		$customer = $this->object;
		if (!$this->isCustomerLoggedIn($customer))
			return;

		$this->first_name = $customer->firstname;
		$this->last_name = $customer->lastname;
		$this->email = $customer->email;
	}

	/**
	 * Check if the customer is logged in or not.
	 * We need to check the cookie if PS version is 1.4 as the CustomerBackwardModule::isLogged() method does not work.
	 *
	 * @param Customer $customer the customer object to check.
	 * @return bool true if the customer is logged in, false otherwise.
	 */
	protected function isCustomerLoggedIn($customer)
	{
		if (!($customer instanceof Customer) || !Validate::isLoadedObject($customer))
			return false;

		if (_PS_VERSION_ >= '1.5')
			return $customer->isLogged();

		if (!isset($this->context) || !($this->context->cookie instanceof Cookie))
			return false;

		// Double check that the given customer object has the same id as the cookie's id_customer property,
		// before checking if the cookie is logged in.
		return (!empty($this->context->cookie->id_customer)
			&& !empty($customer->id)
			&& ($this->context->cookie->id_customer == $customer->id)
			&& $this->context->cookie->isLogged());
	}
}
