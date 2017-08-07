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
class NostoCategory extends AbstractNostoModel
{
    /**
     * @var string the built category string.
     */
    public $category_string;

    /**
     * @param $id_category
     * @param $id_lang
     * @return Category
     * @suppress PhanTypeMismatchArgument
     */
    public static function loadId($id_category, $id_lang)
    {
        return new Category((int)$id_category, $id_lang);
    }

    /**
     * Loads the category data from supplied context and category objects.
     * Builds a tagging string of the given category including all its parent categories.
     *
     * @param Context $context the context object.
     * @param Category $category the category object.
     * @return string
     */
    public static function loadData(Context $context, Category $category)
    {
        if (!Validate::isLoadedObject($category)) {
            return null;
        }

        $category_list = array();
        if ((int)$category->active === 1) {
            foreach ($category->getParentsCategories($context->language->id) as $parent_category) {
                if (isset($parent_category['name'], $parent_category['active'])
                    && (int)$parent_category['active'] === 1
                ) {
                    $category_list[] = (string)$parent_category['name'];
                }
            }
        }

        if (empty($category_list)) {
            return '';
        }

        return DIRECTORY_SEPARATOR . implode(DIRECTORY_SEPARATOR, array_reverse($category_list));
    }
}
