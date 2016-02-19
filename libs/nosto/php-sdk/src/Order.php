<?php
/**
 * Copyright (c) 2016, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2016 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

/**
 * Order DTO (Data Transfer Object).
 */
class NostoOrder implements NostoOrderInterface
{
    /**
     * @var string|int the unique order number identifying the order.
     */
    protected $orderNumber;

    /**
     * @var string the external order reference number, i.e. "real order id".
     */
    protected $externalRef;

    /**
     * @var NostoDate the date when the order was placed.
     */
    protected $createdDate;

    /**
     * @var NostoOrderPaymentProviderInterface the payment provider used for order.
     */
    protected $paymentProvider;

    /**
     * @var NostoOrderBuyerInterface the user info of the buyer.
     */
    protected $buyer;

    /**
     * @var NostoOrderItemInterface[] the items in the order.
     */
    protected $items = array();

    /**
     * @var NostoOrderStatusInterface the order status.
     */
    protected $status;

    /**
     * @var NostoOrderStatusInterface[] list of order status history.
     */
    protected $historyStatuses = array();

    /**
     * @inheritdoc
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * @inheritdoc
     */
    public function getExternalRef()
    {
        return $this->externalRef;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedDate()
    {
        return $this->createdDate;
    }

    /**
     * @inheritdoc
     */
    public function getPaymentProvider()
    {
        return $this->paymentProvider;
    }

    /**
     * @inheritdoc
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    /**
     * @inheritdoc
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @inheritdoc
     */
    public function getHistoryStatuses()
    {
        return $this->historyStatuses;
    }

    /**
     * Sets the unique order number identifying the order.
     *
     * The order number must be either a string or and integer.
     *
     * Usage:
     * $order->setOrderNumber('100');
     *
     * @param string|int $orderNumber the order number.
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;
    }

    /**
     * Sets the external order reference number.
     *
     * This can help identify the order in Nosto's backend, while the above order number is more of a "machine name"
     * for the order.
     *
     * The external reference must be either a string or an integer.
     *
     * Usage:
     * $order->setExternalRef('#000100');
     *
     * @param string|int $ref the order reference.
     */
    public function setExternalRef($ref)
    {
        $this->externalRef = $ref;
    }

    /**
     * Sets the date when the order was placed.
     *
     * The date must be an instance of `NostoDate`.
     *
     * Usage:
     * $order->setCreatedDate(new NostoDate(time()));
     *
     * @param NostoDate $date the creation date.
     */
    public function setCreatedDate(NostoDate $date)
    {
        $this->createdDate = $date;
    }

    /**
     * Sets the payment provider used for placing the order.
     *
     * The provider must implement the `NostoOrderPaymentProviderInterface` interface.
     *
     * Usage:
     * $order->setPaymentProvider(NostoOrderPaymentProviderInterface $paymentProvider);
     *
     * @param NostoOrderPaymentProviderInterface $paymentProvider the payment provider.
     */
    public function setPaymentProvider(NostoOrderPaymentProviderInterface $paymentProvider)
    {
        $this->paymentProvider = $paymentProvider;
    }

    /**
     * Sets the buyer info of the user who placed the order.
     *
     * The buyer must implement the `NostoOrderBuyerInterface` interface.
     *
     * Usage:
     * $order->setBuyerInfo(NostoOrderBuyerInterface $buyer);
     *
     * @param NostoOrderBuyerInterface $buyer the buyer info.
     */
    public function setBuyer(NostoOrderBuyerInterface $buyer)
    {
        $this->buyer = $buyer;
    }

    /**
     * Sets the purchased items which were included in the order.
     *
     * The items must implement the `NostoOrderItemInterface` interface.
     *
     * Usage:
     * $order->setItems(array(NostoOrderItemInterface $item, [...]));
     *
     * @param NostoOrderItemInterface[] $items the purchased items.
     */
    public function setItems(array $items)
    {
        $this->items = array();
        foreach ($items as $item) {
            $this->addItem($item);
        }
    }

    /**
     * Adds a purchased item to the order.
     *
     * The item must implement the `NostoOrderItemInterface` interface.
     *
     * Usage:
     * $order->addItem(NostoOrderItemInterface $item);
     *
     * @param NostoOrderItemInterface $item the item.
     */
    public function addItem(NostoOrderItemInterface $item)
    {
        $this->items[] = $item;
    }

    /**
     * Sets the order status.
     *
     * The status must implement the `NostoOrderStatusInterface` interface.
     *
     * Usage:
     * $order->setStatus(NostoOrderStatusInterface $status);
     *
     * @param NostoOrderStatusInterface $status the status.
     */
    public function setStatus(NostoOrderStatusInterface $status)
    {
        $this->status = $status;
    }

    /**
     * Sets the history order statuses for the order.
     *
     * These are used in the order export to track the order funnel.
     *
     * The statuses must implement the `NostoOrderStatusInterface` interface.
     *
     * Usage:
     * $order->setHistoryStatuses(array(NostoOrderStatusInterface $status, [...]));
     *
     * @param NostoOrderStatusInterface[] $statuses the statuses.
     */
    public function setHistoryStatuses(array $statuses)
    {
        $this->historyStatuses = array();
        foreach ($statuses as $status) {
            $this->addHistoryStatus($status);
        }
    }

    /**
     * Adds a history status to the order.
     *
     * These are used in the order export to track the order funnel.
     *
     * The status must implement the `NostoOrderStatusInterface` interface.
     *
     * Usage:
     * $order->addHistoryStatus(NostoOrderStatusInterface $status);
     *
     * @param NostoOrderStatusInterface $status the status.
     */
    public function addHistoryStatus(NostoOrderStatusInterface $status)
    {
        $this->historyStatuses[] = $status;
    }
}
