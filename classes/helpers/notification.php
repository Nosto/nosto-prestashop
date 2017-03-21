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
 * Helper class for managing notifications related to Nosto module.
 *
 */
class NostoTaggingHelperNotification
{
    /**
     * Checks if any of the tokens are missing from the store and language
     *
     * @param Shop $shop
     * @param Language $language
     * @return boolean
     */
    public function checkTokens(Shop $shop, Language $language)
    {
        $id_shop_group = isset($shop->id_shop_group) ? $shop->id_shop_group : null;
        $tokens_ok = true;
        $connected = NostoTaggingHelperAccount::existsAndIsConnected($language->id, $id_shop_group, $shop->id);
        if ($connected) {
            /** @var NostoAccount $account */
            $account = NostoTaggingHelperAccount::find($language->id);
            if ($account instanceof NostoAccountInterface && $account->hasMissingTokens()) {
                $tokens_ok = false;
            }
        }

        return $tokens_ok;
    }

    /**
     * Checks if Nosto is installed to a given store and language
     *
     * @param Shop $shop
     * @param Language $language
     * @return bool
     */
    protected function checkNostoIstalled(Shop $shop, Language $language)
    {
        $is_installed = true;
        $id_shop_group = isset($shop->id_shop_group) ? $shop->id_shop_group : null;
        $connected = NostoTaggingHelperAccount::existsAndIsConnected($language->id, $id_shop_group, $shop->id);
        if (!$connected) {
            $is_installed = false;
        }

        return $is_installed;
    }

    /**
     * Checks if some of the stores use multiple currencies but Nosto multi-currency is not enabled
     *
     * @param Shop $shop
     * @param Language $language
     * @return bool
     */
    protected function checkMulticurrencyEnabled(Shop $shop, Language $language)
    {
        $multicurrency_ok = true;
        $id_shop_group = isset($shop->id_shop_group) ? $shop->id_shop_group : null;
        $connected = NostoTaggingHelperAccount::existsAndIsConnected($language->id, $id_shop_group, $shop->id);
        if ($connected) {
            /** @var NostoTaggingHelperConfig $helper_config */
            $helper_config = Nosto::helper('nosto_tagging/config');
            if (!$helper_config->useMultipleCurrencies($language->id, $id_shop_group, $shop->id)) {
                /* @var NostoTaggingHelperContextFactory $context_factory */
                $context_factory = Nosto::helper('nosto_tagging/context_factory');
                /* @var NostoTaggingHelperCurrency $helper_currency */
                $helper_currency = Nosto::helper('nosto_tagging/currency');
                $forged_context = $context_factory->forgeContext($language->id, $shop->id);
                $currencies = $helper_currency->getCurrencies($forged_context, true);
                $context_factory->revertToOriginalContext();
                if (count($currencies) > 1) {
                    $multicurrency_ok = false;
                }
            }
        }

        return $multicurrency_ok;
    }

    /**
     * Checks and returns all notification for the Prestashop installation
     *
     * @return array of NostoTaggingAdminNotification objects
     */
    public function getAll()
    {
        $notifications = array();
        foreach (Shop::getShops() as $shopArray) {
            $shop = new Shop($shopArray['id_shop']);
            foreach (Language::getLanguages(true, $shop->id) as $languageArray) {
                $language = new Language($languageArray['id_lang']);
                if ($this->checkNostoIstalled($shop, $language) == false) {
                    $notification = new NostoTaggingAdminNotification(
                        $shop,
                        $language,
                        NostoNotificationInterface::TYPE_MISSING_INSTALLATION,
                        NostoNotificationInterface::SEVERITY_INFO,
                        'Nosto account is not installed to shop %s and language %s'
                    );
                    $notifications[] = $notification;
                }
                if ($this->checkTokens($shop, $language) == false) {
                    $notification = new NostoTaggingAdminNotification(
                        $shop,
                        $language,
                        NostoNotificationInterface::TYPE_MISSING_TOKENS,
                        NostoNotificationInterface::SEVERITY_WARNING,
                        'One or more Nosto API tokens are missing for shop %s and language %s'
                    );
                    $notifications[] = $notification;
                }
                if ($this->checkMulticurrencyEnabled($shop, $language) == false) {
                    $notification = new NostoTaggingAdminNotification(
                        $shop,
                        $language,
                        NostoNotificationInterface::TYPE_MULTI_CURRENCY_DISABLED,
                        NostoNotificationInterface::SEVERITY_WARNING,
                        'Your shop %s with language %s is using multiple currencies but' .
                        ' the multi-currency feature for Nosto is disabled'
                    );
                    $notifications[] = $notification;
                }
            }
        }

        return $notifications;
    }
}
