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
     * @param int $idLang the language identifier
     * @param int $idShop the shop identifier
     * @param $callable
     * @return mixed the return value of the anonymous function
     */
    public static function runInContext($idLang, $idShop, $callable)
    {
        $retval = null;

        self::emulateContext($idLang, $idShop);
        try {
            $retval = $callable();
        } catch (Exception $e) {
            NostoHelperLogger::log($e);
        }
        self::revertToOriginalContext();

        return $retval;
    }

    public static function runWithEachNostoAccount($callable)
    {
        self::runWithEachNostoAccount(function () use ($callable)
        {
            $account = NostoHelperAccount::find();
            if ($account === null) {
                return null;
            } else {
                $callable();
            }
        });
    }


    public static function runInAContextForEachLanguageEachShop($callable)
    {
        foreach (Shop::getShops() as $shop) {
            $shopId = isset($shop['id_shop']) ? $shop['id_shop'] : null;
            foreach (Language::getLanguages(true, $shopId) as $language) {
                $id_shop_group = isset($shop['id_shop_group']) ? $shop['id_shop_group'] : null;
                self::runInContext($language['id_lang'], $shopId, function() use ($callable)
                {
                    $callable();
                });
            }
        }

        return true;
    }

    public static function emulateContext($languageId, $shopId)
    {
        $context = Context::getContext();
        self::$backupContextStack[] = $context->cloneContext();

        // Reset the shop context to be the current processed shop. This will fix the "friendly url"'
        // format of urls generated through the Link class.
        self::$backupShopContextStack[] = Shop::getContext();
        Shop::setContext(Shop::CONTEXT_SHOP, $shopId);
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

        $context->language = new Language($languageId);
        $context->shop = new Shop($shopId);
        $context->currency = Currency::getDefaultCurrency();
        $context->link = NostoHelperLink::getLink();
    }

    /**
     * Revert the active context to the original one (before calling forgeContext)
     */
    public static function revertToOriginalContext()
    {
        if (!self::$backupContextStack || !self::$backupShopContextStack )
        {
            throw new Exception('revertToOriginalContext() is called before calling emulateContext()');
        }

        $backupContext = array_shift(self::$backupContextStack);
        $backupShopContext = array_shift(self::$backupShopContextStack);

        $context = Context::getContext();

        $context->language = $backupContext->language;
        $context->shop = $backupContext->shop;
        $context->currency = $backupContext->currency;
        $context->link = $backupContext->link;

        Shop::setContext($backupShopContext, $context->shop ? $context->shop->id : null);

        if (method_exists('ShopUrl', 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }
    }

    /**
     * Get language Id from current context
     *
     * @return mixed int|null
     */
    public static function getLanguageId()
    {
        return Context::getContext()->language->id;
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
        if (Context::getContext()->shop instanceof Shop) {
            return Context::getContext()->shop->id;
        }

        return null;
    }
}
