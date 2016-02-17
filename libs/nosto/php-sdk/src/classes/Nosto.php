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
 * Main SDK class.
 * Provides common functionality for the SDK.
 */
class Nosto
{
    /**
     * @var array registry collection
     */
    private static $registry = array();

    /**
     * Return environment variable.
     *
     * @param string $name the name of the variable.
     * @param null $default the default value to return if the env variable cannot be found.
     * @return mixed the env variable or null.
     */
    public static function getEnvVariable($name, $default = null)
    {
        return isset($_ENV[$name]) ? $_ENV[$name] : $default;
    }

    /**
     * Gets a helper class instance by name.
     *
     * @param string $helper the name of the helper class to get.
     * @return NostoHelper the helper instance.
     * @throws NostoException if helper cannot be found.
     */
    public static function helper($helper)
    {
        $registryKey = '__helper__/'.$helper;
        if (!self::registry($registryKey)) {
            $helperClass = self::getHelperClassName($helper);
            if (!class_exists($helperClass)) {
                throw new NostoException(sprintf('Unknown helper class %s', $helperClass));
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
     * @throws NostoException if the key is already registered.
     */
    public static function register($key, $value)
    {
        if (isset(self::$registry[$key])) {
            throw new NostoException(sprintf('Nosto registry key %s already exists', $key));
        }
        self::$registry[$key] = $value;
    }

    /**
     * Throws a new NostoHttpException exception with info about both the
     * request and response.
     *
     * @param string $message the error message.
     * @param NostoHttpRequest $request the request object to take additional info from.
     * @param NostoHttpResponse $response the response object to take additional info from.
     * @throws NostoHttpException the exception.
     */
    public static function throwHttpException($message, NostoHttpRequest $request, NostoHttpResponse $response)
    {
        $message .= sprintf(' Error: %s.', $response->getCode());
        $message .= sprintf(' Request: %s.', $request);
        $message .= sprintf(' Response: %s.', $response);
        throw new NostoHttpException($message, $response->getCode());
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
