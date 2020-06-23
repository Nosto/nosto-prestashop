<?php
/**
 * 2013-2020 Nosto Solutions Ltd
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
 * @copyright 2013-2020 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Context helper for creating and replacing contexts i.e. emulation
 */
class NostoHelperContext
{
    private static $backupContextStack = array();
    private static $backupShopContextStack = array();

    /**
     * Runs a function in the scope of another shop's context and reverts back to the original
     * context after the the function invocation
     *
     * @param callable $callable
     * @param bool|int $languageId the language identifier. False means do not manipulate it
     * @param bool|int $shopId the shop identifier. False means do not manipulate it
     * @param bool|int $currencyId the currency id. False means do not manipulate it
     * @param bool|int $employeeId the employee id. False means do not manipulate it
     * @param bool|int $countryId the country id. False means do not manipulate it
     * @return mixed the return value of the anonymous function
     */
    public static function runInContext(
        $callable,
        $languageId = false,
        $shopId = false,
        $currencyId = false,
        $employeeId = false,
        $countryId = false
    ) {
        $retVal = null;

        self::emulateContext($languageId, $shopId, $currencyId, $employeeId, $countryId);
        try {
            // @phan-suppress-next-line PhanTypeVoidAssignment
            $retVal = $callable();
        } catch (Exception $e) {
            NostoHelperLogger::log($e->getMessage());
        }
        self::revertToOriginalContext();

        return $retVal;
    }

    public static function runWithEachNostoAccount($callable)
    {
        self::runInContextForEachLanguageEachShop(function () use ($callable) {
            $account = NostoHelperAccount::getAccount();
            if ($account === null) {
                return null;
            } else {
                $callable();
            }
        });
    }

    public static function runInContextForEachLanguageEachShop($callable)
    {
        foreach (Shop::getShops() as $shop) {
            $shopId = isset($shop['id_shop']) ? $shop['id_shop'] : null;
            foreach (Language::getLanguages(true, $shopId) as $language) {
                self::runInContext(
                    function () use ($callable) {
                        $callable();
                    },
                    $language['id_lang'],
                    $shopId
                );
            }
        }

        return true;
    }

    /**
     * emulate context
     * @param bool|int $languageId the language identifier. False means do not manipulate it
     * @param bool|int $shopId the shop identifier. False means do not manipulate it
     * @param bool|int $currencyId the currency id. False means do not manipulate it
     * @param bool|int $employeeId the employee id. False means do not manipulate it
     * @param bool|int $countryId the country id. False means do not manipulate it
     *
     * @throws PrestaShopException
     * @suppress PhanTypeMismatchArgument
     * @suppress PhanTypeMismatchProperty
     */
    public static function emulateContext(
        $languageId = false,
        $shopId = false,
        $currencyId = false,
        $employeeId = false,
        $countryId = false
    ) {
        $context = Context::getContext();
        self::$backupContextStack[] = $context->cloneContext();

        // Reset the shop context to be the current processed shop. This will fix the "friendly url"'
        // format of urls generated through the Link class.
        self::$backupShopContextStack[] = Shop::getContext();

        if ($shopId !== false) {
            $context->shop = new Shop($shopId);
            Shop::setContext(Shop::CONTEXT_SHOP, $shopId);
        }
        // Reset the dispatcher singleton instance so that the url rewrite setting is check on a
        // shop basis when generating product urls. This will fix the issue of incorrectly formatted
        // urls when one shop has the rewrite setting enabled and another does not.
        Dispatcher::$instance = null;
        if (method_exists('ShopUrl', 'resetMainDomainCache')) {
            // Reset the shop url domain cache so that it is re-initialized on a shop basis when
            // generating product image urls. This will fix the issue of the image urls having an
            // incorrect shop base url when the
            // shops are configured to use different domains.
            ShopUrl::resetMainDomainCache();
        }

        //clean local cache. Otherwise it may get the wrong quantity data or other data
        Cache::clean("*");

        if ($languageId !== false) {
            $context->language = new Language($languageId);
        }

        if ($currencyId !== false) {
            $context->currency = new Currency($currencyId);
        }

        if ($employeeId !== false) {
            $context->employee = new Employee($employeeId);
        }

        if ($countryId !== false) {
            $context->country = new Country($countryId);
        }

        $context->link = NostoHelperLink::getLink();
    }

    /**
     * Revert the active context to the original one (before calling forgeContext)
     */
    public static function revertToOriginalContext()
    {
        if (!self::$backupContextStack || !self::$backupShopContextStack) {
            throw new RuntimeException('revertToOriginalContext() is called before calling emulateContext()');
        }

        $backupContext = array_pop(self::$backupContextStack);
        $backupShopContext = array_pop(self::$backupShopContextStack);

        $context = Context::getContext();

        $context->language = $backupContext->language;
        $context->shop = $backupContext->shop;
        $context->currency = $backupContext->currency;
        $context->employee = $backupContext->employee;
        $context->country = $backupContext->country;
        $context->link = $backupContext->link;

        Shop::setContext($backupShopContext, $context->shop ? $context->shop->id : null);

        if (method_exists('ShopUrl', 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }
    }

    /**
     * Get language from current context
     *
     * @return Language|null
     */
    public static function getLanguage()
    {
        return Context::getContext()->language;
    }

    /**
     * Get language Id from current context
     *
     * @return int|null
     */
    public static function getLanguageId()
    {
        return self::getLanguage() ? (int)self::getLanguage()->id : null;
    }

    /**
     * Get cart from current context
     *
     * @return Cart|null
     */
    public static function getCart()
    {
        return Context::getContext()->cart;
    }

    /**
     * Get cart Id from current context
     *
     * @return int|null
     */
    public static function getCartId()
    {
        return self::getCart() ? (int)self::getCart()->id : null;
    }

    /**
     * Get currency Id from current context
     *
     * @return int|null
     */
    public static function getCurrencyId()
    {
        return self::getCurrency() ? (int)self::getCurrency()->id : null;
    }

    /**
     * Get currency from current context
     *
     * @return Currency|null
     */
    public static function getCurrency()
    {
        return Context::getContext()->currency;
    }

    /**
     * Get country Id from current context
     *
     * @return int|null
     */
    public static function getCountryId()
    {
        return self::getCountry() ? (int)self::getCountry()->id : null;
    }

    /**
     * Get currency from current context
     *
     * @return Country|null
     */
    public static function getCountry()
    {
        return Context::getContext()->country;
    }

    /**
     * Get employee Id from current context
     *
     * @return int|null
     */
    public static function getEmployeeId()
    {
        return self::getEmployee() ? self::getEmployee()->id : null;
    }

    /**
     * Get employee from current context
     *
     * @return Employee|null
     */
    public static function getEmployee()
    {
        return Context::getContext()->employee;
    }

    /**
     * Get shop group Id from current context
     *
     * @return int|null
     */
    public static function getShopGroupId()
    {
        if (Context::getContext()->shop instanceof Shop) {
            return Context::getContext()->shop->id_shop_group;
        }

        return null;
    }

    /**
     * Get shop Id from current context
     *
     * @return int|null
     */
    public static function getShopId()
    {
        return self::getShop() ? self::getShop()->id : null;
    }

    /**
     * Get shop from current context
     *
     * @return Shop|null
     */
    public static function getShop()
    {
        return Context::getContext()->shop;
    }

    /**
     * Set value to cookie
     * @param $key
     * @param $value
     * @throws Exception
     */
    public static function setCookieValue($key, $value)
    {
        Context::getContext()->cookie->__set($key, $value);
    }
}
