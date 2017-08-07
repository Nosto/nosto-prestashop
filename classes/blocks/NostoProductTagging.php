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

class NostoProductTagging extends NostoCategoryTagging
{
    /**
     * Render meta-data (tagging) for a product.
     *
     * @return string The rendered HTML
     */
    public static function get()
    {
        $product = NostoHelperController::resolveObject("id_product", Product::class, "getProduct");
        if (!$product instanceof Product) {
            return null;
        }

        $nostoProduct = NostoProduct::loadData(Context::getContext(), $product);
        $params = array('nosto_product' => $nostoProduct);


        $category = NostoHelperController::resolveObject("id_category", Category::class, "getCategory");
        if (Validate::isLoadedObject($category)) {
            $nostoCategory = NostoCategory::loadData(Context::getContext(), $category);
            $params['nosto_category'] = $nostoCategory;
        }

        Context::getContext()->smarty->assign($params);
        return 'views/templates/hook/footer-product_product-tagging.tpl';
    }
}