<?php

class NostoAccountMetaDataOwner implements NostoAccountMetaDataOwnerInterface
{
	public function getFirstName()
	{
		return 'James';
	}
	public function getLastName()
	{
		return 'Kirk';
	}
	public function getEmail()
	{
		return 'james.kirk@example.com';
	}
}
