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
 * Helper class for produt related tasks.
 */
class NostoTaggingHelperProduct
{
    /**
     * Fetches the base currency from the context.
     *
     * @param Context|ContextCore $context the context.
     * @return Product
     *
     * @throws NostoException if the currency cannot be found, we require it.
     */
    public function getSingleActiveProduct(Context $context)
    {
        $product_ids = $this->getActiveProductIds(100, 0);
        foreach ($product_ids as $id_product) {
            $product = new Product($id_product, true, $context->language->id, $context->shop->id);
            if (!Validate::isLoadedObject($product)) {
                continue;
            }

            if ($product->getPrice(true) > 1) {
                return $product;
            }
        }
    }

    /**
     * Gets all product ids from active products that are available for order
     *
     * @param $limit
     * @param $offset
     * @return array
     * @throws PrestaShopDatabaseException
     */
    protected function getActiveProductIds($limit, $offset)
    {
        $product_ids = array();
        $sql = sprintf(
            '
                SELECT id_product
                FROM %sproduct
                WHERE active = 1 AND available_for_order = 1
                LIMIT %d
                OFFSET %d
            ',
            _DB_PREFIX_,
            $limit,
            $offset
        );

        $rows = Db::getInstance()->executeS($sql);
        foreach ($rows as $row) {
            $product_ids[] = (int)$row['id_product'];
        }
        return $product_ids;
    }
}
