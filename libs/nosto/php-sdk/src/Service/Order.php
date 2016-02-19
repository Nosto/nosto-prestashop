<?php
/**
 * Copyright (c) 2015, Nosto Solutions Ltd
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
 * @copyright 2015 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

/**
 * Handles sending the order confirmations to Nosto via the API.
 *
 * Order confirmations can be sent two different ways:
 * - matched orders; where we know the Nosto customer ID of the user who placed the order
 * - un-matched orders: where we do not know the Nosto customer ID of the user who placed the order
 *
 * The second option is a fallback and should be avoided as much as possible.
 */
class NostoServiceOrder
{
    /**
     * @var NostoAccount the Nosto account to confirm an order for.
     */
    protected $account;

    /**
     * Constructor.
     *
     * Accepts the Nosto account for which the service is to operate on.
     *
     * @param NostoAccount $account the Nosto account object.
     */
    public function __construct(NostoAccount $account)
    {
        $this->account = $account;
    }

    /**
     * Sends an order confirmation to Nosto.
     *
     * @param NostoOrderInterface $order the order to confirm.
     * @param null $customerId the Nosto customer ID of the user who placed the order.
     * @throws NostoException on failure.
     * @return true on success.
     */
    public function confirm(NostoOrderInterface $order, $customerId = null)
    {
        $request = $this->initApiRequest($customerId);
        $response = $request->post($this->getOrderAsJson($order));
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to send order confirmation to Nosto.', $request, $response);
        }
        return true;
    }

    /**
     * Builds the API request and returns it.
     *
     * @param string|null $customerId the Nosto customer ID of the user who placed the order.
     * @return NostoApiRequest the request object.
     */
    protected function initApiRequest($customerId)
    {
        $request = new NostoApiRequest();
        $request->setContentType('application/json');
        if (!empty($customerId)) {
            $request->setPath(NostoApiRequest::PATH_ORDER_TAGGING);
            $request->setReplaceParams(array('{m}' => $this->account->getName(), '{cid}' => $customerId));
        } else {
            $request->setPath(NostoApiRequest::PATH_UNMATCHED_ORDER_TAGGING);
            $request->setReplaceParams(array('{m}' => $this->account->getName()));
        }
        return $request;
    }

    /**
     * Turns an order object into a JSON structure.
     *
     * @param NostoOrderInterface $order the order object.
     * @return string the JSON structure.
     */
    protected function getOrderAsJson(NostoOrderInterface $order)
    {
        /** @var NostoFormatterDate $dateFormatter */
        $dateFormatter = Nosto::formatter('date');
        /** @var NostoFormatterPrice $priceFormatter */
        $priceFormatter = Nosto::formatter('price');

        $dateFormat = new NostoDateFormat(NostoDateFormat::YMD);
        $priceFormat = new NostoPriceFormat(2, '.', '');

        $data = array(
            'order_number' => $order->getOrderNumber(),
            'order_status_code' => $order->getOrderStatus()->getCode(),
            'order_status_label' => $order->getOrderStatus()->getLabel(),
            'buyer' => array(
                'first_name' => $order->getBuyerInfo()->getFirstName(),
                'last_name' => $order->getBuyerInfo()->getLastName(),
                'email' => $order->getBuyerInfo()->getEmail(),
            ),
            'created_at' => $dateFormatter->format($order->getCreatedDate(), $dateFormat),
            'payment_provider' => $order->getPaymentProvider(),
            'purchased_items' => array(),
        );
        foreach ($order->getPurchasedItems() as $item) {
            $data['purchased_items'][] = array(
                'product_id' => $item->getProductId(),
                'quantity' => (int)$item->getQuantity(),
                'name' => $item->getName(),
                'unit_price' => $priceFormatter->format($item->getUnitPrice(), $priceFormat),
                'price_currency_code' => $item->getCurrency()->getCode(),
            );
        }
        return json_encode($data);
    }
}
