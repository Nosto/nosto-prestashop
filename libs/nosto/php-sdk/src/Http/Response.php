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
 * Represents a http request response returned by NostoHttpRequest.
 */
class NostoHttpResponse
{
    /**
     * @var array the response headers if there are any.
     */
    protected $headers;

    /**
     * @var mixed the request result raw body.
     */
    protected $result;

    /**
     * @var string possible request error message.
     */
    protected $message;

    /**
     * @var int runtime cache for the http response code.
     */
    private $code;

    /**
     * Constructor.
     * Creates and populates the response object.
     *
     * @param array $headers the response headers.
     * @param string $body the response body.
     * @param string $error optional error message.
     */
    public function __construct($headers, $body, $error = null)
    {
        if (!empty($headers) && is_array($headers)) {
            $this->headers = $headers;
        }
        if (!empty($body) && is_string($body)) {
            $this->result = $body;
        }
        if (!empty($error) && is_string($error)) {
            $this->message = $error;
        }
    }

    /**
     * Getter for the request response data.
     *
     * @return mixed the request response data.
     */
    public function getResult()
    {
        return $this->result;
    }

    /**
     * Getter for the request response as JSON.
     *
     * @param bool $assoc if the returned JSON should be formatted as an associative array or an stdClass instance.
     * @return array|stdClass
     */
    public function getJsonResult($assoc = false)
    {
        return json_decode($this->result, $assoc);
    }

    /**
     * Getter for the error message of the request.
     *
     * @return string the message.
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Returns the `last` http response code.
     *
     * @return int the http code or 0 if not set.
     */
    public function getCode()
    {
        if (is_null($this->code)) {
            $code = 0;
            if (!empty($this->headers)) {
                foreach ($this->headers as $header) {
                    $matches = array();
                    preg_match('|HTTP/\d\.\d\s+(\d+)\s+.*|', $header, $matches);
                    if (isset($matches[1])) {
                        $code = (int)$matches[1];
                    }
                }
            }
            $this->code = $code;
        }
        return $this->code;
    }

    /**
     * Converts the response to a string and returns it.
     * Used when logging http request errors.
     */
    public function __toString()
    {
        return serialize(
            array(
                'headers' => $this->headers,
                'body' => $this->result,
                'error' => $this->message,
            )
        );
    }
}
