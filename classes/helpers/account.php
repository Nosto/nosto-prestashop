<?php
/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for managing Nosto accounts.
 */
class NostoTaggingHelperAccount
{

    /**
     * Saves a Nosto account to PS config.
     * Also handles any attached API tokens.
     *
     * @param NostoAccount $account the account to save.
     * @param null|int $id_lang the ID of the language to set the account name for.
     * @param null|int $id_shop_group the ID of the shop context.
     * @param null|int $id_shop the ID of the shop.
     * @return bool true if the save was successful, false otherwise.
     */
    public static function save(NostoAccount $account, $id_lang, $id_shop_group = null, $id_shop = null)
    {
        /** @var NostoTaggingHelperConfig $helper_config */
        $helper_config = Nosto::helper('nosto_tagging/config');
        $success = $helper_config->saveAccountName(
            $account->getName(),
            $id_lang,
            $id_shop_group,
            $id_shop
        );
        if ($success) {
            foreach ($account->getTokens() as $token) {
                $success = $success && $helper_config->saveToken(
                    $token->getName(),
                    $token->getValue(),
                    $id_lang,
                    $id_shop_group,
                    $id_shop
                );
            }
        }
        return $success;
    }

    /**
     * Deletes a Nosto account from the PS config.
     * Also sends a notification to Nosto that the account has been deleted.
     *
     * @param NostoAccount $account the account to delete.
     * @param int $id_lang the ID of the language model to delete the account for.
     * @param null|int $id_shop_group the ID of the shop context.
     * @param null|int $id_shop the ID of the shop.
     * @return bool true if successful, false otherwise.
     */
    public static function delete(NostoAccount $account, $id_lang, $id_shop_group = null, $id_shop = null)
    {
        /** @var NostoTaggingHelperConfig $helper_config */
        $helper_config = Nosto::helper('nosto_tagging/config');
        $success = $helper_config->deleteAllFromContext($id_lang, $id_shop_group, $id_shop);
        if ($success) {
            $token = $account->getApiToken('sso');
            if ($token) {
                try {
                    $account->delete();
                } catch (NostoException $e) {
                    /* @var NostoTaggingHelperLogger $logger */
                    $logger = Nosto::helper('nosto_tagging/logger');
                    $logger->error(
                        __CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
                        $e->getCode()
                    );
                }
            }
        }
        return $success;
    }

    /**
     * Deletes all Nosto accounts from the system and notifies nosto that accounts are deleted.
     *
     * @return bool
     */
    public static function deleteAll()
    {
        foreach (Shop::getShops() as $shop) {
            $id_shop = isset($shop['id_shop']) ? $shop['id_shop'] : null;
            foreach (Language::getLanguages(true, $id_shop) as $language) {
                $id_shop_group = isset($shop['id_shop_group']) ? $shop['id_shop_group'] : null;
                $account = self::find($language['id_lang'], $id_shop_group, $id_shop);
                if ($account === null) {
                    continue;
                }
                self::delete($account, $language['id_lang'], $id_shop_group, $id_shop);
            }
        }
        return true;
    }

    public static function findByContext(Context $context)
    {
        if ($context->shop instanceof Shop) {
            return self::find(
                $context->language->id,
                $context->shop->id_shop_group,
                $context->shop->id
            );
        } else {
            return self::find($context->language->id);
        }
    }

    /**
     * Finds and returns an account for given criteria.
     *
     * @param null|int $lang_id the ID of the language.
     * @param null|int $id_shop_group the ID of the shop context.
     * @param null|int $id_shop the ID of the shop.
     * @return NostoAccount|null the account with loaded API tokens, or null if not found.
     */
    public static function find($lang_id = null, $id_shop_group = null, $id_shop = null)
    {
        /** @var NostoTaggingHelperConfig $helper_config */
        $helper_config = Nosto::helper('nosto_tagging/config');
        $account_name = $helper_config->getAccountName($lang_id, $id_shop_group, $id_shop);
        if (!empty($account_name)) {
            $account = new NostoAccount($account_name);
            $tokens = array();
            foreach (NostoApiToken::getApiTokenNames() as $token_name) {
                $token_value = $helper_config->getToken($token_name, $lang_id, $id_shop_group, $id_shop);
                if (!empty($token_value)) {
                    $tokens[$token_name] = $token_value;
                }
            }

            if (!empty($tokens)) {
                foreach ($tokens as $name => $value) {
                    $account->addApiToken(new NostoApiToken($name, $value));
                }
            }

            return $account;
        }
        return null;
    }

    /**
     * Checks if Nosto is installed to a given store and language
     *
     * @param Context $context
     * @return bool
     */
    public static function isContextConnected(Context $context)
    {
        if ($context->shop instanceof Shop) {
            return self::existsAndIsConnected(
                $context->language->id,
                $context->shop->id_shop_group,
                $context->shop->id
            );
        } else {
            return self::existsAndIsConnected($context->language->id);
        }
    }

    /**
     * Checks if an account exists and is "connected to Nosto" for given criteria.
     *
     * @param null|int $lang_id the ID of the language.
     * @param null|int $id_shop_group the ID of the shop context.
     * @param null|int $id_shop the ID of the shop.
     * @return bool true if it does, false otherwise.
     */
    public static function existsAndIsConnected($lang_id = null, $id_shop_group = null, $id_shop = null)
    {
        $account = self::find($lang_id, $id_shop_group, $id_shop);
        return ($account !== null && $account->isConnectedToNosto());
    }


    /**
     * Sends a currency exchange rate update request to Nosto via API.
     *
     * @param NostoAccount $account
     * @param Context|ContextCore $context
     * @return bool
     */
    public static function updateCurrencyExchangeRates(NostoAccount $account, Context $context)
    {
        /** @var NostoTaggingHelperCurrency $currency_helper */
        $currency_helper = Nosto::helper('nosto_tagging/currency');
        try {
            $exchangeRates = $currency_helper->getExchangeRateCollection($context);
            $service = new NostoOperationExchangeRate($account, $exchangeRates);
            return $service->update();
        } catch (NostoException $e) {
            /** @var NostoTaggingHelperLogger $logger */
            $logger = Nosto::helper('nosto_tagging/logger');
            $logger->error(__CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(), $e->getCode());
        }
        return false;
    }

    /**
     * Sends account settings update request to Nosto via API.
     *
     * @param NostoAccount $account
     * @param NostoTaggingMetaAccount $accountMetaData
     * @return bool
     */
    public static function updateSettings(NostoAccount $account, NostoTaggingMetaAccount $accountMetaData)
    {
        $service = new NostoOperationAccount($account, $accountMetaData);
        return $service->update();
    }
}
