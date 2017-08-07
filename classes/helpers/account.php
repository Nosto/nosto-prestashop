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
     * @param Nosto\Types\Signup\AccountInterface $account the account to save.
     * @param null|int $id_lang the ID of the language to set the account name for.
     * @param null|int $id_shop_group the ID of the shop context.
     * @param null|int $id_shop the ID of the shop.
     * @return bool true if the save was successful, false otherwise.
     */
    public static function save(
        Nosto\Types\Signup\AccountInterface $account,
        $id_lang,
        $id_shop_group = null,
        $id_shop = null
    ) {
        $success = NostoTaggingHelperConfig::saveAccountName(
            $account->getName(),
            $id_lang,
            $id_shop_group,
            $id_shop
        );
        if ($success) {
            foreach ($account->getTokens() as $token) {
                $success = $success && NostoTaggingHelperConfig::saveToken(
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
     * @param $context
     * @param Nosto\Object\Signup\Account $account the account to delete.
     * @param int $id_lang the ID of the language model to delete the account for.
     * @param null|int $id_shop_group the ID of the shop context.
     * @param null|int $id_shop the ID of the shop.
     * @return bool true if successful, false otherwise.
     */
    public static function delete(
        Context $context,
        Nosto\Object\Signup\Account $account,
        $id_lang,
        $id_shop_group = null,
        $id_shop = null
    ) {
        $success = NostoTaggingHelperConfig::deleteAllFromContext($id_lang, $id_shop_group,
            $id_shop);
        $currentUser = NostoCurrentUser::loadData($context);
        if ($success) {
            $token = $account->getApiToken('sso');
            if ($token) {
                try {
                    $service = new Nosto\Operation\UninstallAccount($account);
                    $service->delete($currentUser);
                } catch (Nosto\NostoException $e) {
                    NostoHelperLogger::error(
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
                self::delete(Context::getContext(), $account, $language['id_lang'], $id_shop_group,
                    $id_shop);
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
     * @return Nosto\Object\Signup\Account|null the account with loaded API tokens, or null if not
     *     found.
     */
    public static function find($lang_id = null, $id_shop_group = null, $id_shop = null)
    {
        $account_name = NostoTaggingHelperConfig::getAccountName($lang_id, $id_shop_group,
            $id_shop);
        if (!empty($account_name)) {
            $account = new Nosto\Object\Signup\Account($account_name);
            $tokens = array();
            foreach (Nosto\Request\Api\Token::getApiTokenNames() as $token_name) {
                $token_value = NostoTaggingHelperConfig::getToken($token_name, $lang_id,
                    $id_shop_group,
                    $id_shop);
                if (!empty($token_value)) {
                    $tokens[$token_name] = $token_value;
                }
            }

            if (!empty($tokens)) {
                foreach ($tokens as $name => $value) {
                    $account->addApiToken(new Nosto\Request\Api\Token($name, $value));
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
    public static function existsAndIsConnected(
        $lang_id = null,
        $id_shop_group = null,
        $id_shop = null
    ) {
        $account = self::find($lang_id, $id_shop_group, $id_shop);
        return ($account !== null && $account->isConnectedToNosto());
    }
}
