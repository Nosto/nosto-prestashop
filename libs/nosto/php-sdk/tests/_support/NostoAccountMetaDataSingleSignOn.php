<?php

class NostoAccountMetaDataSingleSignOn implements NostoAccountMetaSingleSignOnInterface
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
	public function getPlatform()
	{
		return 'platform';
	}
}
