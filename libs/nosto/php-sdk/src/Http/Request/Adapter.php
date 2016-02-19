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
 * Base class for all http request adapters.
 */
abstract class NostoHttpRequestAdapter
{
    /**
     * @var array the request headers.
     */
    protected $headers = array();

    /**
     * @var mixed the request content.
     */
    protected $content = null;

    /**
     * Initializes the request options.
     *
     * @param array $options the options.
     */
    protected function init(array $options = array())
    {
        foreach ($options as $key => $value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }
    }

    /**
     * Does a GET request and returns the http response object.
     *
     * @param string $url the URL to request.
     * @param array $options the request options.
     * @return NostoHttpResponse the response object.
     */
    abstract public function get($url, array $options = array());

    /**
     * Does a POST request and returns the http response object.
     *
     * @param string $url the URL to request.
     * @param array $options the request options.
     * @return NostoHttpResponse the response object.
     */
    abstract public function post($url, array $options = array());

    /**
     * Does a PUT request and returns the http response object.
     *
     * @param string $url the URL to request.
     * @param array $options the request options.
     * @return NostoHttpResponse the response object.
     */
    abstract public function put($url, array $options = array());

    /**
     * Does a DELETE request and returns the http response object.
     *
     * @param string $url the URL to request.
     * @param array $options the request options.
     * @return NostoHttpResponse the response object.
     */
    abstract public function delete($url, array $options = array());
}
