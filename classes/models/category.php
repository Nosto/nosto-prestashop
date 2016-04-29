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
 * Model for tagging categories.
 */
class NostoTaggingCategory extends NostoTaggingModel
{
    /**
     * @var string the built category string.
     */
    public $category_string;

    /**
     * Loads the category data from supplied context and category objects.
     *
     * @param Context $context the context object.
     * @param Category $category the category object.
     */
    public function loadData(Context $context, Category $category)
    {
        if (!Validate::isLoadedObject($category)) {
            return;
        }

        $this->category_string = self::buildCategoryString($category->id, $context->language->id);
    }

    /**
     * Builds a tagging string of the given category including all its parent categories.
     *
     * @param int $id_category
     * @param int $id_lang
     * @return string
     */
    public static function buildCategoryString($id_category, $id_lang)
    {
        $category_list = array();

        $category = new Category((int)$id_category, $id_lang);

        if (Validate::isLoadedObject($category) && (int)$category->active === 1) {
            foreach ($category->getParentsCategories($id_lang) as $parent_category) {
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

        return DS.implode(DS, array_reverse($category_list));
    }
}
