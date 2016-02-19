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
 * Product variation DTO (Data Transfer Object).
 */
class NostoProductPriceVariation implements NostoProductPriceVariationInterface
{
    /**
     * @var string|int the variation ID.
     */
    private $variationId;

    /**
     * @var NostoCurrencyCode the currency code (ISO 4217) for the variation.
     */
    private $currency;

    /**
     * @var NostoPrice the price of the variation including possible discounts and taxes.
     */
    private $price;

    /**
     * @var NostoPrice the list price of the variation without discounts but incl taxes.
     */
    private $listPrice;

    /**
     * @var NostoProductAvailability the availability of the variation, i.e. if it is in stock or not.
     */
    private $availability;

    /**
     * @inheritdoc
     */
    public function getVariationId()
    {
        return $this->variationId;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @inheritdoc
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @inheritdoc
     */
    public function getListPrice()
    {
        return $this->listPrice;
    }

    /**
     * @inheritdoc
     */
    public function getAvailability()
    {
        return $this->availability;
    }

    /**
     * Sets the variation ID.
     *
     * The ID must be a non-empty value.
     *
     * $variation->setVariationId('USD');
     *
     * @param int|string $variationId the variation ID.
     */
    public function setVariationId($variationId)
    {
        $this->variationId = $variationId;
    }

    /**
     * Sets the currency code (ISO 4217) for the variation.
     *
     * The currency code must be an instance of `NostoCurrencyCode`.
     *
     * Usage:
     * $variation->setCurrency(new NostoCurrencyCode('USD'));
     *
     * @param NostoCurrencyCode $currency the currency code.
     */
    public function setCurrency(NostoCurrencyCode $currency)
    {
        $this->currency = $currency;
    }

    /**
     * Sets the price of the variation including possible discounts and taxes.
     *
     * The price must be an instance of `NostoPrice`.
     *
     * Usage:
     * $variation->setPrice(new NostoPrice(19.99));
     *
     * @param NostoPrice $price the price.
     */
    public function setPrice(NostoPrice $price)
    {
        $this->price = $price;
    }

    /**
     * Sets the list price of the variation without discounts but incl taxes.
     *
     * The list price must be an instance of `NostoPrice`.
     *
     * Usage:
     * $variation->setListPrice(new NostoPrice(29.99));
     *
     * @param NostoPrice $listPrice the list price.
     */
    public function setListPrice(NostoPrice $listPrice)
    {
        $this->listPrice = $listPrice;
    }

    /**
     * Sets the availability of the variation, i.e. if it is in stock or not.
     *
     * The availability must be an instance of `NostoProductAvailability`.
     *
     * Usage:
     * $variation->setAvailability(new NostoProductAvailability(NostoProductAvailability::IN_STOCK));
     *
     * @param NostoProductAvailability $availability the availability.
     */
    public function setAvailability(NostoProductAvailability $availability)
    {
        $this->availability = $availability;
    }

    public function getId()
    {
        return $this->getVariationId();
    }
}
