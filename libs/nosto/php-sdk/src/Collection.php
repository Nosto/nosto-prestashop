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
 * Base class for all Nosto object collection classes.
 * The base class provides the functionality to validate the items added to the collection.
 * The collection behaves like an array. making it easy to add items to it and iterate over it.
 */
abstract class NostoCollection extends ArrayObject
{
    /**
     * @var string the type of items this collection can contain.
     */
    protected $validItemType = '';

    /**
     * @inheritdoc
     */
    public function offsetSet($index, $newval)
    {
        $this->validate($newval);
        parent::offsetSet($index, $newval);
    }

    /**
     * @inheritdoc
     */
    public function append($value)
    {
        $this->validate($value);
        parent::append($value);
    }

    /**
     * Validates that the given value is of correct type.
     *
     * @see NostoCollection::$validItemType
     * @param mixed $value the value.
     * @throws NostoException if the value is of invalid type.
     */
    protected function validate($value)
    {
        if (!is_a($value, $this->validItemType)) {
            $valueType = gettype($value);
            if ($valueType === 'object') {
                $valueType = get_class($value);
            }
            throw new NostoException(sprintf(
                'Collection supports items of type "%s" (type "%s" given)',
                $this->validItemType,
                $valueType
            ));
        }
    }
}
