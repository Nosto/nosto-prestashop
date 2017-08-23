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
    const TOKEN_CONFIG_PREFIX = 'NOSTOTAGGING_API_TOKEN_';
    const MULTI_CURRENCY_METHOD_VARIATION = 'priceVariation';
    const MULTI_CURRENCY_METHOD_EXCHANGE_RATE = 'exchangeRate';
    const MULTI_CURRENCY_METHOD_DISABLED = 'disabled';
    const NOSTOTAGGING_POSITION = 'NOSTOTAGGING_POSITION';
    const NOSTOTAGGING_IMAGE_TYPE = 'NOSTOTAGGING_IMAGE_TYPE';
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
     * @param null|int $lang_id the language id to save it for.
     * @param bool $global if it should be saved for all shops or in current context.
     * @param null|int $id_shop_group
     * @param null|int $id_shop
     * @return bool true is saved, false otherwise.
     */
    private static function write(
        $name,
        $value,
        $lang_id = null,
        $global = false,
        $id_shop_group = null,
        $id_shop = null
    )
    {
        $callback = array(
            'Configuration',
            ($global && method_exists(
                    'Configuration',
                    'updateGlobalValue'
                )) ? 'updateGlobalValue' : 'updateValue'
        );
        // Store this value for given language only if specified.
        if (!is_array($value) && !empty($lang_id)) {
            $value = array($lang_id => $value);
        }
        if (
            $global === false
            && !empty($id_shop_group)
            && !empty($id_shop)
        ) {
            $return = call_user_func($callback, (string)$name, $value, $id_shop_group, $id_shop);
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
        $config_table = pSQL(_DB_PREFIX_ . 'configuration');
        $config_lang_table = pSQL($config_table . '_lang');

        Db::getInstance()->execute(
            'DELETE `' . $config_lang_table . '` FROM `' . $config_lang_table . '`
            LEFT JOIN `' . $config_table . '`
            ON `' . $config_lang_table . '`.`id_configuration` = `' . $config_table . '`.`id_configuration`
            WHERE `' . $config_table . '`.`name` LIKE "NOSTOTAGGING_%"'
        );
        Db::getInstance()->execute(
            'DELETE FROM `' . $config_table . '`
            WHERE `' . $config_table . '`.`name` LIKE "NOSTOTAGGING_%"'
        );

        // Reload the config.
        Configuration::loadConfiguration();

        return true;
    }

    /**
     * Removes all "NOSTOTAGGING_" config entries for the current context and given language.
     *
     * @param int|null $id_lang the ID of the language object to remove the config entries for.
     * @param null|int $id_shop_group the ID of the shop context.
     * @param null|int $id_shop the ID of the shop.
     * @return bool
     */
    public static function deleteAllFromContext($id_lang = null, $id_shop_group = null, $id_shop = null)
    {
        if ($id_shop === null) {
            $id_shop = (int)Shop::getContextShopID(true);
        }
        if ($id_shop_group === null) {
            $id_shop_group = (int)Shop::getContextShopGroupID(true);
        }

        if ($id_shop) {
            $context_restriction = ' AND `id_shop` = ' . $id_shop;
        } elseif ($id_shop_group) {
            $context_restriction = '
                AND `id_shop_group` = ' . $id_shop_group . '
                AND (`id_shop` IS NULL OR `id_shop` = 0)
            ';
        } else {
            $context_restriction = '
                AND (`id_shop_group` IS NULL OR `id_shop_group` = 0)
                AND (`id_shop` IS NULL OR `id_shop` = 0)
            ';
        }

        $config_table = pSQL(_DB_PREFIX_ . 'configuration');
        $config_lang_table = pSQL($config_table . '_lang');

        if (!empty($id_lang)) {
            Db::getInstance()->execute(
                'DELETE `' . $config_lang_table . '` FROM `' . $config_lang_table . '`
                INNER JOIN `' . $config_table . '`
                ON `' . $config_lang_table . '`.`id_configuration` = `' . $config_table . '`.`id_configuration`
                WHERE `' . $config_table . '`.`name` LIKE "NOSTOTAGGING_%"
                AND `id_lang` = ' . (int)$id_lang
                . $context_restriction
            );
        }
        // Reload the config.
        Configuration::loadConfiguration();

        return true;
    }

    /**
     * Saves the account name to the config for given language.
     *
     * @param string $account_name the account name to save.
     * @return bool true if saving the configuration was successful, false otherwise
     */
    public static function saveAccountName($account_name)
    {
        if (Context::getContext()->shop instanceof Shop) {
            return self::write(
                self::ACCOUNT_NAME,
                $account_name,
                NostoHelperContext::getLanguageId(),
                false,
                NostoHelperContext::getShopGroupId(),
                NostoHelperContext::getShopId()
            );
        } else {
            return self::write(self::ACCOUNT_NAME, $account_name, NostoHelperContext::getLanguageId());
        }
    }

    /**
     * Gets a account name from the config.
     *
     * @param int $id_lang the language identifier for which to fetch the configuration
     * @param null|int $id_shop_group the shop-group identifier for which to fetch the configuration
     * @param null|int $id_shop the shop identifier for which to fetch the configuration
     * @return mixed
     */
    public static function getAccountName($id_lang, $id_shop_group = null, $id_shop = null)
    {
        return Configuration::get(self::ACCOUNT_NAME, $id_lang, $id_shop_group, $id_shop);
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
        if (Context::getContext()->shop instanceof Shop) {
            return self::write(
                self::getTokenConfigKey($tokeName),
                $tokenValue,
                NostoHelperContext::getLanguageId(),
                false,
                NostoHelperContext::getShopGroupId(),
                NostoHelperContext::getShopId()
            );
        } else {
            return self::write(self::getTokenConfigKey($tokeName), $tokenValue, NostoHelperContext::getLanguageId());
        }
    }

    /**
     * Gets a token from the config by name.
     *
     * @param string $token_name the name of the token to get.
     * @return mixed
     */
    public static function getToken($token_name)
    {
        return Configuration::get(self::getTokenConfigKey($token_name), NostoHelperContext::getLanguageId(), NostoHelperContext::getShopGroupId(), NostoHelperContext::getShopId());
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
        $method = Configuration::get(self::MULTI_CURRENCY_METHOD, NostoHelperContext::getLanguageId(), NostoHelperContext::getShopGroupId(), NostoHelperContext::getShopId());
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
        if (Context::getContext()->shop instanceof Shop) {
            return self::write(
                self::MULTI_CURRENCY_METHOD,
                $method,
                NostoHelperContext::getLanguageId(),
                false,
                NostoHelperContext::getShopGroupId(),
                NostoHelperContext::getShopId()
            );
        } else {
            return self::write(self::MULTI_CURRENCY_METHOD, $method, NostoHelperContext::getLanguageId());
        }
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
        if (Context::getContext()->shop instanceof Shop) {
            return self::write(
                self::SKU_SWITCH,
                $enabled,
                NostoHelperContext::getLanguageId(),
                false,
                NostoHelperContext::getShopGroupId(),
                NostoHelperContext::getShopId()
            );
        } else {
            return self::write(self::SKU_SWITCH, $enabled, NostoHelperContext::getLanguageId());
        }
    }

    /**
     * Saves the position where to render Nosto tagging
     *
     * @param string $method the multi currency method.
     * @return bool true if saving the configuration was successful, false otherwise
     */
    public static function saveNostoTaggingRenderPosition($method)
    {
        if (Context::getContext()->shop instanceof Shop) {
            return self::write(
                self::NOSTOTAGGING_POSITION,
                $method,
                NostoHelperContext::getLanguageId(),
                false,
                NostoHelperContext::getShopGroupId(),
                NostoHelperContext::getShopId()
            );
        } else {
            return self::write(self::NOSTOTAGGING_POSITION, $method, NostoHelperContext::getLanguageId());
        }
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

    /**
     * Returns the image type to be used for Nosto tagging
     *
     * @return int
     */
    public static function getImageType()
    {
        $type = self::read(self::NOSTOTAGGING_IMAGE_TYPE);

        return !empty($type) ? $type : null;
    }

    /**
     * Saves the image type to be used for Nosto tagging
     *
     * @param int $type the image type id
     * @return bool true if saving the configuration was successful, false otherwise
     */
    public static function saveImageType($type)
    {
        if (Context::getContext()->shop instanceof Shop) {
            return self::write(
                self::NOSTOTAGGING_IMAGE_TYPE,
                $type,
                NostoHelperContext::getLanguageId(),
                false,
                NostoHelperContext::getShopGroupId(),
                NostoHelperContext::getShopId()
            );
        } else {
            return self::write(self::NOSTOTAGGING_IMAGE_TYPE, $type, NostoHelperContext::getLanguageId());
        }
    }
}
