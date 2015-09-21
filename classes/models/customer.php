<?php
/**
 * 2013-2015 Nosto Solutions Ltd
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
 * @copyright 2013-2015 Nosto Solutions Ltd
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
	protected $first_name;

	/**
	 * @var string the customer last name.
	 */
	protected $last_name;

	/**
	 * @var string the customer email address.
	 */
	protected $email;

	/**
	 * Sets up this DTO.
	 *
	 * @param Customer|CustomerCore $customer the PS customer model.
	 * @param Context $context the PS context model.
	 */
	public function loadData(Customer $customer, Context $context = null)
	{
		if (!self::isLoggedIn($customer, $context))
			return;

		if (is_null($context))
			$context = Context::getContext();

		$this->first_name = $customer->firstname;
		$this->last_name = $customer->lastname;
		$this->email = $customer->email;

		$this->dispatchHookActionLoadAfter(array(
			'nosto_customer' => $this,
			'customer' => $customer,
			'context' => $context
		));
	}

	/**
	 * Check if the customer is logged in or not.
	 * We need to check the cookie if PS version is 1.4 as the CustomerBackwardModule::isLogged() method does not work.
	 *
	 * @param Customer|CustomerCore $customer the PS customer model.
	 * @param Context|null $context the PS context model.
	 * @return bool true if the customer is logged in, false otherwise.
	 */
	public static function isLoggedIn(Customer $customer, Context $context = null)
	{
		if (!Validate::isLoadedObject($customer))
			return false;

		if (is_null($context))
			$context = Context::getContext();

		if (_PS_VERSION_ >= '1.5')
			return $customer->isLogged();

		if (!isset($context->cookie))
			return false;

		// Double check that the given customer object has the same id as the cookie's id_customer property,
		// before checking if the cookie is logged in.
		return (!empty($context->cookie->id_customer)
			&& !empty($customer->id)
			&& ($context->cookie->id_customer == $customer->id)
			&& $context->cookie->isLogged());
	}

	/**
	 * Returns the logged in customers first name.
	 *
	 * @return string the name.
	 */
	public function getFirstName()
	{
		return $this->first_name;
	}

	/**
	 * Returns the logged in customers last name.
	 *
	 * @return string the name.
	 */
	public function getLastName()
	{
		return $this->last_name;
	}

	/**
	 * Returns the logged in customers email address.
	 *
	 * @return string the email.
	 */
	public function getEmail()
	{
		return $this->email;
	}

	/**
	 * Sets the first name of the logged in user.
	 *
	 * The name must be a non-empty string.
	 *
	 * Usage:
	 * $object->setFirstName('John');
	 *
	 * @param string $first_name the name.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setFirstName($first_name)
	{
		if (!is_string($first_name) || empty($first_name))
			throw new InvalidArgumentException('First name must be a non-empty string value.');

		$this->first_name = $first_name;
	}

	/**
	 * Sets the last name of the logged in user.
	 *
	 * The name must be a non-empty string.
	 *
	 * Usage:
	 * $object->setLastName('Doe');
	 *
	 * @param string $last_name the name.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setLastName($last_name)
	{
		if (!is_string($last_name) || empty($last_name))
			throw new InvalidArgumentException('Last name must be a non-empty string value.');

		$this->last_name = $last_name;
	}

	/**
	 * Sets the email address of the logged in user.
	 *
	 * The email must be a non-empty valid email address string.
	 *
	 * Usage:
	 * $object->setEmail('john.doe@example.com');
	 *
	 * @param string $email the email.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setEmail($email)
	{
		if (!is_string($email) || empty($email))
			throw new InvalidArgumentException('Email name must be a non-empty string value.');
		if (!Validate::isEmail($email))
			throw new InvalidArgumentException('Email is not a valid email address.');

		$this->email = $email;
	}
}
