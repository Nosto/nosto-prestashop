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
 * Meta data class for account Single Sign On.
 */
class NostoTaggingMetaAccountSso implements NostoAccountMetaSingleSignOnInterface
{
	/**
	 * @var string the first name of the user who is doing the SSO.
	 */
	protected $first_name;

	/**
	 * @var string the last name of the user who is doing the SSO.
	 */
	protected $last_name;

	/**
	 * @var string the email address of the user who doing the SSO.
	 */
	protected $email;

	/**
	 * Loads the SSO data.
	 *
	 * @param Employee|EmployeeCore $employee the employee doing the SSO.
	 */
	public function loadData(Employee $employee)
	{
		$this->first_name = $employee->firstname;
		$this->last_name = $employee->lastname;
		$this->email = $employee->email;
	}

	/**
	 * The name of the platform.
	 * A list of valid platform names is issued by Nosto.
	 *
	 * @return string the platform name.
	 */
	public function getPlatform()
	{
		return 'prestashop';
	}

	/**
	 * The first name of the user who is doing the SSO.
	 *
	 * @return string the first name.
	 */
	public function getFirstName()
	{
		return $this->first_name;
	}

	/**
	 * The last name of the user who is doing the SSO.
	 *
	 * @return string the last name.
	 */
	public function getLastName()
	{
		return $this->last_name;
	}

	/**
	 * The email address of the user who doing the SSO.
	 *
	 * @return string the email address.
	 */
	public function getEmail()
	{
		return $this->email;
	}
}
