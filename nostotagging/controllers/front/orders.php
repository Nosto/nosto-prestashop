<?php
 
if (!defined('_PS_VERSION_'))
        exit;
include('../../config/config.inc.php');
class NostoTaggingAPIModuleFrontController extends ModuleFrontController
{
    const NOSTOTAGGING_API_REGISTER_ORDERS_URL = 'http://localhost:9000/api/register/orders';
	const NOSTOTAGGING_API_BOOTSTRAP_TOKEN = 'yrneZyrBuHTrNPIi31k7Bw9TaPhCGsHrdBozoXkqCKKFwQLqZJBZrAZvyHNpDg1F';

    public function initContent()
    {

        $limit = empty(Tools::getValue('limit')) ? 100000 : Tools::getValue('limit');
        $offset = empty(Tools::getValue('offset')) ? 0 : Tools::getValue('offset');

        $orders_sql = <<<EOT
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
             LIMIT $limit
            OFFSET $offset
EOT;

        if ($orders = Db::getInstance()->ExecuteS($orders_sql))
        {
            foreach ($orders as &$order)
            {
                $product_sql = <<<'EOT'
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
                              WHERE d.id_order = 
EOT;
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
}
?>