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
 * Order status DTO (Data Transfer Object).
 */
class NostoOrderStatus implements NostoOrderStatusInterface
{
    /**
     * @var string the order status code, e.g. "completed".
     */
    private $code;

    /**
     * @var string the order status label, e.g. "Completed".
     */
    private $label;

    /**
     * @var NostoDate the date when the order status was assigned to the order.
     */
    private $createdAt;

    /**
     * @inheritdoc
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @inheritdoc
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the order status code.
     *
     * The code must be a non-empty string.
     *
     * Usage:
     * $status->setCode('completed');
     *
     * @param string $code the code.
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * Sets the order status label.
     *
     * The label must be a non-empty string.
     *
     * Usage:
     * $status->setLabel('Completed');
     *
     * @param string $label the label.
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * Sets the date when the order status was assigned to the order.
     *
     * The date must be an instance of `NostoDate`.
     *
     * Usage:
     * $status->setCreatedAt(new NostoDate(time()));
     *
     * @param NostoDate $date the date.
     */
    public function setCreatedAt(NostoDate $date)
    {
        $this->createdAt = $date;
    }
}
