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
class NostoVariationTagging
{

    /**
     * Render meta-data (tagging) for the price variation in use.
     *
     * This is needed for the multi currency features.
     *
     * @return string The rendered HTML
     */
    public static function get()
    {
        /* @var $currencyHelper NostoTaggingHelperCurrency */
        $currencyHelper = Nosto::helper('nosto_tagging/currency');
        /** @var NostoTaggingHelperConfig $helper_config */
        $helper_config = Nosto::helper('nosto_tagging/config');
        $id_lang = Context::getContext()->language->id;
        $id_shop = null;
        $id_shop_group = null;
        if (Context::getContext()->shop instanceof Shop) {
            $id_shop = Context::getContext()->shop->id;
            $id_shop_group = Context::getContext()->shop->id_shop_group;
        }
        if ($helper_config->useMultipleCurrencies($id_lang, $id_shop_group, $id_shop)) {
            $defaultVariationId = $currencyHelper->getActiveCurrency(Context::getContext());
            $priceVariation = new NostoTaggingPriceVariation($defaultVariationId);
            Context::getContext()->smarty->assign(array(
                'nosto_price_variation' => $priceVariation
            ));

            return 'views/templates/hook/top_price_variation-tagging.tpl';
        }

        return null;
    }
}