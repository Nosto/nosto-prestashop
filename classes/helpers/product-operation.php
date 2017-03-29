<?php
/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for sending product create/update/delete events to Nosto.
 */
class NostoTaggingHelperProductOperation extends NostoTaggingHelperOperation
{
    /**
     * Maxmium batch size. If exceeded the batches will be splitted into smaller
     * ones.
     *
     * @var int
     */
    public static $maxBatchSize = 500;

    /**
     * Max time to wait for Nosto's API response
     *
     * @var int
     */
    public static $apiWaitTimeout = 60;

    /**
     * Array key for data
     * @var string
     */
    const KEY_DATA = 'data';

    /**
     * Array key for account
     * @var string
     */
    const KEY_ACCOUNT = 'account';

    /**
     * @var array runtime cache for products that have already been processed during this request to avoid sending the
     * info to Nosto many times during the same request. This will otherwise happen as PrestaShop will sometime invoke
     * the hook callback methods multiple times when saving a product.
     */
    private static $processedProducts = array();

    /**
     * Updates a batch of products to Nosto
     *
     * @param Product[] $products
     * @return bool
     */
    public function updateBatch(array $products)
    {
        return $this->update($products);
    }

    /**
     * Updates a single product to Nosto
     *
     * @param Product $product
     * @return bool
     */
    public function updateProduct(Product $product)
    {
        return $this->update(array($product));
    }

    /**
     * Sends a product update to Nosto for all stores and installed Nosto
     * accounts
     *
     * @param Product[] $products
     * @return bool
     * @throws NostoException
     */
    private function update(array $products)
    {
        NostoHttpRequest::$responseTimeout = self::$apiWaitTimeout;
        $products_in_store = array();
        $counter = 0;
        $batch = 1;
        foreach ($products as $product) {
            if ($counter > 0 && $counter % self::$maxBatchSize === 0) {
                ++$batch;
            }
            ++$counter;
            if (
                $product instanceof Product === false
                || !Validate::isLoadedObject($product)
            ) {
                Nosto::throwException(
                    sprintf(
                        'Invalid data type or not loaded objec, expecting Product' .
                        ', got %s with id %s',
                        get_class($product),
                        $product->id
                    )
                );
            }
            if (in_array($product->id, self::$processedProducts)) {
                continue;
            }
            self::$processedProducts[] = $product->id;
            foreach ($this->getAccountData() as $data) {
                /** @var NostoAccount $account */
                list($account, $id_shop, $id_lang) = $data;
                $account_name = $account->getName();
                $nosto_product = $this->loadNostoProduct($product->id, $id_lang, $id_shop);
                if ($nosto_product instanceof NostoTaggingProduct === false) {
                    continue;
                }
                if (!isset($products_in_store[$account_name])) {
                    $products_in_store[$account_name] = array();
                }
                if (!isset($products_in_store[$account_name][self::KEY_ACCOUNT])) {
                    $products_in_store[$account_name][self::KEY_ACCOUNT] = $account;
                }
                if (!isset($products_in_store[$account_name][self::KEY_DATA])) {
                    $products_in_store[$account_name][self::KEY_DATA] = array();
                }

                if (!isset($products_in_store[$account_name][self::KEY_DATA][$batch])) {
                    $products_in_store[$account_name][self::KEY_DATA][$batch] = array();
                }
                $products_in_store[$account_name][self::KEY_DATA][$batch][] = $nosto_product;
            }
        }
        foreach ($products_in_store as $nosto_account_name => $data) {
            $nosto_account = $data[self::KEY_ACCOUNT];
            foreach ($data[self::KEY_DATA] as $batchIndex => $batches) {
                $op = new NostoOperationProduct($nosto_account);
                foreach ($batches as $product) {
                    $op->addProduct($product);
                }
                try {
                    $op->upsert();
                } catch (Exception $e) {
                    /* @var NostoTaggingHelperLogger $logger */
                    $logger = Nosto::helper('nosto_tagging/logger');
                    $logger->error(
                        __CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
                        $e->getCode()
                    );
                }
            }
        }

        return true;
    }

    /**
     * Sends a product create API request to Nosto.
     *
     * @param Product $product the product that has been created.
     * @return self::updateProduct()
     */
    public function create(Product $product)
    {
        return $this->updateProduct($product);
    }

    /**
     * Sends a product delete API request to Nosto.
     *
     * @param Product $product the product that has been deleted.
     */
    public function delete(Product $product)
    {
        if (!Validate::isLoadedObject($product) || in_array($product->id, self::$processedProducts)) {
            return;
        }

        self::$processedProducts[] = $product->id;
        foreach ($this->getAccountData() as $data) {
            list($account) = $data;

            $nosto_product = new NostoTaggingProduct();
            $nosto_product->assignId($product);

            try {
                $op = new NostoOperationProduct($account);
                $op->addProduct($nosto_product);
                $op->delete();
            } catch (NostoException $e) {
                /* @var NostoTaggingHelperLogger $logger */
                $logger = Nosto::helper('nosto_tagging/logger');
                $logger->error(
                    __CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
                    $e->getCode(),
                    get_class($product),
                    (int)$product->id
                );
            }
        }
    }

    /**
     * Loads a Nosto product model for given PS product ID, language ID and shop ID.
     *
     * @param int $id_product the PS product ID.
     * @param int $id_lang the language ID.
     * @param int $id_shop the shop ID.
     * @return NostoTaggingProduct|null the product or null if could not be loaded.
     */
    protected function loadNostoProduct($id_product, $id_lang, $id_shop)
    {
        $product = new Product($id_product, true, $id_lang, $id_shop);
        if (!Validate::isLoadedObject($product)) {
            return null;
        }
        /* @var NostoTaggingHelperContextFactory $context_factory */
        $context_factory = Nosto::helper('nosto_tagging/context_factory');
        $forged_context = $context_factory->forgeContext($id_lang, $id_shop);
        $nosto_product = new NostoTaggingProduct();
        $nosto_product->loadData($forged_context, $product);
        $context_factory->revertToOriginalContext();

        return $nosto_product;
    }
}
