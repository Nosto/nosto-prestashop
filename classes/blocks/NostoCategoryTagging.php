<?php
/**
 * 2013-2022 Nosto Solutions Ltd
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
 * @copyright 2013-2022 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class NostoCategoryTagging
{
    /**
     * Renders the current category tagging by checking if the underlying controller has
     * an accessor for it and if not, it falls back to using the identifier
     * @return string|null the tagging
     * @throws PrestaShopException
     * @throws ReflectionException
     */
    public static function get()
    {
        $category = NostoHelperController::resolveObject("id_category", 'Category', "getCategory");
        if (!$category instanceof Category) {
            // An edge-case for this tagging block that if the category isn't found, it will use
            // the last viewed category
            /** @noinspection PhpUndefinedFieldInspection */
            if (!isset(Context::getContext()->cookie) && !Context::getContext()->cookie->last_visited_category) {
                return null;
            }

            /** @noinspection PhpUndefinedFieldInspection */
            $idCategory = Context::getContext()->cookie->last_visited_category;
            $category = NostoCategory::loadId($idCategory);
        }
        $nostoCategory = NostoCategory::loadData($category);

        return $nostoCategory ? $nostoCategory->toHtml() : null;
    }
}
