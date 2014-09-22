<?php
 
if (!defined('_PS_VERSION_'))
        exit;
include('../../config/config.inc.php');
class NostoTaggingAPIModuleFrontController extends ModuleFrontController
{
    const NOSTOTAGGING_API_REGISTER_ORDERS_URL = 'http://localhost:9000/api/register/products';

    public function initContent()
    {

        $limit = empty(Tools::getValue('limit')) ? 100000 : Tools::getValue('limit');
        $offset = empty(Tools::getValue('offset')) ? 0 : Tools::getValue('offset');

        $products_sql = <<<EOT
            SELECT p.id_product AS product_id,
                   pl.name,
                   GROUP_CONCAT(DISTINCT(cl.name) SEPARATOR ",") AS categories,
                   p.price,
                   p.reference, 
                   m.name AS brand,
                   IFNULL(pl.description_short, pl.description) AS description,
                   GROUP_CONCAT(DISTINCT(pa.name) SEPARATOR "," ) AS tags,
                   pl.available_now,
                   pl.available_later,
                   CONCAT('http://', IFNULL(pc.value,'example.com'), '/img/p/', p.id_product, '-' , pi.id_image, '.jpg') as url_image,
                   CONCAT('http://', IFNULL(pc.value,'example.com'), '/', p.id_product, '-' , pl.link_rewrite, '.html') as url
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
              JOIN ps_manufacturer m
                ON p.id_manufacturer = m.id_manufacturer
               AND m.active = 1 
              LEFT
              JOIN ps_image pi 
                ON p.id_product = pi.id_product
              LEFT 
              JOIN ps_product_tag pt 
                ON p.id_product = pt.id_product
              LEFT
              JOIN ps_tag pa
                ON pt.id_tag = pa.id_tag
              LEFT
              JOIN ps_configuration pc
                ON pc.name = 'PS_SHOP_DOMAIN'
             WHERE pl.id_lang = 1
               AND cl.id_lang = 1
               AND p.id_shop_default = 1
               AND c.id_shop_default = 1
               AND p.active = 1
               AND p.available_for_order = 1
               AND visibility != 'none'
             GROUP 
                BY p.id_product
             LIMIT $limit
            OFFSET $offset
EOT;

        if ($products = Db::getInstance()->ExecuteS($products_sql))
        {
            foreach ($products as &$product)
            {
                $x = new Product($product['product_id']);
                $product['price'] = $x->getPrice();
                $product['categories'] = $x->buildCategoryString();
                
            }
            header('Content-Type: application/json');
            print(json_encode($products, JSON_PRETTY_PRINT));
        }
        die;
    }
}
?>