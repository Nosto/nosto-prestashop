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

use Nosto\NostoException as NostoSDKException;
use Nosto\Object\Signup\Account as NostoSDKAccount;
use Nosto\Operation\UninstallAccount as NostoSDKUninstallAccountOperation;
use Nosto\Request\Api\Token as NostoSDKAPIToken;
use Nosto\Types\Signup\AccountInterface as NostoSDKSignupAccountInterface;

/**
 * Helper class for managing Nosto accounts.
 */
class NostoHelperAccount
{

    /**
     * Saves a Nosto account to PS config.
     * Also handles any attached API tokens.
     *
     * @param NostoSDKSignupAccountInterface $account the account to save.
     * @return bool true if the save was successful, false otherwise.
     */
    public static function save(NostoSDKSignupAccountInterface $account)
    {
        $success = NostoHelperConfig::saveAccountName($account->getName());
        if ($success) {
            foreach ($account->getTokens() as $token) {
                $success = $success && NostoHelperConfig::saveToken($token->getName(), $token->getValue());
            }
        }
        return $success;
    }

    /**
     * Deletes a Nosto account from the PS config.
     * Also sends a notification to Nosto that the account has been deleted.
     *
     * @param NostoSDKAccount $account the account to delete.
     * @param int $id_lang the ID of the language model to delete the account for.
     * @param null|int $id_shop_group the ID of the shop context.
     * @param null|int $id_shop the ID of the shop.
     * @return bool true if successful, false otherwise.
     */
    public static function delete(NostoSDKAccount $account, $id_lang, $id_shop_group = null, $id_shop = null)
    {
        $success = NostoHelperConfig::deleteAllFromContext(
            $id_lang,
            $id_shop_group,
            $id_shop
        );
        $currentUser = NostoCurrentUser::loadData();
        if ($success) {
            $token = $account->getApiToken('sso');
            if ($token) {
                try {
                    $service = new NostoSDKUninstallAccountOperation($account);
                    $service->delete($currentUser);
                } catch (NostoSDKException $e) {
                    NostoHelperLogger::error($e);
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

    /**
     * Finds and returns an account for given criteria.
     *
     * @param null|int $lang_id the ID of the language.
     * @param null|int $id_shop_group the ID of the shop context.
     * @param null|int $id_shop the ID of the shop.
     * @return NostoSDKAccount|null the account with loaded API tokens, or null if not
     *     found.
     */
    public static function find($lang_id = null, $id_shop_group = null, $id_shop = null)
    {
        $account_name = NostoHelperConfig::getAccountName(
            $lang_id,
            $id_shop_group,
            $id_shop
        );
        if (!empty($account_name)) {
            $account = new NostoSDKAccount($account_name);
            $tokens = array();
            foreach (NostoSDKAPIToken::getApiTokenNames() as $token_name) {
                $token_value = NostoHelperConfig::getToken(
                    $token_name,
                    $lang_id,
                    $id_shop_group,
                    $id_shop
                );
                if (!empty($token_value)) {
                    $tokens[$token_name] = $token_value;
                }
            }

            if (!empty($tokens)) {
                foreach ($tokens as $name => $value) {
                    $account->addApiToken(new NostoSDKAPIToken($name, $value));
                }
            }

            return $account;
        }
        return null;
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
}
