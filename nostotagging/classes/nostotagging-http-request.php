<?php

/**
 * Helper class for doing http requests and returning unified response including header info.
 */
class NostoTaggingHttpRequest
{
	/**
	 * Builds an uri by replacing the param placeholders in $uri with the ones given in $$replace_params.
	 *
	 * @param string $uri
	 * @param array $replace_params
	 * @return string
	 */
	public static function build_uri($uri, array $replace_params)
	{
		return strtr($uri, $replace_params);
	}

	/**
	 * Sends a POST request.
	 *
	 * @param string $url
	 * @param array $headers
	 * @param string $content
	 * @return NostoTaggingHttpResponse
	 */
	public function post($url, array $headers = array(), $content = '')
	{
		return $this->send($url, array(
			'http' => array(
				'method' => 'POST',
				'header' => implode("\r\n", $headers),
				'content' => $content
			)
		));
	}

	/**
	 * Sends a GET request.
	 *
	 * @param string $url
	 * @param array $headers
	 * @param array $params
	 * @return NostoTaggingHttpResponse
	 */
	public function get($url, array $headers = array(), array $params = array())
	{
		if (!empty($params))
			$url .= '?'.http_build_query($params);
		return $this->send($url, array(
			'http' => array(
				'method' => 'GET',
				'header' => implode("\r\n", $headers),
			)
		));
	}

	/**
	 * Sends the request and returns a response instance.
	 *
	 * @param string $url
	 * @param array $options
	 * @return NostoTaggingHttpResponse
	 */
	protected function send($url, array $options = array())
	{
		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);
		$response = new NostoTaggingHttpResponse();
		if (isset($http_response_header))
			$response->setHttpResponseHeader($http_response_header);
		$response->setResult($result);
		return $response;
	}
}
