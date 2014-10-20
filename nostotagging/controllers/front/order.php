<?php

require_once(dirname(__FILE__).'/api.php');

/**
 * Front controller for gathering all existing orders from the shop and sending the meta-data to Nosto.
 *
 * This controller should only be invoked once, when the Nosto module has been installed.
 */
class NostoTaggingOrderModuleFrontController extends NostoTaggingApiModuleFrontController
{
	/**
	 * @inheritdoc
	 */
	public function initContent()
	{
		$nosto_orders = array();
		foreach ($this->getOrderIds() as $id_order)
		{
			$order = new Order($id_order);
			$nosto_order = $this->module->getOrderData($order);
			if (!empty($nosto_order))
				$nosto_orders[] = $nosto_order;
			$order = null;
			$currency = null;
		}

		$this->encryptOutput(json_encode($nosto_orders));
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