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
		$context = $this->module->getContext();
		$collection = new NostoExportProductCollection();
		foreach ($this->getProductIds() as $id_product)
		{
			$product = new Product($id_product, true, $context->language->id, $context->shop->id);
			if (!Validate::isLoadedObject($product))
				continue;

			$nosto_product = new NostoTaggingProduct();
			$nosto_product->loadData($context, $product);

			$validator = new NostoValidator($nosto_product);
			if ($validator->validate())
				$collection[] = $nosto_product;

			$product = null;
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
		$product_ids = array();
		$sql = <<<EOT
			SELECT `id_product`
			FROM `ps_product`
			WHERE `active` = 1 AND `available_for_order` = 1
			LIMIT $this->limit
			OFFSET $this->offset
EOT;
		$rows = Db::getInstance()->executeS($sql);
		foreach ($rows as $row)
			$product_ids[] = (int)$row['id_product'];
		return $product_ids;
	}
}
