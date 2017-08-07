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
 * Meta data class for account iframe related information needed when showing the admin iframe on
 * module settings page.
 */
class NostoTaggingMetaAccountIframe extends Nosto\Object\Iframe
{
    protected $recentVisits;
    protected $recentSales;
    private $currency;

    /**
     * Loads the meta-data from context.
     *
     * @param Context $context the context to get the meta-data from.
     * @param int $id_lang the language ID of the shop for which to get the meta-data.
     * @param $uniqueId
     * @return NostoTaggingMetaAccountIframe|null
     */
    public static function loadData($context, $id_lang, $uniqueId)
    {
        $iframe = new NostoTaggingMetaAccountIframe();
        $shop_language = new Language($id_lang);
        $shop_context = $context->shop->getContext();
        if (
            !Validate::isLoadedObject($shop_language)
            || $shop_context !== Shop::CONTEXT_SHOP
        ) {
            return null;
        }

        /** @var NostoTaggingHelperUrl $url_helper */
        $url_helper = Nosto::helper('nosto_tagging/url');

        $iframe->setFirstName($context->employee->firstname);
        $iframe->setLastName($context->employee->lastname);
        $iframe->setEmail($context->employee->email);
        $iframe->setLanguageIsoCode($context->language->iso_code);
        $iframe->setLanguageIsoCodeShop($shop_language->iso_code);
        $iframe->setPreviewUrlProduct($url_helper->getPreviewUrlProduct(null, $id_lang));
        $iframe->setPreviewUrlCategory($url_helper->getPreviewUrlCategory(null, $id_lang));
        $iframe->setPreviewUrlSearch($url_helper->getPreviewUrlSearch($id_lang));
        $iframe->setPreviewUrlCart($url_helper->getPreviewUrlCart($id_lang));
        $iframe->setPreviewUrlFront($url_helper->getPreviewUrlHome($id_lang));
        $iframe->setShopName($shop_language->name);
        $iframe->setVersionModule(NostoTagging::PLUGIN_VERSION);
        $iframe->setVersionPlatform(_PS_VERSION_);
        $iframe->setUniqueId($uniqueId);
        $iframe->setPlatform('prestashop');

        try {
            //check the recent visits and sales
            //get the shop traffic for the qualification check
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
            //AdminStatsControllerCore is none public api. Add a try/catch incase it has been removed or changed.
            NostoHelperLogger::error(
                __CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
                $e->getCode()
            );
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
