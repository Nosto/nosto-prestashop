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
class NostoCategoryTagging
{

    /**
     * Tries to resolve current / active category in context
     *
     * @return Category|null
     * @suppress PhanUndeclaredMethod
     */
    private static function resolveCategoryInContext()
    {
        $category = null;
        if (method_exists(Context::getContext()->controller, 'getCategory')) {
            $category = Context::getContext()->controller->getCategory();
        }
        if ($category instanceof Category == false) {
            $id_category = null;
            if (Tools::getValue('id_category')) {
                $id_category = Tools::getValue('id_category');
            } /** @noinspection PhpUndefinedFieldInspection */ elseif (
                isset(Context::getContext()->cookie)
                && (Context::getContext()->cookie->last_visited_category)
            ) {
                /** @noinspection PhpUndefinedFieldInspection */
                $id_category = Context::getContext()->cookie->last_visited_category;
            }
            if ($id_category) {
                $category = new Category($id_category, Context::getContext()->language->id,
                    Context::getContext()->shop->id);
            }
        }
        if (
            $category instanceof Category === false
            || !Validate::isLoadedObject($category)
        ) {
            $category = null;
        }

        return $category;
    }

    /**
     * Render meta-data (tagging) for a category.
     *
     * @return string The rendered HTML
     */
    public static function get()
    {
        $category = self::resolveCategoryInContext();
        if (!$category instanceof Category) {
            return null;
        }

        $nosto_category = new NostoTaggingCategory();
        $nosto_category->loadData(Context::getContext(), $category);

        Context::getContext()->smarty->assign(array(
            'nosto_category' => $nosto_category,
        ));

        return 'views/templates/hook/category-footer_category-tagging.tpl';
    }
}