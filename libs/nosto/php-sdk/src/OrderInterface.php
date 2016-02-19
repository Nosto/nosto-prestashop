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
 * Interface for the meta data of an placed order.
 * This is used when making order confirmation API requests and order history exports to Nosto.
 */
interface NostoOrderInterface
{
    /**
     * The unique order number identifying the order.
     *
     * @return string|int the order number.
     */
    public function getOrderNumber();

    /**
     * Returns an external order reference number.
     * This can help identify the order in Nosto's backend, while the above
     * order number is more of a "machine name" for the order.
     *
     * @return string|null the order reference or null if not used.
     */
    public function getExternalRef();

    /**
     * The date when the order was placed.
     *
     * @return NostoDate the creation date.
     */
    public function getCreatedDate();

    /**
     * The payment provider used for placing the order.
     *
     * @return NostoOrderPaymentProviderInterface the payment provider.
     */
    public function getPaymentProvider();

    /**
     * The buyer info of the user who placed the order.
     *
     * @return NostoOrderBuyerInterface the meta data model.
     */
    public function getBuyer();

    /**
     * The purchased items which were included in the order.
     *
     * @return NostoOrderItemInterface[] the meta data models.
     */
    public function getItems();

    /**
     * Returns the order status model.
     *
     * @return NostoOrderStatusInterface the model.
     */
    public function getStatus();

    /**
     * Returns a list of history order status models.
     * These are used in the order export to track the order funnel.
     *
     * @return NostoOrderStatusInterface[] the status models.
     */
    public function getHistoryStatuses();
}
