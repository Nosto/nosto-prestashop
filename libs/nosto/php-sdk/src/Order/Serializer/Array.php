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
 * Order array serializer.
 */
class NostoOrderSerializerArray
{
    /**
     * Serializes the order into an array structure.
     *
     * @param NostoOrderInterface $order the order to serialize.
     * @return array the serialized data.
     */
    public function serialize(NostoOrderInterface $order)
    {
        /** @var NostoFormatterDate $dateFormatter */
        $dateFormatter = Nosto::formatter('date');
        /** @var NostoFormatterPrice $priceFormatter */
        $priceFormatter = Nosto::formatter('price');
        $data = array(
            'order_number' => $order->getOrderNumber(),
            'buyer' => array(),
            'purchased_items' => array(),
        );

        if ($order->getCreatedDate() instanceof NostoDate) {
            $data['created_at'] = $dateFormatter->format($order->getCreatedDate());
        } else {
            $data['created_at'] = '';
        }

        if ($order->getStatus() instanceof NostoOrderStatusInterface) {
            $data['order_status_code'] = $order->getStatus()->getCode();
            $data['order_status_label'] = $order->getStatus()->getLabel();
        } elseif (is_string($order->getStatus()) || is_numeric($order->getStatus())) {
            $data['order_status_code'] = $order->getStatus();
            $data['order_status_label'] = $order->getStatus();
        }
        if ($order->getPaymentProvider() instanceof NostoOrderPaymentProviderInterface) {
            $data['payment_provider'] = $order->getPaymentProvider()->getProvider();
        } elseif (is_string($order->getPaymentProvider()) || is_numeric($order->getPaymentProvider())) {
            $data['payment_provider'] = $order->getPaymentProvider();
        }

        foreach ($order->getItems() as $item) {
            $itemData = array(
                'product_id' => $item->getItemId(),
                'quantity' => (int)$item->getQuantity(),
                'name' => $item->getName(),
            );
            if ($item->getUnitPrice() instanceof NostoPrice) {
                $itemData['unit_price'] = $priceFormatter->format($item->getUnitPrice());
            } elseif (is_numeric($item->getUnitPrice())) {
                $itemData['unit_price'] = $item->getUnitPrice();
            } else {
                $itemData['unit_price'] = '';
            }
            if ($item->getCurrency() instanceof NostoCurrencyCode) {
                $itemData['price_currency_code'] = $item->getCurrency()->getCode();
            } elseif (is_string($item->getCurrency())) {
                $itemData['price_currency_code'] = $item->getCurrency();
            } else {
                $itemData['price_currency_code'] = '';
            }
            $data['purchased_items'][] = $itemData;
        }

        // Add optional order reference if set.
        if ($order->getExternalRef()) {
            $data['external_order_ref'] = $order->getExternalRef();
        }
        // Add optional buyer info.
        if ($order->getBuyer() instanceof NostoOrderBuyerInterface) {
            $data['buyer']['first_name'] = $order->getBuyer()->getFirstName();
            $data['buyer']['last_name'] = $order->getBuyer()->getLastName();
            $data['buyer']['email'] = $order->getBuyer()->getEmail();
        }
        // Add optional order status history if set.
        if ($order->getHistoryStatuses() !== array()) {
            $dateFormat = new NostoDateFormat(NostoDateFormat::ISO_8601);
            $statuses = array();
            foreach ($order->getHistoryStatuses() as $status) {
                if ($status instanceof NostoOrderStatusInterface
                    && $status->getCreatedAt()
                ) {
                    if (!isset($statuses[$status->getCode()])) {
                        $statuses[$status->getCode()] = array();
                    }
                    $statuses[$status->getCode()][] = $dateFormatter->format(
                        $status->getCreatedAt(),
                        $dateFormat
                    );
                }
            }
            if (count($statuses) > 0) {
                $data['order_statuses'] = $statuses;
            }
        }

        return $data;
    }
}
