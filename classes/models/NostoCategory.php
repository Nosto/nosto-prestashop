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

use \Nosto\Object\Category as NostoSDKCatetory;

class NostoCategory extends NostoSDKCatetory
{
    /**
     * @param $idCategory
     * @return Category
     * @suppress PhanTypeMismatchArgument
     */
    public static function loadId($idCategory)
    {
        return new Category((int)$idCategory, NostoHelperContext::getLanguageId());
    }

    public function __construct($category)
    {
        parent::__construct($category);
    }

    /**
     * Loads the category data from supplied context and category objects.
     * Builds a tagging string of the given category including all its parent categories.
     *
     * @param Category $category the category model to process
     * @return NostoCategory|null the category object
     */
    public static function loadData(Category $category)
    {
        if (!Validate::isLoadedObject($category)) {
            return null;
        }

        $categoryList = array();
        if ((int)$category->active === 1) {
            foreach ($category->getParentsCategories(NostoHelperContext::getLanguageId()) as $parentCategory) {
                if (isset($parentCategory['name'], $parentCategory['active'])
                    && (int)$parentCategory['active'] === 1
                ) {
                    $categoryList[] = (string)$parentCategory['name'];
                }
            }
        }

        if (empty($categoryList)) {
            return null;
        }

        $nostoCategory = new NostoCategory(
            implode(DIRECTORY_SEPARATOR, array_reverse($categoryList))
            . DIRECTORY_SEPARATOR
        );

        NostoHelperHook::dispatchHookActionLoadAfter(get_class($nostoCategory), array(
            'category' => $category,
            'nosto_category' => $nostoCategory
        ));
        return $nostoCategory;
    }
}
