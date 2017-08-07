<?php

use Nosto\Operation\SyncRates;
use Nosto\Types\Signup\AccountInterface;

class RatesService
{
    /**
     * Updates exchange rates for all stores and Nosto accounts
     *
     * @throws \Nosto\NostoException
     */
    public function updateExchangeRatesForAllStores()
    {
        /** @var NostoTaggingHelperContextFactory $context_factory */
        $context_factory = Nosto::helper('nosto_tagging/context_factory');
        /** @var NostoTaggingHelperConfig $helper_config */
        $helper_config = Nosto::helper('nosto_tagging/config');

        foreach (Shop::getShops() as $shop) {
            $id_shop = isset($shop['id_shop']) ? (int)$shop['id_shop'] : null;
            $id_shop_group = isset($shop['id_shop_group']) ? (int)$shop['id_shop_group'] : null;
            foreach (Language::getLanguages(true, $id_shop) as $language) {
                $id_lang = (int)$language['id_lang'];
                $use_multiple_currencies = $helper_config->useMultipleCurrencies($id_lang,
                    $id_shop_group, $id_shop);
                if ($use_multiple_currencies) {
                    $nosto_account = NostoTaggingHelperAccount::find($id_lang, $id_shop_group,
                        $id_shop);
                    if (!is_null($nosto_account)) {
                        $context = $context_factory->forgeContext($id_lang, $id_shop);
                        if (!$this->updateCurrencyExchangeRates($nosto_account, $context)) {
                            throw new Nosto\NostoException(
                                sprintf(
                                    'Exchange rate update failed for %s',
                                    $nosto_account->getName()
                                )
                            );
                        } else {
                            $context_factory->revertToOriginalContext();
                        }
                    }
                }
            }
        }
    }

    /**
     * Sends a currency exchange rate update request to Nosto via API.
     *
     * @param AccountInterface $account
     * @param Context $context
     * @return bool
     */
    public function updateCurrencyExchangeRates(AccountInterface $account, Context $context)
    {
        try {
            $exchangeRates = Rates::loadData($context);
            $service = new SyncRates($account);
            return $service->update($exchangeRates);
        } catch (Exception $e) {
            NostoTaggingHelperLogger::error(__CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
                $e->getCode());
        }
        return false;
    }

}