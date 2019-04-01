<?php
/**
 * 2013-2019 Nosto Solutions Ltd
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
 * @copyright 2013-2019 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for managing config values.
 */
class NostoHelperConfig
{
    const ACCOUNT_NAME = 'NOSTOTAGGING_ACCOUNT_NAME';
    const ADMIN_URL = 'NOSTOTAGGING_ADMIN_URL';
    const INSTALLED_VERSION = 'NOSTOTAGGING_INSTALLED_VERSION';
    const CRON_ACCESS_TOKEN = 'NOSTOTAGGING_CRON_ACCESS_TOKEN';
    const MULTI_CURRENCY_METHOD = 'NOSTOTAGGING_MC_METHOD';
    const SKU_SWITCH = 'NOSTOTAGGING_SKU_SWITCH';
    const CART_UPDATE_SWITCH = 'NOSTOTAGGING_CART_UPDATE_SWITCH';
    const SKIP_CUSTOMER_TAGGING_SWITCH = 'NOSTOTAGGING_SKIP_CUSTOMER_TAGGING_SWITCH';
    const VARIATION_SWITCH = 'NOSTOTAGGING_VARIATION_SWITCH';
    const VARIATION_TAX_RULE_SWITCH = 'NOSTOTAGGING_TAX_RULE_SWITCH';
    const TOKEN_CONFIG_PREFIX = 'NOSTOTAGGING_API_TOKEN_';
    const MULTI_CURRENCY_METHOD_VARIATION = 'priceVariation';
    const MULTI_CURRENCY_METHOD_EXCHANGE_RATE = 'exchangeRate';
    const MULTI_CURRENCY_METHOD_DISABLED = 'disabled';
    const NOSTOTAGGING_POSITION = 'NOSTOTAGGING_POSITION';
    const NOSTOTAGGING_POSITION_TOP = 'top';
    const NOSTOTAGGING_POSITION_FOOTER = 'footer';

    /**
     * Reads and returns a config entry value.
     *
     * @param string $name the name of the config entry in the db.
     * @return mixed
     */
    private static function read($name)
    {
        return Configuration::get(
            $name,
            NostoHelperContext::getLanguageId(),
            NostoHelperContext::getShopGroupId(),
            NostoHelperContext::getShopId()
        );
    }

    /**
     * Reads and returns a global config entry value.
     *
     * @param string $name the name of the config entry in the db.
     * @return mixed
     */
    private static function readGlobal($name)
    {
        return Configuration::get($name);
    }

    /**
     * Writes a config entry value to the db.
     *
     * @param string $name the name of the config entry to save.
     * @param mixed $value the value to save.
     * @param null|int $languageId the language id to save it for.
     * @param bool $global if it should be saved for all shops or in current context.
     * @param null|int $shopGroupId
     * @param null|int $shopId
     * @return bool true is saved, false otherwise.
     */
    private static function write(
        $name,
        $value,
        $languageId = null,
        $global = false,
        $shopGroupId = null,
        $shopId = null
    ) {
        $callback = array(
            'Configuration',
            ($global && method_exists(
                'Configuration',
                'updateGlobalValue'
            )) ? 'updateGlobalValue' : 'updateValue'
        );
        // Store this value for given language only if specified.
        if (!is_array($value) && !empty($languageId)) {
            $value = array($languageId => $value);
        }
        if ($global === false
            && !empty($shopGroupId)
            && !empty($shopId)
        ) {
            $return = call_user_func($callback, (string)$name, $value, false, $shopGroupId, $shopId);
        } else {
            $return = call_user_func($callback, (string)$name, $value);
        }
        return $return;
    }

    /**
     * Removes all "NOSTOTAGGING_" config entries.
     *
     * @return bool always true.
     */
    public static function purge()
    {
        $configTable = pSQL(_DB_PREFIX_ . 'configuration');
        $configLangTable = pSQL($configTable . '_lang');

        Db::getInstance()->execute(
            'DELETE `' . $configLangTable . '` FROM `' . $configLangTable . '`
            LEFT JOIN `' . $configTable . '`
            ON `' . $configLangTable . '`.`id_configuration` = `' . $configTable . '`.`id_configuration`
            WHERE `' . $configTable . '`.`name` LIKE "NOSTOTAGGING_%"'
        );
        Db::getInstance()->execute(
            'DELETE FROM `' . $configTable . '`
            WHERE `' . $configTable . '`.`name` LIKE "NOSTOTAGGING_%"'
        );

        // Reload the config.
        Configuration::loadConfiguration();

        return true;
    }

    /**
     * Removes all "NOSTOTAGGING_" config entries for the current context and given language.
     *
     * @return bool
     */
    public static function deleteAllFromContext()
    {
        $languageId = NostoHelperContext::getLanguageId();
        $shopId = (int)Shop::getContextShopID(true);
        $shopGroupId = (int)Shop::getContextShopGroupID(true);
        if ($shopId) {
            $contextRestriction = ' AND `id_shop` = ' . $shopId;
        } elseif ($shopGroupId) {
            $contextRestriction = '
                AND `id_shop_group` = ' . $shopGroupId . '
                AND (`id_shop` IS NULL OR `id_shop` = 0)
            ';
        } else {
            $contextRestriction = '
                AND (`id_shop_group` IS NULL OR `id_shop_group` = 0)
                AND (`id_shop` IS NULL OR `id_shop` = 0)
            ';
        }

        $configTable = pSQL(_DB_PREFIX_ . 'configuration');
        $configLangTable = pSQL($configTable . '_lang');

        if (!empty($languageId)) {
            Db::getInstance()->execute(
                'DELETE `' . $configLangTable . '` FROM `' . $configLangTable . '`
                INNER JOIN `' . $configTable . '`
                ON `' . $configLangTable . '`.`id_configuration` = `' . $configTable . '`.`id_configuration`
                WHERE `' . $configTable . '`.`name` LIKE "NOSTOTAGGING_%"
                AND `id_lang` = ' . (int)$languageId
                . $contextRestriction
            );
        }
        // Reload the config.
        Configuration::loadConfiguration();

        return true;
    }

    /**
     * Saves the account name to the config for given language.
     *
     * @param string $accountName the account name to save.
     * @return bool true if saving the configuration was successful, false otherwise
     */
    public static function saveAccountName($accountName)
    {
        return self::saveSetting(self::ACCOUNT_NAME, $accountName);
    }

    /**
     * Gets a account name from the config.
     *
     * @return mixed
     */
    public static function getAccountName()
    {
        return Configuration::get(
            self::ACCOUNT_NAME,
            NostoHelperContext::getLanguageId(),
            NostoHelperContext::getShopGroupId(),
            NostoHelperContext::getShopId()
        );
    }

    /**
     * Save the token to the config for given language.
     *
     * @param string $tokeName the name of the token.
     * @param string $tokenValue the value of the token.
     * @return bool true if saving the configuration was successful, false otherwise
     */
    public static function saveToken($tokeName, $tokenValue)
    {
        return self::saveSetting(self::getTokenConfigKey($tokeName), $tokenValue);
    }

    /**
     * Gets a token from the config by name.
     *
     * @param string $tokenName the name of the token to get.
     * @return mixed
     */
    public static function getToken($tokenName)
    {
        return Configuration::get(
            self::getTokenConfigKey($tokenName),
            NostoHelperContext::getLanguageId(),
            NostoHelperContext::getShopGroupId(),
            NostoHelperContext::getShopId()
        );
    }

    /**
     * Saves the admin url to the config.
     *
     * @param string $url the url.
     * @return bool true if saved successfully, false otherwise.
     */
    public static function saveAdminUrl($url)
    {
        return self::write(self::ADMIN_URL, $url);
    }

    /**
     * Get the admin url from the config.
     *
     * @return mixed
     */
    public static function getAdminUrl()
    {
        return self::readGlobal(self::ADMIN_URL);
    }

    /**
     * Gets the fully qualified config key for a token name.
     *
     * @param string $name the name of the token.
     * @return string the fully qualified config key.
     */
    protected static function getTokenConfigKey($name)
    {
        return self::TOKEN_CONFIG_PREFIX . Tools::strtoupper($name);
    }

    /**
     * Returns the access token for the cron controllers.
     * This token is stored globally for all stores and languages.
     *
     * @return bool|string the token or false if not found.
     */
    public static function getCronAccessToken()
    {
        return self::readGlobal(self::CRON_ACCESS_TOKEN);
    }

    /**
     * Saves the access token for the cron controllers.
     * This token is stored globally for all stores and languages.
     *
     * @param string $token the token.
     * @return bool true if saved successfully, false otherwise.
     */
    public static function saveCronAccessToken($token)
    {
        return self::write(self::CRON_ACCESS_TOKEN, $token, null, true);
    }

    /**
     * Returns the multi currency method in use for the context.
     *
     * @return string the multi currency method.
     */
    public static function getMultiCurrencyMethod()
    {
        $method = Configuration::get(
            self::MULTI_CURRENCY_METHOD,
            NostoHelperContext::getLanguageId(),
            NostoHelperContext::getShopGroupId(),
            NostoHelperContext::getShopId()
        );

        return !empty($method) ? $method : self::MULTI_CURRENCY_METHOD_DISABLED;
    }

    /**
     * Returns the position where to render Nosto tagging
     *
     * @return string
     */
    public static function getNostotaggingRenderPosition()
    {
        $position = self::read(self::NOSTOTAGGING_POSITION);
        return !empty($position) ? $position : self::NOSTOTAGGING_POSITION_TOP;
    }

    /**
     * Saves the multi currency method in use for the context.
     *
     * @param string $method the multi currency method.
     * @return bool true if saving the configuration was successful, false otherwise
     */
    public static function saveMultiCurrencyMethod($method)
    {
        return self::saveSetting(self::MULTI_CURRENCY_METHOD, $method);
    }

    /**
     * Is sku feature enabled
     * @return bool true if sku feature has been enabled, false otherwise
     */
    public static function getSkuEnabled()
    {
        return (bool)self::read(self::SKU_SWITCH);
    }

    /**
     * Saves enable/disable of sku feature
     *
     * @param bool $enabled
     * @return bool true if saving the configuration was successful, false otherwise
     */
    public static function saveSkuEnabled($enabled)
    {
        return self::saveSetting(self::SKU_SWITCH, $enabled);
    }

    /**
     * Is cart update feature enabled
     * @return bool true if cart update feature has been enabled, false otherwise
     */
    public static function isCartUpdateEnabled()
    {
        return (bool)self::read(self::CART_UPDATE_SWITCH);
    }

    /**
     * Saves enable/disable of cart update feature
     *
     * @param bool $enabled
     * @return bool true if saving the configuration was successful, false otherwise
     */
    public static function saveCartUpdateEnabled($enabled)
    {
        return self::saveSetting(self::CART_UPDATE_SWITCH, $enabled);
    }

    /**
     * Is customer tagging and order buyer tagging feature enabled
     * @return bool true if customer tagging and order buyer tagging has been enabled, false otherwise
     */
    public static function isCustomerTaggingEnabled()
    {
        $skipped = self::read(self::SKIP_CUSTOMER_TAGGING_SWITCH);

        return !(bool)$skipped;
    }

    /**
     * Saves enable/disable of customer tagging and order buyer tagging
     *
     * @param bool $enabled
     * @return bool true if saving the configuration was successful, false otherwise
     */
    public static function saveCustomerTaggingEnabled($enabled)
    {
        $skipped = $enabled ? '0' : '1';

        return self::saveSetting(self::SKIP_CUSTOMER_TAGGING_SWITCH, $skipped);
    }

    /**
     * Saves the position where to render Nosto tagging
     *
     * @param string $position
     * @return bool true if saving the configuration was successful, false otherwise
     */
    public static function saveNostoTaggingRenderPosition($position)
    {
        return self::saveSetting(self::NOSTOTAGGING_POSITION, $position);
    }

    public static function saveVariationEnabled($enabled)
    {
        return self::saveSetting(self::VARIATION_SWITCH, $enabled);
    }

    /**
     * Is variation feature enabled
     * @return bool true if variation feature has been enabled, false otherwise
     */
    public static function getVariationEnabled()
    {
        return (bool)self::read(self::VARIATION_SWITCH);
    }

    /**
     * @param boolean $enabled Are countries used in tax rules used for price variations
     * @return bool
     */
    public static function saveVariationTaxRuleEnabled($enabled)
    {
        return self::saveSetting(self::VARIATION_TAX_RULE_SWITCH, $enabled);
    }

    /**
     * Are countries used in tax rules used for price variations
     * @return bool true if countries are used in tax rules used for price variations
     */
    public static function getVariationTaxRuleEnabled()
    {
        return (bool)self::read(self::VARIATION_TAX_RULE_SWITCH);
    }

    public static function saveSetting($configName, $value)
    {
        if (NostoHelperContext::getShop() instanceof Shop) {
            $result = self::write(
                $configName,
                $value,
                NostoHelperContext::getLanguageId(),
                false,
                NostoHelperContext::getShopGroupId(),
                NostoHelperContext::getShopId()
            );
        } else {
            $result = self::write(self::SKU_SWITCH, $value, NostoHelperContext::getLanguageId());
        }

        //Reload configuration from database. Prestashop caches the configuration data,
        //and if the configuration key is new (not available before this http request),
        //Configuration::get() always return false in the same http request even the value has been changed
        Configuration::loadConfiguration();

        return $result;
    }

    /**
     * Checks if multiple currencies are used in tagging
     *
     * @return bool the multi currency method.
     */
    public static function useMultipleCurrencies()
    {
        return self::getMultiCurrencyMethod() !== self::MULTI_CURRENCY_METHOD_DISABLED;
    }

    /**
     * Clears tagging related caches (compiled templates)
     *
     * @param $smarty
     */
    public static function clearCache($smarty = null)
    {
        if (method_exists('Tools', 'clearCompile')) {
            Tools::clearCompile($smarty);
        }
    }
}
