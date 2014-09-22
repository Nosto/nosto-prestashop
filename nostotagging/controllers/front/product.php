<?php

class NostoTaggingProductModuleFrontController extends NostoTaggingApiModuleFrontController
{
    const NOSTOTAGGING_API_REGISTER_ORDERS_URL = 'http://localhost:9000/api/register/products';

    public function initContent()
    {
        $products_sql = <<<EOT
            SELECT p.id_product AS product_id,
                   pl.name,
                   GROUP_CONCAT(DISTINCT(cl.name) SEPARATOR ",") AS categories,
                   p.price,
                   p.reference, 
                   p.supplier_reference,
                   p.id_manufacturer AS brand,
                   p.upc,
                   IFNULL(pl.description_short, pl.description) AS description,
                   GROUP_CONCAT(DISTINCT(pa.name) SEPARATOR "," ) AS tags,
                   pl.available_now,
                   pl.available_later,
                   p.available_for_order
              FROM ps_product p
              LEFT 
              JOIN ps_product_lang pl 
                ON p.id_product = pl.id_product
              LEFT 
              JOIN ps_category_product cp 
                ON p.id_product = cp.id_product
              LEFT 
              JOIN ps_category_lang cl 
                ON cp.id_category = cl.id_category
              LEFT 
              JOIN ps_category c 
                ON cp.id_category = c.id_category
              LEFT 
              JOIN ps_product_tag pt 
                ON p.id_product = pt.id_product
              LEFT
              JOIN ps_tag pa
                ON pt.id_tag = pa.id_tag
             WHERE pl.id_lang = 1
               AND cl.id_lang = 1
               AND p.id_shop_default = 1
               AND c.id_shop_default = 1
               AND p.active = 1
             GROUP 
                BY p.id_product
             LIMIT $this->limit
            OFFSET $this->offset
EOT;

print("vxvxv");
die;
        if ($products = Db::getInstance()->ExecuteS($products_sql))
        {
            header('Content-Type: application/json');
            print(json_encode($products, JSON_PRETTY_PRINT));
        }
        die;
    }
}
?>