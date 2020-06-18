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
 * @copyright 2013-2019 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\Object\Iframe as NostoSDKIframe;

class NostoIframe extends NostoSDKIframe
{
    private $recentVisits;
    private $recentSales;
    private $currency;

    /**
     * Loads the meta-data from context.
     *
     * @return NostoIframe|null the iframe object
     */
    public static function loadData()
    {
        $nostoIframe = new NostoIframe();
        $shopLanguage = new Language(NostoHelperContext::getLanguageId());
        $shopContext = NostoHelperContext::getShop()->getContext();
        if (!Validate::isLoadedObject($shopLanguage)
            || $shopContext !== Shop::CONTEXT_SHOP
        ) {
            return null;
        }

        $nostoIframe->setFirstName(NostoHelperContext::getEmployee()->firstname);
        $nostoIframe->setLastName(NostoHelperContext::getEmployee()->lastname);
        $nostoIframe->setEmail(NostoHelperContext::getEmployee()->email);
        $nostoIframe->setLanguageIsoCode(NostoHelperContext::getLanguage()->iso_code);
        $nostoIframe->setLanguageIsoCodeShop($shopLanguage->iso_code);
        $nostoIframe->setPreviewUrlProduct(NostoHelperUrl::getPreviewUrlProduct());
        $nostoIframe->setPreviewUrlCategory(NostoHelperUrl::getPreviewUrlCategory());
        $nostoIframe->setPreviewUrlSearch(NostoHelperUrl::getPreviewUrlSearch());
        $nostoIframe->setPreviewUrlCart(NostoHelperUrl::getPreviewUrlCart());
        $nostoIframe->setPreviewUrlFront(NostoHelperUrl::getPreviewUrlHome());
        $nostoIframe->setShopName($shopLanguage->name);
        $nostoIframe->setVersionModule(NostoTagging::PLUGIN_VERSION);
        $nostoIframe->setVersionPlatform(_PS_VERSION_);
        $nostoIframe->setUniqueId('');
        $nostoIframe->setPlatform('prestashop');

        try {
            //Check the recent visits and sales and get the shop traffic for the qualification
            if (class_exists('AdminStatsControllerCore')
                && method_exists('AdminStatsControllerCore', 'getTotalSales')
                && method_exists('AdminStatsControllerCore', 'getVisits')
            ) {
                $today = date("Y-m-d");
                $daysBack = new DateTime();
                $beginDate = $daysBack->sub(new DateInterval("P30D"))->format("Y-m-d");
                $sales = AdminStatsControllerCore::getTotalSales($beginDate, $today);
                $visits = AdminStatsControllerCore::getVisits(false, $beginDate, $today);
                $nostoIframe->setRecentVisits((string)$visits);
                $nostoIframe->setRecentSales(number_format((float)$sales));
                $currency = NostoHelperContext::getCurrency();
                if ($currency instanceof Currency) {
                    $nostoIframe->setCurrency($currency->iso_code);
                }
            }
        } catch (Exception $e) {
            //AdminStatsControllerCore is not a public API. Adding a try-catch in case it has been
            //removed or changed.
            NostoHelperLogger::error($e);
        }

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoIframe), array(
            'nosto_iframe' => $nostoIframe
        ));
        return $nostoIframe;
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
