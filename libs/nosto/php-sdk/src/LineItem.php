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
 * Line item DTO (Data Transfer Object).
 * This is an abstract used by the cart and order items.
 */
abstract class NostoLineItem
{
    /**
     * @var string|int the item ID.
     */
    private $itemId;

    /**
     * @var int the amount of items.
     */
    private $quantity;

    /**
     * @var string the item name.
     */
    private $name;

    /**
     * @var NostoPrice the item unit price.
     */
    private $unitPrice;

    /**
     * @var NostoCurrencyCode the item price currency.
     */
    private $currency;

    /**
     * @inheritdoc
     */
    public function getItemId()
    {
        return $this->itemId;
    }

    /**
     * @inheritdoc
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getUnitPrice()
    {
        return $this->unitPrice;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Sets the unique identifier for the item.
     *
     * The identifier must be either a string or an integer.
     *
     * Usage:
     * $item->setItemId('example');
     *
     * @param string|int $itemId the identifier.
     */
    public function setItemId($itemId)
    {
        $this->itemId = $itemId;
    }

    /**
     * Sets the quantity of items.
     *
     * The quantity must be an integer value.
     *
     * Usage:
     * $item->setQuantity(2);
     *
     * @param int $quantity the quantity
     */
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;
    }

    /**
     * Sets the items name.
     *
     * The name must be a non-empty string value.
     *
     * Usage:
     * $item->setName('Example');
     *
     * @param string $name the name.
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Sets the unit price of the item.
     *
     * The price needs to be an instance of `NostoPrice`.
     *
     * Usage:
     * $item->setUnitPrice(new NostoPrice(19.99));
     *
     * @param NostoPrice $unitPrice the unit price.
     */
    public function setUnitPrice(NostoPrice $unitPrice)
    {
        $this->unitPrice = $unitPrice;
    }

    /**
     * Sets the 3-letter ISO code (ISO 4217) for the item currency.
     *
     * The currency must be an instance of `NostoCurrencyCode`.
     *
     * Usage:
     * $item->setCurrency(new NostoCurrencyCode('USD'));
     *
     * @param NostoCurrencyCode $currency the currency.
     */
    public function setCurrency(NostoCurrencyCode $currency)
    {
        $this->currency = $currency;
    }
}
