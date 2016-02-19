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
        return self::getInstanceFromRegistry($helper, 'Helper');
    }

    /**
     * Gets a formatter class instance by name.
     *
     * @param string $formatter the name of the formatter class.
     * @return NostoFormatter the formatter instance.
     * @throws NostoException if the formatter cannot be found.
     */
    public static function formatter($formatter)
    {
        return self::getInstanceFromRegistry($formatter, 'Formatter');
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
     * Creates a new NostoHttpException exception with info about both the
     * request and response.
     *
     * @param string $message the error message.
     * @param NostoHttpRequest $request the request object to take additional info from.
     * @param NostoHttpResponse $response the response object to take additional info from.
     * @return NostoHttpException the exception.
     */
    public static function createHttpException($message, NostoHttpRequest $request, NostoHttpResponse $response)
    {
        $message .= sprintf(' Error: %s.', $response->getCode());
        $message .= sprintf(' Request: %s.', $request);
        $message .= sprintf(' Response: %s.', $response);
        return new NostoHttpException($message, $response->getCode());
    }

    /**
     * Register a new variable.
     * Overwrites entries with identical key.
     *
     * @param string $key the key to register the variable for.
     * @param mixed $value the variable to register.
     */
    private static function register($key, $value)
    {
        self::$registry[$key] = $value;
    }

    /**
     * Gets an instance from registry by name.
     *
     * @param string $name the name of the class.
     * @param string $type the type of the class, e.g. "Helper", "Formatter"
     * @return NostoFormatter the instance.
     * @throws NostoException if instance cannot be found.
     */
    private static function getInstanceFromRegistry($name, $type)
    {
        $registryKey = '__'.strtolower($type).'__/'.$name;
        if (!self::registry($registryKey)) {
            $className = self::getClassName($name, $type);
            if (!class_exists($className)) {
                throw new NostoException(sprintf('Unknown %s class %s', $type, $className));
            }
            self::register($registryKey, new $className);
        }
        return self::registry($registryKey);
    }

    /**
     * Converts a helper/formatter class name reference name to a real class name.
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
     * @param string $name the helper reference name.
     * @param string $type the helper type, e.g. "Helper", "Formatter".
     * @return string|bool the helper class name or false if it cannot be built.
     */
    private static function getClassName($name, $type)
    {
        if (strpos($name, '/') === false) {
            $name = 'nosto/'.$name;
        }
        return str_replace(' ', '', ucwords(str_replace('_', ' ', str_replace('/', ' '.$type.' ', $name))));
    }
}
