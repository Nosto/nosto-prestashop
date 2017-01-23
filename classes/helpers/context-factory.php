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
     * Holds the original shop id whever context is forged
     * @var int
     */
    private $original_shop_id;

    /**
     * Forges a new context and returns the altered context
     *
     * @param int $id_lang the language ID to add to the new context.
     * @param int $id_shop the shop ID to add to the new context.
     *
     * @return Context the new context.
     */
    public function forgeContext($id_lang, $id_shop)
    {
        /* @var ContextCore $context */
        $context = Context::getContext();
        $this->saveOriginalContext($context);
        $forged_context = $context->cloneContext();
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

        $forged_context->language = new Language($id_lang);
        $forged_context->shop = new Shop($id_shop);
        $forged_context->link = new Link('http://', 'http://');
        $forged_context->currency = isset($currency) ? $currency : Currency::getDefaultCurrency();

        return $forged_context;
    }

    /**
     * Saves necessary parts of current context so those can be reverted
     *
     * @param Context $context
     */
    private function saveOriginalContext(Context $context)
    {
        if (isset($context->shop) && !empty($context->shop->id)) {
            $this->original_shop_id = $context->shop->id;
        }
    }

    /**
     * Reverst the active context to the original one (before calling forgeContext)
     */
    public function revertToOriginalContext()
    {
        if (_PS_VERSION_ >= '1.5' && !empty($this->original_shop_id)) {
            Shop::setContext(Shop::CONTEXT_SHOP, $this->original_shop_id);
        }
    }
}
