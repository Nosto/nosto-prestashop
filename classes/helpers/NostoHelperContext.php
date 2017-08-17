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
        $context = new NostoContextManager($idLang, $idShop);
        $retval = null;
        try {
            $retval = $callable($context->getForgedContext());
        } catch (Exception $e) {
            NostoHelperLogger::log($e);
        }
        $context->revertToOriginalContext();
        return $retval;
    }

    /**
     * Get language Id from current context
     *
     * @return mixed int|null
     */
    public static function getLanguageIdFromContext()
    {
        return Context::getContext()->language->id;
    }

    /**
     * Get shop group Id from current context
     *
     * @return int|null
     */
    public static function getShopGroupIdFromContext()
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
    public static function getShopIdFromContext()
    {
        if (Context::getContext()->shop instanceof Shop) {
            return Context::getContext()->shop->id;
        }

        return null;
    }
}
