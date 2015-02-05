<?php

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
	 * @var    string the account owner email address.
	 */
	protected $email;

	/**
	 * @param Context $context
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
	 * @param string $firstName the first name.
	 */
	public function setFirstName($firstName)
	{
		$this->first_name = $firstName;
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
	 * @param string $lastName the last name.
	 */
	public function setLastName($lastName)
	{
		$this->last_name = $lastName;
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
