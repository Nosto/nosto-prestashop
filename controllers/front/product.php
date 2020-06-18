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

require_once(dirname(__FILE__) . '/api.php');

use Nosto\Object\Product\ProductCollection as NostoSDKProductCollection;

/**
 * Front controller for gathering all products from the shop and sending the meta-data to Nosto.
 *
 * This controller should only be invoked once, when the Nosto module has been installed.
 */
class NostoTaggingProductModuleFrontController extends NostoTaggingApiModuleFrontController
{
    /**
     * @inheritdoc
     */
    public function initContent()
    {
        // We need to forge the employee in order to get a price for a product
        $employee = new Employee(); //@codingStandardsIgnoreLine

        $controller = $this;
        NostoHelperContext::runInContext(
            function () use ($controller) {
                $collection = new NostoSDKProductCollection();
                if (Tools::getValue(NostoTagging::ID)) {
                    $product = new Product(
                        Tools::getValue(NostoTagging::ID),
                        true,
                        NostoHelperContext::getLanguageId(),
                        NostoHelperContext::getShopId()
                    );
                    if (!Validate::isLoadedObject($product)) {
                        Controller::getController('PageNotFoundController')->run();
                    }
                    $nostoProduct = NostoProduct::loadData($product);
                    $collection->append($nostoProduct);
                } else {
                    foreach ($controller->getProductIds() as $idProduct) {
                        $product = new Product(
                            $idProduct,
                            true,
                            NostoHelperContext::getLanguageId(),
                            NostoHelperContext::getShopId()
                        );
                        if (!Validate::isLoadedObject($product)) {
                            continue;
                        }

                        $nostoProduct = NostoProduct::loadData($product);
                        $collection->append($nostoProduct);
                    }
                }
                $controller->encryptOutput($collection);
            },
            false,
            false,
            false,
            $employee->id
        );
    }

    /**
     * Returns a list of all active product ids with limit and offset applied.
     *
     * @return array the product id list.
     */
    protected function getProductIds()
    {
        $productIds = array();
        /** @noinspection SqlNoDataSourceInspection */
        $sql = sprintf(
            '
                SELECT id_product
                FROM %sproduct
                WHERE active = 1 AND available_for_order = 1
                LIMIT %d
                OFFSET %d
            ',
            pSQL(_DB_PREFIX_),
            $this->limit,
            $this->offset
        );

        $rows = Db::getInstance()->executeS($sql);
        foreach ($rows as $row) {
            $productIds[] = (int)$row['id_product'];
        }

        return $productIds;
    }
}
