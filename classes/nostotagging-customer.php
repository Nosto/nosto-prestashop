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
		$customer = $this->object;
		if (!Validate::isLoadedObject($customer) || !$customer->isLogged())
			return;

		$this->first_name = $customer->firstname;
		$this->last_name = $customer->lastname;
		$this->email = $customer->email;
	}
}
