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



class NostoRestoreCart
{
    /**
     * Loads the cart data from supplied cart object.
     *
     * @param Cart $cart the cart model to process
     * @return string|null the restore cart url
     */
    public static function loadData(Cart $cart)
    {
        $cartId = $cart->id;

        $nostoCustomer = $this->updateNostoId($quote);
        if ($nostoCustomer && $nostoCustomer->getRestoreCartHash()) {
            return $this->generateRestoreCartUrl($nostoCustomer->getRestoreCartHash(), $store);
        }

        return null;
    }

    /**
     * @param Quote $quote
     *
     * @return NostoCustomer|null
     */
    private function updateNostoId(Quote $quote)
    {
        // Handle the Nosto customer & quote mapping
        $nostoCustomerId = $this->cookieManager->getCookie(NostoCustomer::COOKIE_NAME);

        if ($quote === null || $quote->getId() === null || empty($nostoCustomerId)) {
            return null;
        }

        $quoteId = $quote->getId();
        /** @noinspection PhpUndefinedMethodInspection */
        $customerQuery = $this->nostoCustomerFactory
            ->create()
            ->getCollection()
            ->addFieldToFilter(NostoCustomer::QUOTE_ID, $quoteId)
            ->addFieldToFilter(NostoCustomer::NOSTO_ID, $nostoCustomerId)
            ->setPageSize(1)
            ->setCurPage(1);

        /** @var NostoCustomer $nostoCustomer */
        $nostoCustomer = $customerQuery->getFirstItem(); // @codingStandardsIgnoreLine
        if ($nostoCustomer->hasData(NostoCustomer::CUSTOMER_ID)) {
            if ($nostoCustomer->getRestoreCartHash() === null) {
                $nostoCustomer->setRestoreCartHash($this->generateRestoreCartHash());
            }
            $nostoCustomer->setUpdatedAt(self::getNow());
        } else {
            /** @noinspection PhpUndefinedMethodInspection */
            $nostoCustomer = $this->nostoCustomerFactory->create();
            /** @noinspection PhpUndefinedMethodInspection */
            $nostoCustomer->setQuoteId($quoteId);
            /** @noinspection PhpUndefinedMethodInspection */
            $nostoCustomer->setNostoId($nostoCustomerId);
            $nostoCustomer->setCreatedAt(self::getNow());
            $nostoCustomer->setRestoreCartHash($this->generateRestoreCartHash());
        }
        try {
            /** @noinspection PhpDeprecationInspection */
            $nostoCustomer->save();

            return $nostoCustomer;
        } catch (\Exception $e) {
            $this->logger->exception($e);
        }

        return null;
    }

    /**
     * Generate unique hash for restore cart
     * Size of it equals to or less than restore_cart_hash column length
     *
     * @return string
     */
    private function generateRestoreCartHash()
    {
        $hash = $this->encryptor->getHash(uniqid('nostocartrestore'));
        if (strlen($hash) > NostoCustomer::NOSTO_TAGGING_RESTORE_CART_ATTRIBUTE_LENGTH) {
            $hash = substr($hash, 0, NostoCustomer::NOSTO_TAGGING_RESTORE_CART_ATTRIBUTE_LENGTH);
        }

        return $hash;
    }

    /**
     * Returns the current datetime object
     *
     * @return \DateTime the current datetime
     */
    private function getNow()
    {
        return \DateTime::createFromFormat('Y-m-d H:i:s', $this->date->date());
    }

    /**
     * Returns restore cart url
     *
     * @param string $hash
     * @param Store $store
     * @return string
     */
    private function generateRestoreCartUrl($hash, Store $store)
    {
        $params = NostoHelperUrl::getUrlOptionsWithNoSid($store);
        $params['h'] = $hash;
        $url = $store->getUrl(NostoHelperUrl::NOSTO_PATH_RESTORE_CART, $params);

        return $url;
    }
}
