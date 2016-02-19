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
 * Validator util that is used to validate `validatable` objects.
 */
class NostoValidator
{
    /**
     * @var NostoValidatableInterface the object to validate.
     */
    private $_object;

    /**
     * @var array map of validation errors per attribute.
     */
    private $_errors = array();

    /**
     * Constructor.
     * Creates a new validator for the object to validate.
     *
     * @param NostoValidatableInterface $object the object to validate.
     */
    public function __construct(NostoValidatableInterface $object)
    {
        $this->_object = $object;
    }

    /**
     * Validates the `validatable` object based on it's validation rules.
     *
     * @return bool true if the object is valid, false otherwise.
     * @throws NostoException if the rule validator is not found.
     */
    public function validate()
    {
        foreach ($this->_object->getValidationRules() as $rule) {
            if (isset($rule[0], $rule[1])) {
                $properties = $rule[0];
                $validator = 'validate'.$rule[1];
                if (!method_exists($this, $validator)) {
                    throw new NostoException(sprintf('Nosto validator "%s" does not exist.', $validator));
                }
                $params = array_merge(array($properties), array_slice($rule, 2));
                $isValid = call_user_func_array(array($this, $validator), $params);
                if (!$isValid) {
                    return false;
                }
            }
        }
        return true;
    }

    /**
     * Returns if the object contains validation errors.
     *
     * @return bool true if contains errors, false otherwise.
     */
    public function hasErrors()
    {
        return (bool)count($this->_errors);
    }

    /**
     * Returns the validations errors per attribute.
     *
     * @return array the list of error messages.
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Adds a new validation error message for the attribute.
     *
     * @param string $attribute the attribute name.
     * @param string $message the error message.
     */
    protected function addError($attribute, $message)
    {
        if (!isset($this->_errors[$attribute])) {
            $this->_errors[$attribute] = array();
        }
        $this->_errors[$attribute][] = $message;
    }

    /**
     * Validates that all the given properties are NOT empty in this instance.
     *
     * @param array $properties the list of property names to validate.
     * @return bool true if all are valid, false otherwise.
     */
    protected function validateRequired(array $properties)
    {
        foreach ($properties as $property) {
            $value = $this->_object->{$property};
            if (empty($value)) {
                $this->addError($property, sprintf('Property "%s" must not be empty.', $property));
                return false;
            }
        }
        return true;
    }

    /**
     * Validates that all given properties are IN the list of supplied values.
     *
     * @param array $properties the list of properties to validate.
     * @param array $values the list of valid values the properties must
     * @return bool true if all are valid, false otherwise.
     */
    protected function validateIn(array $properties, array $values)
    {
        $supported = implode('", "', $values);
        foreach ($properties as $property) {
            $value = $this->_object->{$property};
            if (!in_array($value, $values)) {
                $this->addError(
                    $property,
                    sprintf('Property "%s" must be one of the following: "%s".', $property, $supported)
                );
                return false;
            }
        }
        return true;
    }
}
