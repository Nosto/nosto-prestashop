<?php

class NostoAccountMetaDataBilling implements NostoAccountMetaBillingInterface
{
	public function getCountry()
	{
		return new NostoCountryCode('US');
	}
}