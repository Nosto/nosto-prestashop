<?php

require_once(dirname(__FILE__).'/api.php');

/**
 * Front controller for gathering all existing orders from the shop and sending the meta-data to Nosto.
 *
 * This controller should only be invoked once, when the Nosto module has been installed.
 */
class NostoTaggingOrderModuleFrontController extends NostoTaggingApiModuleFrontController
{
    const API_REGISTER_ORDERS_URL = 'https://api.nosto.com/register/orders';
	const API_BOOTSTRAP_TOKEN = 'yrneZyrBuHTrNPIi31k7Bw9TaPhCGsHrdBozoXkqCKKFwQLqZJBZrAZvyHNpDg1F';

	/**
	 * @inheritdoc
	 */
	public function initContent()
    {
		$nosto_orders = array();
		foreach ($this->getOrderIds() as $id_order)
		{
			$order = new Order($id_order);
			$currency = new Currency($order->id_currency);
			$nosto_order = $this->module->getOrderData($order, $currency);
			if (!empty($nosto_order))
				$nosto_orders[] = $nosto_order;
			$order = null;
			$currency = null;
		}

		if (!empty($nosto_orders))
		{
			$request = new NostoTaggingHttpRequest();
			$response = $request->post(
				self::API_REGISTER_ORDERS_URL,
				array(
					'Content-type: application/json',
					'Authorization: Basic '.base64_encode(':'.self::API_BOOTSTRAP_TOKEN)
				),
				gzencode(json_encode($nosto_orders), 9)
			);
			if ($response->getCode() !== 200)
				NostoTaggingLogger::log(
					__CLASS__.'::'.__FUNCTION__.' - Failed to send order history to Nosto',
					NostoTaggingLogger::LOG_SEVERITY_ERROR,
					$response->getCode()
				);

			header($response->getRawStatus());
		}
		die;
    }

	/**
	 * Returns a list of all order ids with limit and offset applied.
	 *
	 * @return array the order id list.
	 */
	protected function getOrderIds()
	{
		$order_ids = array();
		$sql = <<<EOT
            SELECT `id_order`
            FROM `ps_orders`
            LIMIT $this->limit
            OFFSET $this->offset
EOT;
		$rows = Db::getInstance()->executeS($sql);
		foreach ($rows as $row)
			$order_ids[] = (int)$row['id_order'];
		return $order_ids;
	}
}
