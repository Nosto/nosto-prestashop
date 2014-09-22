<?php

require_once(dirname(__FILE__).'/api.php');

/**
 * Front controller for gathering all products from the shop and sending the meta-data to Nosto.
 *
 * This controller should only be invoked once, when the Nosto module has been installed.
 */
class NostoTaggingProductModuleFrontController extends NostoTaggingApiModuleFrontController
{
    const API_REGISTER_PRODUCTS_URL = 'https://api.nosto.com/register/products';
	const API_BOOTSTRAP_TOKEN = 'yrneZyrBuHTrNPIi31k7Bw9TaPhCGsHrdBozoXkqCKKFwQLqZJBZrAZvyHNpDg1F';

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

		if (!empty($nosto_products))
		{
			$request = new NostoTaggingHttpRequest();
			$response = $request->post(
				self::API_REGISTER_PRODUCTS_URL,
				array(
					'Content-type: application/json',
					'Authorization: Basic '.base64_encode(':'.self::API_BOOTSTRAP_TOKEN)
				),
				gzencode(json_encode($nosto_products), 9)
			);
			if ($response->getCode() !== 200)
				NostoTaggingLogger::log(
					__CLASS__.'::'.__FUNCTION__.' - Failed to send product data to Nosto',
					NostoTaggingLogger::LOG_SEVERITY_ERROR,
					$response->getCode()
				);

			header($response->getRawStatus());
		}
		die;
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
