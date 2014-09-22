<?php

class NostoTaggingOrderModuleFrontController extends NostoTaggingApiModuleFrontController
{
    const NOSTOTAGGING_API_REGISTER_ORDERS_URL = 'http://localhost:9000/api/register/orders';
	const NOSTOTAGGING_API_BOOTSTRAP_TOKEN = 'yrneZyrBuHTrNPIi31k7Bw9TaPhCGsHrdBozoXkqCKKFwQLqZJBZrAZvyHNpDg1F';

    public function initContent()
    {
		$orders = $this->getOrders();
		if (!empty($orders))
		{
			$nosto_orders = array();
            foreach ($orders as $order)
            {
				$products = $this->getOrderProducts($order);
				if (!empty($products))
					$nosto_orders[] = array(
						'customer' => array(
							'order_number' => 0,
							'first_name' => '',
							'last_name' => '',
						),
						'created_at' => $order['order_date'],
						'purchased_items' => $products,
					);


                if ($products = Db::getInstance()->ExecuteS($product_sql.$order['order_number']))
                    $order = array(
                        'customer' => $order);
                    $order['created_at'] = $order['customer']['order_date'];
                    $order['purchased_items'] = $products;
            }

			$headers = array(
				'Content-type: application/json',
				'Authorization: Basic '.base64_encode(':'.self::NOSTOTAGGING_API_BOOTSTRAP_TOKEN)
			);
			$options = array(
				'http' => array(
					'method' => 'POST',
					'header' => implode("\r\n", $headers)."\r\n",
					'content' => gzencode(json_encode($orders), 9),
				)
			);
			$context = stream_context_create($options);
			file_get_contents(self::NOSTOTAGGING_API_REGISTER_ORDERS_URL, false, $context);
            header($http_response_header[0]);
        }
        die;
    }

	protected function getOrders()
	{
		$sql = <<<EOT
            SELECT o.id_order AS order_number,
                   UNIX_TIMESTAMP (o.date_upd) AS order_date,
                   g.firstname AS first_name,
                   g.lastname AS last_name,
                   g.email AS email
              FROM ps_orders o
              LEFT
              JOIN ps_customer g ON (o.id_customer = g.id_customer)
              LEFT
              JOIN ps_order_state_lang os ON (o.current_state = os.id_order_state)
             WHERE os.id_lang = 1
               AND os.id_order_state = 1
             LIMIT $this->limit
            OFFSET $this->offset
EOT;
		return Db::getInstance()->executeS($sql);
	}

	protected function getOrderProducts($order)
	{
		if (!isset($order['order_number']))
			return array();

		$order_number = (int)$order['order_number'];
		$sql = <<<EOT
			 SELECT d.product_name AS name,
					d.product_reference AS product_id,
					d.product_price AS unit_price,
					d.product_quantity AS quantity,
					c.iso_code AS price_currency_code
			   FROM ps_order_detail d
			   JOIN ps_orders o
				 ON o.id_order = d.id_order
			   JOIN ps_currency c
				 ON c.id_currency = o.id_currency
			  WHERE d.id_order = $order_number
EOT;
		return Db::getInstance()->executeS($sql);
	}
}