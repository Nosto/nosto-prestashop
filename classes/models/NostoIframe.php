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

use Nosto\Model\ConnectionMetadata as NostoConnectionMetadata;

class NostoConnection extends NostoConnectionMetadata
{
    private $recentVisits;
    private $recentSales;
    private $currency;

    /**
     * Loads the meta-data from context.
     *
     * @return NostoConnection|null the conenction object
     * @throws PrestaShopException
     */
    public static function loadData()
    {
        $nostoConnection = new NostoConnection();
        $shopLanguage = new Language(NostoHelperContext::getLanguageId());
        $shopContext = NostoHelperContext::getShop()->getContext();
        if (!Validate::isLoadedObject($shopLanguage)
            || $shopContext !== Shop::CONTEXT_SHOP
        ) {
            return null;
        }

        $nostoConnection->setFirstName(NostoHelperContext::getEmployee()->firstname);
        $nostoConnection->setLastName(NostoHelperContext::getEmployee()->lastname);
        $nostoConnection->setEmail(NostoHelperContext::getEmployee()->email);
        $nostoConnection->setLanguageIsoCode(NostoHelperContext::getLanguage()->iso_code);
        $nostoConnection->setLanguageIsoCodeShop($shopLanguage->iso_code);
        $nostoConnection->setShopName($shopLanguage->name);
        $nostoConnection->setVersionModule(NostoTagging::PLUGIN_VERSION);
        $nostoConnection->setVersionPlatform(_PS_VERSION_);
        $nostoConnection->setUniqueId('');
        $nostoConnection->setPlatform('prestashop');

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
                $nostoConnection->setRecentVisits((string)$visits);
                $nostoConnection->setRecentSales(number_format((float)$sales));
                $currency = NostoHelperContext::getCurrency();
                if ($currency instanceof Currency) {
                    $nostoConnection->setCurrency($currency->iso_code);
                }
            }
        } catch (Exception $e) {
            //AdminStatsControllerCore is not a public API. Adding a try-catch in case it has been
            //removed or changed.
            NostoHelperLogger::error($e);
        }

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoConnection), array(
            'nosto_connection' => $nostoConnection
        ));
        return $nostoConnection;
    }

    /**
     * Return visits in last 30 days
     *
     * @return string
     * @noinspection PhpUnused
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
     * @noinspection PhpUnused
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
     * @noinspection PhpUnused
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
