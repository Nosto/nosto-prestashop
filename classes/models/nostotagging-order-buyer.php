<?php

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
