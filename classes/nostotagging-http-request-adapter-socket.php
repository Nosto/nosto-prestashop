<?php
/**
 * 2013-2014 Nosto Solutions Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@nosto.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2014 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Adapter class for making http requests using php sockets.
 * This adapter uses file_get_contents() and stream_context_create() for creating http requests.
 *
 * Note that if php is compiled with "--with-curlwrappers" then headers are not sent properly in older php versions.
 * @link https://bugs.php.net/bug.php?id=55438
 */
class NostoTaggingHttpRequestAdapterSocket extends NostoTaggingHttpRequestAdapter
{
	/**
	 * @inheritdoc
	 */
	public function get($url, array $options = array())
	{
		$this->init($options);
		return $this->send($url, array(
			'http' => array(
				'method' => 'GET',
				'header' => implode("\r\n", $this->headers),
				// Fetch the content even on failure status codes.
				'ignore_errors' => true,
			),
		));
	}

	/**
	 * @inheritdoc
	 */
	public function post($url, array $options = array())
	{
		$this->init($options);
		return $this->send($url, array(
			'http' => array(
				'method' => 'POST',
				'header' => implode("\r\n", $this->headers),
				'content' => $this->content,
				// Fetch the content even on failure status codes.
				'ignore_errors' => true,
			),
		));
	}

	/**
	 * Sends the request and creates a NostoTaggingHttpResponse instance containing the response headers and body.
	 *
	 * @param string $url the url for the request.
	 * @param array $stream_options options for stream_context_create().
	 * @return NostoTaggingHttpResponse
	 */
	protected function send($url, array $stream_options)
	{
		$context = stream_context_create($stream_options);
		// We use file_get_contents() directly here as we need the http response headers which are automatically
		// populated into $http_response_header, which is only available in the local scope where file_get_contents()
		// is executed (http://php.net/manual/en/reserved.variables.httpresponseheader.php).
		$http_response_header = array();
		$result = file_get_contents($url, false, $context);
		$response = new NostoTaggingHttpResponse();
		if (!empty($http_response_header))
			$response->setHttpResponseHeader($http_response_header);
		$response->setResult($result);
		return $response;
	}
}
