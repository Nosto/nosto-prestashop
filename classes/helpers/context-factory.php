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
 * Context factory helper for creating and replacing PS contexts.
 */
class NostoTaggingHelperContextFactory
{
    /**
     * Forges a new context and replaces the current one.
     *
     * @param int $id_lang the language ID to add to the new context.
     * @param int $id_shop the shop ID to add to the new context.
     * @return Context the new context.
     */
    public function forgeContext($id_lang, $id_shop)
    {
        if (_PS_VERSION_ >= '1.5') {
            // Reset the shop context to be the current processed shop. This will fix the "friendly url" format of urls
            // generated through the Link class.
            Shop::setContext(Shop::CONTEXT_SHOP, $id_shop);
            // Reset the dispatcher singleton instance so that the url rewrite setting is check on a shop basis when
            // generating product urls. This will fix the issue of incorrectly formatted urls when one shop has the
            // rewrite setting enabled and another does not.
            Dispatcher::$instance = null;
            if (method_exists('ShopUrl', 'resetMainDomainCache')) {
                // Reset the shop url domain cache so that it is re-initialized on a shop basis when generating product
                // image urls. This will fix the issue of the image urls having an incorrect shop base url when the
                // shops are configured to use different domains.
                ShopUrl::resetMainDomainCache();
            }

            foreach (Currency::getCurrenciesByIdShop($id_shop) as $row) {
                if ($row['deleted'] === '0' && $row['active'] === '1') {
                    $currency = new Currency($row['id_currency']);
                    break;
                }
            }
        }

        $context = Context::getContext();
        $context->language = new Language($id_lang);
        $context->shop = new Shop($id_shop);
        $context->link = new Link('http://', 'http://');
        $context->currency = isset($currency) ? $currency : Currency::getDefaultCurrency();

        return $context;
    }
}