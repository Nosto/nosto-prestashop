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
class NostoBrandTagging
{

    /**
     * Tries to resolve current / active manufacturer in context
     *
     * @return Manufacturer|null
     * @suppress PhanUndeclaredMethod
     */
    private static function resolveManufacturerInContext()
    {
        $manufacturer = null;
        if (method_exists(Context::getContext()->controller, 'getManufacturer')) {
            $manufacturer = Context::getContext()->controller->getManufacturer();
        }
        if ($manufacturer instanceof Manufacturer == false) {
            $id_manufacturer = null;
            if (Tools::getValue('id_manufacturer')) {
                $id_manufacturer = Tools::getValue('id_manufacturer');
            }
            if ($id_manufacturer) {
                $manufacturer = new Manufacturer((int)$id_manufacturer,
                    Context::getContext()->language->id);
            }
        }
        if (
            $manufacturer instanceof Manufacturer === false
            || !Validate::isLoadedObject($manufacturer)
        ) {
            $manufacturer = null;
        }

        return $manufacturer;
    }

    /**
     * Render meta-data (tagging) for a manufacturer.
     *
     * @return string The rendered HTML
     */
    public static function get()
    {
        $manufacturer = self::resolveManufacturerInContext();
        if (!$manufacturer instanceof Manufacturer) {
            return null;
        }

        $nosto_brand = new NostoBrand();
        $nosto_brand->loadData($manufacturer);

        Context::getContext()->smarty->assign(array(
            'nosto_brand' => $nosto_brand,
        ));

        return 'views/templates/hook/manufacturer-footer_brand-tagging.tpl';
    }
}