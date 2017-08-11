<?php

/**
 * 2013-2017 Nosto Solutions Ltd
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
 * @copyright 2013-2017 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class NostoContextManager
{
    /**
     * Holds the original shop id
     *
     * @var int
     */
    private $originalShopId;

    /**
     * Holds the original shop context type
     *
     * @see Shop
     * @var int
     */
    private $originalShopContext;

    /**
     * Holds the original shop group
     *
     * @var int
     */
    private $originalShopGroup;

    /**
     * Holds the original language
     *
     * @var Language
     */
    private $originalLanguage;

    /**
     * Holds the original language
     *
     * @var Currency
     */
    private $originalCurrency;

    /**
     * Constructor to a new context object which contains the state of the old context and provides
     * methods to revert back to the old context
     *
     * @param int $idLang the language ID to add to the new context.
     * @param int $idShop the shop ID to add to the new context.
     */
    public function __construct($idLang, $idShop)
    {
        $context = Context::getContext();
        if (isset($context->shop) && $context->shop instanceof Shop) {
            $this->originalShopContext = $context->shop->getContext();
            if ($context->shop->getContextShopID()) {
                $this->originalShopId = $context->shop->getContextShopID();
            }
            $contextShopGroupID = $context->shop->getContextShopGroupID();
            if (!empty($contextShopGroupID)) {
                $this->originalShopGroup = $contextShopGroupID;
            }
            if (!empty($context->currency)) {
                $this->originalCurrency = $context->currency;
            }
            if (!empty($context->language)) {
                $this->originalLanguage = $context->language;
            }
        }

        $this->forgedContext = $context->cloneContext();
        // Reset the shop context to be the current processed shop. This will fix the "friendly url"'
        // format of urls generated through the Link class.
        Shop::setContext(Shop::CONTEXT_SHOP, $idShop);
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

        foreach (Currency::getCurrenciesByIdShop($idShop) as $row) {
            if ($row['deleted'] === '0' && $row['active'] === '1') {
                $currency = new Currency($row['id_currency']);
                break;
            }
        }

        $this->forgedContext->language = new Language($idLang);
        $this->forgedContext->shop = new Shop($idShop);
        $this->forgedContext->link = NostoHelperLink::getLink();
        $this->forgedContext->currency = isset($currency) ? $currency : Currency::getDefaultCurrency();
    }

    public function getForgedContext()
    {
        return $this->forgedContext;
    }

    /**
     * Revert the active context to the original one (before calling forgeContext)
     */
    public function revertToOriginalContext()
    {
        $current_context = Context::getContext();

        if (!empty($this->originalLanguage)) {
            $current_context->language = $this->originalLanguage;
        }
        if (!empty($this->originalCurrency)) {
            $current_context->currency = $this->originalCurrency;
        }
        if ($this->originalShopContext === Shop::CONTEXT_SHOP && !empty($this->originalShopId)) {
            Shop::setContext(Shop::CONTEXT_SHOP, $this->originalShopId);
            if (!empty($this->originalShopId)) {
                $current_context->shop = new Shop($this->originalShopId);
            }
        } elseif ($this->originalShopContext === Shop::CONTEXT_GROUP && !empty($this->originalShopGroup)) {
            Shop::setContext(Shop::CONTEXT_GROUP, $this->originalShopGroup);
        } elseif ($this->originalShopContext === Shop::CONTEXT_ALL) {
            Shop::setContext(Shop::CONTEXT_ALL);
        }
        if (method_exists('ShopUrl', 'resetMainDomainCache')) {
            ShopUrl::resetMainDomainCache();
        }
    }
}
