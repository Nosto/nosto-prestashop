<?php

class NostoTaggingMetaAccountBilling implements NostoAccountMetaDataBillingDetailsInterface
{
	/**
	 * @var string country ISO (ISO 3166-1 alpha-2) code for billing details.
	 */
	protected $country;

	/**
	 * @param Context $context
	 */
	public function loadData($context)
	{
		$this->country = $context->country->iso_code;
	}

	/**
	 * Sets the account billing details country ISO (ISO 3166-1 alpha-2) code.
	 *
	 * @param string $country the country ISO code.
	 */
	public function setCountry($country)
	{
		$this->country = $country;
	}

	/**
	 * The 2-letter ISO code (ISO 3166-1 alpha-2) for billing details country.
	 *
	 * @return string the country ISO code.
	 */
	public function getCountry()
	{
		return $this->country;
	}
}
