<?php

class NostoOAuthClientMetaData implements NostoOauthClientMetaInterface
{
    private $account;

	public function getClientId()
	{
		return 'client-id';
	}
	public function getClientSecret()
	{
		return 'client-secret';
	}
	public function getRedirectUrl()
	{
		return 'http://my.shop.com/nosto/oauth';
	}
	public function getScopes()
	{
		return array('sso', 'products');
	}
	public function getLanguage()
	{
		return new NostoLanguageCode('en');
	}
    public function getAccount()
    {
        return $this->account;
    }
    public function setAccount(NostoAccount $account)
    {
        $this->account = $account;
    }
}
