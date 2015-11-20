<?php
/**
 * 2013-2015 Nosto Solutions Ltd
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
 * @copyright 2013-2015 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once(dirname(__FILE__).'/api.php');

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
		$context = Context::getContext();
		/** @var LanguageCore $language */
		$language = $context->language;
		/** @var ShopCore $shop */
		$shop = $context->shop;

		$collection = new NostoExportCollectionProduct();
		foreach ($this->getProductIds() as $id_product)
		{
			$product = new Product($id_product, true, $language->id, $shop->id);
			if (!Validate::isLoadedObject($product))
				continue;

			try {
				$nosto_product = new NostoTaggingProduct();
				$nosto_product->loadData($product);
				$collection[] = $nosto_product;
			} catch (NostoException $e) {
				continue;
			}
		}

		$this->encryptOutput($collection);
	}

	/**
	 * Returns a list of all active product ids with limit and offset applied.
	 *
	 * @return array the product id list.
	 */
	protected function getProductIds()
	{
		$where = sprintf(
			'`active` = %d
				AND
			`available_for_order` = %d',
			1,
			1
		);

		if (!empty($ids = $this->getIds()) && count($this->getIds() > 0)) {

			$ids = array_map('intval', $ids);

			$where .= sprintf(
				' AND `id_product` IN(%s)',
				implode(',', $ids)
			);
		}

		if (!empty($this->getId())) {
			$where .= sprintf(
				' AND `id_product` = %d',
				(int)$this->getId()
			);
		}

		$product_ids = array();
		$sql = <<<EOT
			SELECT `id_product`
			FROM `ps_product`
			WHERE $where
			LIMIT $this->limit
			OFFSET $this->offset
EOT;
		$rows = Db::getInstance()->executeS($sql);
		foreach ($rows as $row)
			$product_ids[] = (int)$row['id_product'];
		return $product_ids;
	}
}
