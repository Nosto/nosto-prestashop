<?php
/**
 * 2013-2023 Nosto Solutions Ltd
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

use Nosto\Model\Category\CategoryCollection as NostoSDKCategoryCollection;

/**
 * Front controller for gathering all categories from the shop and sending the meta-data to Nosto.
 *
 * This controller should only be invoked once, when the Nosto module has been installed.
 */
class NostoTaggingCategoryModuleFrontController extends NostoTaggingApiModuleFrontController
{
    /**
     * @inheritdoc
     * @noinspection PhpUnhandledExceptionInspection
     */
    public function initContent()
    {
        // We need to forge the employee in order to get a price for a product
        $employee = new Employee(); //@codingStandardsIgnoreLine

        $controller = $this;

        $id = Tools::getValue(NostoTagging::ID);

        NostoHelperContext::runInContext(
            function () use ($controller) {
                $collection = new NostoSDKCategoryCollection();
                if (Tools::getValue(NostoTagging::ID)) {
                    $category = new Category(
                        Tools::getValue(NostoTagging::ID),
                        true,
                        NostoHelperContext::getLanguageId(),
                        NostoHelperContext::getShopId()
                    );
                    if (!Validate::isLoadedObject($category)) {
                        Controller::getController('PageNotFoundController')->run();
                    }
                    $nostoCategory = NostoCategory::loadData($category);
                    $collection->append($category);
                } else {
                    foreach ($controller->getCategoryIds() as $idCategory) {
                        $category = new Category(
                            $idCategory,
                            true,
                            NostoHelperContext::getLanguageId(),
                            NostoHelperContext::getShopId()
                        );
                        if (!Validate::isLoadedObject($category)) {
                            continue;
                        }

                        $nostoCategory = NostoCategory::loadData($category);
                        if ($nostoCategory) {
                            $collection->append($nostoCategory);
                        }
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
     * Returns a list of all active category ids with limit and offset applied.
     *
     * @return array the product id list.
     * @throws PrestaShopDatabaseException
     */
    protected function getCategoryIds()
    {
        $categoryIds = array();
        $sql = sprintf(
            '
                SELECT id_category
                FROM %scategory
                WHERE active = 1
                LIMIT %d
                OFFSET %d
            ',
            pSQL(_DB_PREFIX_),
            $this->limit,
            $this->offset
        );

        $rows = Db::getInstance()->executeS($sql);
        foreach ($rows as $row) {
            $categoryIds[] = (int)$row['id_category'];
        }

        return $categoryIds;
    }
}
