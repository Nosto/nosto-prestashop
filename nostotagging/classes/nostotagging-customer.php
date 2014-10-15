<?php

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
	 * Populates the block with data from the customer.
	 *
	 * @param Customer $customer the customer object.
	 */
	public function populate(Customer $customer)
	{
		if (Validate::isLoadedObject($customer) && $customer->isLogged())
		{
			$this->first_name = $customer->firstname;
			$this->last_name = $customer->lastname;
			$this->email = $customer->email;
		}
	}
}
