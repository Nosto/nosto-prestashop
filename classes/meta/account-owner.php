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
 * Meta data class for account owner related information needed when creating new accounts.
 */
class NostoTaggingMetaAccountOwner implements NostoAccountMetaDataOwnerInterface
{
	/**
	 * @var string the account owner first name.
	 */
	protected $first_name;

	/**
	 * @var string the account owner last name.
	 */
	protected $last_name;

	/**
	 * @var string the account owner email address.
	 */
	protected $email;

	/**
	 * Loads the meta data from the given context.
	 *
	 * @param Context $context the context to use as data source.
	 */
	public function loadData($context)
	{
		$this->first_name = $context->employee->firstname;
		$this->last_name = $context->employee->lastname;
		$this->email = $context->employee->email;
	}

	/**
	 * Sets the first name of the account owner.
	 *
	 * @param string $first_name the first name.
	 */
	public function setFirstName($first_name)
	{
		$this->first_name = $first_name;
	}

	/**
	 * The first name of the account owner.
	 *
	 * @return string the first name.
	 */
	public function getFirstName()
	{
		return $this->first_name;
	}

	/**
	 * Sets the last name of the account owner.
	 *
	 * @param string $last_name the last name.
	 */
	public function setLastName($last_name)
	{
		$this->last_name = $last_name;
	}

	/**
	 * The last name of the account owner.
	 *
	 * @return string the last name.
	 */
	public function getLastName()
	{
		return $this->last_name;
	}

	/**
	 * Sets the email address of the account owner.
	 *
	 * @param string $email the email address.
	 */
	public function setEmail($email)
	{
		$this->email = $email;
	}

	/**
	 * The email address of the account owner.
	 *
	 * @return string the email address.
	 */
	public function getEmail()
	{
		return $this->email;
	}
}
