<?php

use Nosto\Operation\SyncRates;
use Nosto\Types\Signup\AccountInterface;

class RatesService
{
    private $account;
    private $context;

    public function __construct(AccountInterface $account, Context $context)
    {
        $this->account = $account;
        $this->context = $context;
    }

    /**
     * Sends a currency exchange rate update request to Nosto via API.
     *
     * @return bool
     */
    public function updateCurrencyExchangeRates()
    {
        try {
            $exchangeRates = Rates::loadData($this->context);
            $service = new SyncRates($this->account);
            return $service->update($exchangeRates);
        } catch (Exception $e) {
            /** @var NostoTaggingHelperLogger $logger */
            $logger = Nosto::helper('nosto_tagging/logger');
            $logger->error(__CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(), $e->getCode());
        }
        return false;
    }

}