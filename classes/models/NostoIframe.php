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

use \Nosto\Object\Iframe as NostoSDKIframe;

class NostoIframe extends NostoSDKIframe
{
    private $recentVisits;
    private $recentSales;
    private $currency;

    /**
     * Loads the meta-data from context.
     *
     * @param Context $context the context to get the meta-data from.
     * @param int $id_lang the language ID of the shop for which to get the meta-data.
     * @param $uniqueId
     * @return NostoIframe|null
     */
    public static function loadData($context, $id_lang, $uniqueId)
    {
        $iframe = new NostoIframe();
        $shopLanguage = new Language($id_lang);
        $shopContext = $context->shop->getContext();
        if (
            !Validate::isLoadedObject($shopLanguage)
            || $shopContext !== Shop::CONTEXT_SHOP
        ) {
            return null;
        }

        $iframe->setFirstName($context->employee->firstname);
        $iframe->setLastName($context->employee->lastname);
        $iframe->setEmail($context->employee->email);
        $iframe->setLanguageIsoCode($context->language->iso_code);
        $iframe->setLanguageIsoCodeShop($shopLanguage->iso_code);
        $iframe->setPreviewUrlProduct(NostoHelperUrl::getPreviewUrlProduct(null, $id_lang));
        $iframe->setPreviewUrlCategory(NostoHelperUrl::getPreviewUrlCategory(null, $id_lang));
        $iframe->setPreviewUrlSearch(NostoHelperUrl::getPreviewUrlSearch($id_lang));
        $iframe->setPreviewUrlCart(NostoHelperUrl::getPreviewUrlCart($id_lang));
        $iframe->setPreviewUrlFront(NostoHelperUrl::getPreviewUrlHome($id_lang));
        $iframe->setShopName($shopLanguage->name);
        $iframe->setVersionModule(NostoTagging::PLUGIN_VERSION);
        $iframe->setVersionPlatform(_PS_VERSION_);
        $iframe->setUniqueId($uniqueId);
        $iframe->setPlatform('prestashop');

        try {
            //Check the recent visits and sales and get the shop traffic for the qualification
            if (class_exists("AdminStatsControllerCore")) {
                $today = date("Y-m-d");
                $daysBack = new DateTime();
                $beginDate = $daysBack->sub(new DateInterval("P30D"))->format("Y-m-d");
                $sales = AdminStatsControllerCore::getTotalSales($beginDate, $today);
                $visits = AdminStatsControllerCore::getVisits(false, $beginDate, $today);
                $iframe->setRecentVisits(strval($visits));
                $iframe->setRecentSales(number_format((float)$sales));
                $currency = $context->currency;
                if ($currency instanceof Currency) {
                    $iframe->setCurrency($currency->iso_code);
                }
            }
        } catch (Exception $e) {
            //AdminStatsControllerCore is not a public API. Adding a try-catch in case it has been
            //removed or changed.
            NostoHelperLogger::error($e);
        }

        return $iframe;
    }

    /**
     * Return visits in last 30 days
     *
     * @return string
     */
    public function getRecentVisits()
    {
        return $this->recentVisits;
    }

    /**
     * Set visits in last 30 days
     *
     * @param string $recentVisits
     */
    public function setRecentVisits($recentVisits)
    {
        $this->recentVisits = $recentVisits;
    }

    /**
     * Get sales in last 30 days
     *
     * @return string
     */
    public function getRecentSales()
    {
        return $this->recentSales;
    }

    /**
     * Set sales in last 30 days
     *
     * @param string $recentSales
     */
    public function setRecentSales($recentSales)
    {
        $this->recentSales = $recentSales;
    }

    /**
     * Get main currency of the shop
     *
     * @return string $currency
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set main currency of the shop
     *
     * @param string $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }
}
