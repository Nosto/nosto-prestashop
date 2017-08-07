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
class NostoProductTagging
{

    /**
     * Tries to resolve current / active product in context
     *
     * @return null|Product
     * @suppress PhanUndeclaredMethod
     */
    private static function resolveProductInContext()
    {
        $product = null;
        if (method_exists(Context::getContext()->controller, 'getProduct')) {
            $product = Context::getContext()->controller->getProduct();
        }
        // If product is not set try to get use parameters (mostly for Prestashop < 1.5)
        if ($product instanceof Product == false) {
            $id_product = null;
            if (Tools::getValue('id_product')) {
                $id_product = Tools::getValue('id_product');
            }
            if ($id_product) {
                $product = new Product($id_product, true, Context::getContext()->language->id);
            }
        }
        if (
            $product instanceof Product == false
            || !Validate::isLoadedObject($product)
        ) {
            $product = null;
        }

        return $product;
    }

    /**
     * Render meta-data (tagging) for a product.
     *
     * @return string The rendered HTML
     */
    public static function get()
    {
        $product = self::resolveProductInContext();
        if (!$product instanceof Category) {
            return null;
        }

        $nosto_product = new NostoTaggingProduct();
        $nosto_product->loadData(Context::getContext(), $product);

        $params = array('nosto_product' => $nosto_product);

        if (Validate::isLoadedObject($category)) {
            $nosto_category = new NostoTaggingCategory();
            $nosto_category->loadData(Context::getContext(), $category);
            $params['nosto_category'] = $nosto_category;
        }

        Context::getContext()->smarty->assign($params);
        return 'views/templates/hook/footer-product_product-tagging.tpl';
    }
}