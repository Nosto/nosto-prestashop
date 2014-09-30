<?php

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
		$nosto_products = array();
		foreach ($this->getProductIds() as $id_product)
		{
			$product = new Product($id_product, true, $this->context->language->id, $this->context->shop->id);
			$nosto_product = $this->module->getProductData($product);
			if (!empty($nosto_product))
				$nosto_products[] = $nosto_product;
			$product = null;
		}

		$this->encryptOutput(json_encode($nosto_products));
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
			WHERE `active` = 1
				AND `available_for_order` = 1
				AND `visibility` != 'none'
			LIMIT $this->limit
			OFFSET $this->offset
EOT;
		$rows = Db::getInstance()->executeS($sql);
		foreach ($rows as $row)
			$product_ids[] = (int)$row['id_product'];
		return $product_ids;
	}
}
