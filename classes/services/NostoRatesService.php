<?php

/**
 * 2013-2017 Nosto Solutions Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@nosto.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2017 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\NostoException as NostoSDKException;
use Nosto\Operation\SyncRates as NostoSDKSyncRatesOperation;
use Nosto\Types\Signup\AccountInterface as NostoSDKSignupAccount;

class NostoRatesService
{
    /**
     * Updates exchange rates for all stores and Nosto accounts
     */
    public function updateExchangeRatesForAllStores()
    {
        foreach (Shop::getShops() as $shop) {
            $idShop = isset($shop['id_shop']) ? (int)$shop['id_shop'] : null;
            $idShopGroup = isset($shop['id_shop_group']) ? (int)$shop['id_shop_group'] : null;
            foreach (Language::getLanguages(true, $idShop) as $language) {
                $idLang = (int)$language['id_lang'];
                $useMultipleCurrencies = NostoHelperConfig::useMultipleCurrencies($idLang,
                    $idShopGroup, $idShop);
                if ($useMultipleCurrencies) {
                    $nostoAccount = NostoHelperAccount::find($idLang, $idShopGroup, $idShop);
                    if (!is_null($nostoAccount)) {

                        NostoHelperContext::runInContext($idLang, $idShop,
                            function ($context) use ($nostoAccount) {
                                if (!$this->updateCurrencyExchangeRates($nostoAccount, $context)) {
                                    throw new NostoSDKException(
                                        sprintf(
                                            'Exchange rate update failed for %s',
                                            $nostoAccount->getName()
                                        )
                                    );
                                }
                            }
                        );
                    }
                }
            }
        }
    }

    /**
     * Sends a currency exchange rate update request to Nosto via API.
     *
     * @param NostoSDKSignupAccount $account
     * @param Context $context
     * @return bool
     */
    public function updateCurrencyExchangeRates(NostoSDKSignupAccount $account, Context $context)
    {
        try {
            $exchangeRates = NostoExchangeRates::loadData($context);
            $service = new NostoSDKSyncRatesOperation($account);
            return $service->update($exchangeRates);
        } catch (Exception $e) {
            NostoHelperLogger::error($e);
        }
        return false;
    }

}