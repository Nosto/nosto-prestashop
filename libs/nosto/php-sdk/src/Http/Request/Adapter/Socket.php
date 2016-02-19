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
 * Adapter class for making http requests using php sockets.
 * This adapter uses file_get_contents() and stream_context_create() for creating http requests.
 *
 * Note that if php is compiled with "--with-curlwrappers" then headers are not sent properly in older php versions.
 * @link https://bugs.php.net/bug.php?id=55438
 */
class NostoHttpRequestAdapterSocket extends NostoHttpRequestAdapter
{
    /**
     * @inheritdoc
     */
    public function get($url, array $options = array())
    {
        $this->init($options);
        return $this->send(
            $url,
            array(
                'http' => array(
                    'method' => 'GET',
                    'header' => implode("\r\n", $this->headers),
                    // Fetch the content even on failure status codes.
                    'ignore_errors' => true,
                ),
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function post($url, array $options = array())
    {
        $this->init($options);
        return $this->send(
            $url,
            array(
                'http' => array(
                    'method' => 'POST',
                    'header' => implode("\r\n", $this->headers),
                    'content' => $this->content,
                    // Fetch the content even on failure status codes.
                    'ignore_errors' => true,
                ),
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function put($url, array $options = array())
    {
        $this->init($options);
        return $this->send(
            $url,
            array(
                'http' => array(
                    'method' => 'PUT',
                    'header' => implode("\r\n", $this->headers),
                    'content' => $this->content,
                    // Fetch the content even on failure status codes.
                    'ignore_errors' => true,
                ),
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function delete($url, array $options = array())
    {
        $this->init($options);
        return $this->send(
            $url,
            array(
                'http' => array(
                    'method' => 'DELETE',
                    'header' => implode("\r\n", $this->headers),
                    // Fetch the content even on failure status codes.
                    'ignore_errors' => true,
                ),
            )
        );
    }

    /**
     * Sends the request and creates a NostoHttpResponse instance containing the response headers and body.
     *
     * @param string $url the url for the request.
     * @param array $streamOptions options for stream_context_create().
     * @return NostoHttpResponse
     */
    protected function send($url, array $streamOptions)
    {
        $context = stream_context_create($streamOptions);
        // We use file_get_contents() directly here as we need the http response headers which are automatically
        // populated into $headers, which is only available in the local scope where file_get_contents()
        // is executed (http://php.net/manual/en/reserved.variables.httpresponseheader.php).
        $http_response_header = array();
        $result = @file_get_contents($url, false, $context);
        return new NostoHttpResponse($http_response_header, $result);
    }
}
