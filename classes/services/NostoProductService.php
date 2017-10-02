<?php
/**
 * 2013-2017 Nosto Solutions Ltd
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
 * @copyright 2013-2017 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

use Nosto\NostoException as NostoSDKException;
use Nosto\Operation\UpsertProduct as NostoSDKUpsertProductOperation;
use Nosto\Request\Http\HttpRequest as NostoSDKHttpRequest;
use Nosto\Types\Product\ProductInterface as NostoSDKProductInterface;

/**
 * Helper class for sending product create/update/delete events to Nosto.
 */
class NostoProductService extends AbstractNostoService
{
    /**
     * Maximum batch size. If exceeded the batches will be split into smaller
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
     *
     * @var string
     */
    const KEY_DATA = 'data';

    /**
     * Array key for account
     *
     * @var string
     */
    const KEY_ACCOUNT = 'account';

    /**
     * @var array runtime cache for products that have already been processed during this request
     *     to avoid sending the info to Nosto many times during the same request. This will
     *     otherwise happen as PrestaShop will sometime invoke the hook callback methods multiple
     *     times when saving a product.
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

    private function getProductCacheKey(Product $product)
    {
        return NostoHelperContext::getShopId() . '-' . NostoHelperContext::getLanguageId() . '-' . $product->id;
    }

    /**
     * Sends a product update to Nosto for all stores and installed Nosto
     * accounts
     *
     * @param Product[] $products
     * @return bool
     * @throws NostoSDKException
     */
    private function update(array $products)
    {
        NostoSDKHttpRequest::$responseTimeout = self::$apiWaitTimeout;
        $productsInStore = array();
        $counter = 0;
        $batch = 1;
        foreach ($products as $product) {
            if ($counter > 0 && $counter % self::$maxBatchSize === 0) {
                ++$batch;
            }
            ++$counter;
            if ($product instanceof Product === false
                || !Validate::isLoadedObject($product)
            ) {
                throw new NostoSDKException(
                    sprintf(
                        'Invalid data type or not loaded object, expecting Product' .
                        ', got %s with id %s',
                        get_class($product),
                        $product->id
                    )
                );
            }
            if (in_array($this->getProductCacheKey($product), self::$processedProducts)) {
                continue;
            }
            self::$processedProducts[] = $this->getProductCacheKey($product);

            $nostoAccount = NostoHelperAccount::find();
            if (!$nostoAccount) {
                continue;
            }

            $accountName = $nostoAccount->getName();
            $nostoProduct = $this->loadNostoProduct($product->id);
            if ($nostoProduct instanceof NostoProduct === false) {
                continue;
            }
            if (!isset($productsInStore[$accountName])) {
                $productsInStore[$accountName] = array();
            }
            if (!isset($productsInStore[$accountName][self::KEY_ACCOUNT])) {
                $productsInStore[$accountName][self::KEY_ACCOUNT] = $nostoAccount;
            }
            if (!isset($productsInStore[$accountName][self::KEY_DATA])) {
                $productsInStore[$accountName][self::KEY_DATA] = array();
            }

            if (!isset($productsInStore[$accountName][self::KEY_DATA][$batch])) {
                $productsInStore[$accountName][self::KEY_DATA][$batch] = array();
            }
            $productsInStore[$accountName][self::KEY_DATA][$batch][] = $nostoProduct;
        }

        foreach ($productsInStore as $nostoAccountName => $data) {
            $nostoAccount = $data[self::KEY_ACCOUNT];
            foreach ($data[self::KEY_DATA] as $batchIndex => $batches) {
                $op = new NostoSDKUpsertProductOperation($nostoAccount);
                foreach ($batches as $product) {
                    $op->addProduct($product);
                }
                try {
                    $op->upsert();
                } catch (Exception $e) {
                    NostoHelperLogger::error($e);
                }
            }
        }

        return true;
    }

    /**
     * Sends a product create API request to Nosto.
     *
     * @param $params
     */
    public function upsert($params)
    {
        if (isset($params['object'])) {
            $object = $params['object'];
            if ($object instanceof Product) {
                //run over all the nosto account
                NostoHelperContext::runWithEachNostoAccount(function () use ($object) {
                    $this->updateProduct($object);
                });
            }
        }
    }

    /**
     * Sends a product delete API request to Nosto.
     *
     * @param array $params the product that has been deleted.
     */
    public function delete($params)
    {
        if (isset($params['object'])) {
            $object = $params['object'];
            if ($object instanceof Product) {
                //run over all the nosto account
                NostoHelperContext::runWithEachNostoAccount(function () use ($object) {
                    $this->deleteProduct($object);
                });
            }
        }
    }

    private function deleteProduct(Product $product)
    {
        if (!Validate::isLoadedObject($product)
            || in_array($this->getProductCacheKey($product), self::$processedProducts)
        ) {
            return;
        }

        self::$processedProducts[] = $this->getProductCacheKey($product);

        $nostoAccount = NostoHelperAccount::find();
        if (!$nostoAccount) {
            return;
        }

        $nostoProduct = new NostoProduct();
        $nostoProduct->assignId($product);

        try {
            $op = new NostoSDKUpsertProductOperation($nostoAccount);
            $nostoProduct->setAvailability(NostoSDKProductInterface::DISCONTINUED);
            $op->addProduct($nostoProduct);
            $op->upsert();
        } catch (NostoSDKException $e) {
            NostoHelperLogger::error($e, sprintf("Failed to upsert product %s", $product->id));
        }
    }

    /**
     * Loads a Nosto product model for given PS product ID, language ID and shop ID.
     *
     * @param int $idProduct the PS product ID.
     * @return NostoProduct|null the product or null if could not be loaded.
     */
    protected function loadNostoProduct($idProduct)
    {
        $product = new Product(
            $idProduct,
            true,
            NostoHelperContext::getLanguageId(),
            NostoHelperContext::getShopId()
        );
        if (!Validate::isLoadedObject($product)) {
            return null;
        }

        return NostoProduct::loadData($product);
    }
}
