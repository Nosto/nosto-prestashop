<?php

class NostoAccountMetaData implements NostoAccountMetaInterface
{
	protected $owner;
	protected $billing;
	public function __construct()
	{
		$this->owner = new NostoAccountMetaDataOwner();
		$this->billing = new NostoAccountMetaDataBilling();
	}
	public function getTitle()
	{
		return 'My Shop';
	}
	public function getName()
	{
		return '00000000';
	}
	public function getPlatform()
	{
		return 'platform';
	}
	public function getFrontPageUrl()
	{
		return 'http://localhost';
	}
	public function getCurrency()
	{
		return new NostoCurrencyCode('USD');
	}
	public function getLanguage()
	{
		return new NostoLanguageCode('en');
	}
	public function getOwnerLanguage()
	{
		return new NostoLanguageCode('en');
	}
	public function getOwner()
	{
		return $this->owner;
	}
	public function getBillingDetails()
	{
		return $this->billing;
	}
	public function getSignUpApiToken()
	{
		return 'abc123';
	}
    public function getPartnerCode()
    {
        return 'nosto';
    }
    public function getCurrencies()
    {
        return array(
            new NostoCurrency(
                new NostoCurrencyCode('USD'),
                new NostoCurrencySymbol('$', 'left'),
                new NostoCurrencyFormat(',', 3, '.', 2)
            ),
            new NostoCurrency(
                new NostoCurrencyCode('EUR'),
                new NostoCurrencySymbol('€', 'right'),
                new NostoCurrencyFormat(',', 3, '.', 2)
            )
        );
    }
    public function getDefaultPriceVariationId()
    {
        return null;
    }
    public function getUseCurrencyExchangeRates()
    {
        return array();
    }
}
