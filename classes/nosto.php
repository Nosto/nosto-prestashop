<?php
/**
 * Copyright (c) 2017, Nosto Solutions Ltd
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
 * @copyright 2017 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 *
 */

use \Nosto\NostoException as NostoSDKException;

class Nosto
{
    /**
     * @var array registry collection
     */
    private static $registry = array();

    /**
     * Gets a helper class instance by name.
     *
     * @param string $helper the name of the helper class to get.
     * @return mixed if helper cannot be found.
     * @throws NostoSDKException
     */
    public static function helper($helper)
    {
        $registryKey = '__helper__/'.$helper;
        if (!self::registry($registryKey)) {
            $helperClass = self::getHelperClassName($helper);
            if (!class_exists($helperClass)) {
                throw new NostoSDKException(sprintf('Unknown helper class %s', $helperClass));
            }
            self::register($registryKey, new $helperClass);
        }
        return self::registry($registryKey);
    }

    /**
     * Retrieve a value from registry by a key.
     *
     * @param string $key the register key for the variable.
     * @return mixed the registered variable or null if not found.
     */
    public static function registry($key)
    {
        if (isset(self::$registry[$key])) {
            return self::$registry[$key];
        }
        return null;
    }

    /**
     * Register a new variable.
     *
     * @param string $key the key to register the variable for.
     * @param mixed $value the variable to register.
     * @throws NostoSDKException
     */
    public static function register($key, $value)
    {
        if (isset(self::$registry[$key])) {
            throw new NostoSDKException(sprintf('Nosto registry key %s already exists', $key));
        }
        self::$registry[$key] = $value;
    }


    /**
     * Converts a helper class name reference name to a real class name.
     *
     * Examples:
     *
     * date => NostoHelperDate
     * price_rule => NostoHelperPriceRule
     * nosto/date => NostoHelperDate
     * nosto/price_rule => NostoHelperPriceRule
     * nosto_tagging/date => NostoTaggingHelperDate
     * nosto_tagging/price_rule => NostoTaggingHelperPriceRule
     *
     * @param string $ref the helper reference name.
     * @return string|bool the helper class name or false if it cannot be built.
     */
    protected static function getHelperClassName($ref)
    {
        if (strpos($ref, '/') === false) {
            $ref = 'nosto/'.$ref;
        }
        return str_replace(' ', '', ucwords(str_replace('_', ' ', str_replace('/', ' Helper ', $ref))));
    }
}
