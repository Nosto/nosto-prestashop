<?php

/**
 * Helper class for doing http requests and returning unified response including header info.
 */
class NostoTaggingHttpRequest
{
	const AUTH_BASIC = 'basic';
	const AUTH_BEARER = 'bearer';

	/**
	 * @var string the request url.
	 */
	protected $url;

	/**
	 * @var array list of headers to include in the requests.
	 */
	protected $headers = array();

	/**
	 * @var array list of optional query params that are added to the request url.
	 */
	protected $query_params = array();

	/**
	 * @var array list of optional replace params that can be injected into the url if it contains placeholders.
	 */
	protected $replace_params = array();

	/**
	 * Setter for the request url.
	 *
	 * @param string $url the url.
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}

	/**
	 * Setter for the content type to add to the request header.
	 *
	 * @param string $content_type the content type.
	 */
	public function setContentType($content_type)
	{
		$this->addHeader('Content-type', $content_type);
	}

	/**
	 * Adds a new header to the request.
	 *
	 * @param string $key the header key, e.g. 'Content-type'.
	 * @param string $value the header value, e.g. 'application/json'.
	 */
	public function addHeader($key, $value)
	{
		$this->headers[] = $key.': '.$value;
	}

	/**
	 * Setter for the request url query params.
	 *
	 * @param array $query_params the query params.
	 */
	public function setQueryParams($query_params)
	{
		$this->query_params = $query_params;
	}

	/**
	 * Setter for the request url replace params.
	 *
	 * @param array $replace_params the replace params.
	 */
	public function setReplaceParams($replace_params)
	{
		$this->replace_params = $replace_params;
	}

	/**
	 * Setter for the request authentication header.
	 *
	 * @param string $type the auth type (use AUTH_ constants).
	 * @param mixed $value the auth header value, format depending on the auth type.
	 * @throws Exception if an incorrect auth type is given.
	 */
	public function setAuth($type, $value)
	{
		switch ($type)
		{
			case self::AUTH_BASIC:
				$this->addHeader('Authorization', 'Basic '.base64_encode(implode(':', $value)));
				break;

			case self::AUTH_BEARER:
				$this->addHeader('Authorization', 'Bearer '.$value);
				break;

			default:
				throw new Exception('Unsupported auth type.');
		}
	}

	/**
	 * Convenience method for setting the basic auth type.
	 *
	 * @param string $username the user name.
	 * @param string $password the password.
	 */
	public function setAuthBasic($username, $password)
	{
		$this->setAuth(self::AUTH_BASIC, array($username, $password));
	}

	/**
	 * Convenience method for setting the bearer auth type.
	 *
	 * @param string $token the access token.
	 */
	public function setAuthBearer($token)
	{
		$this->setAuth(self::AUTH_BEARER, $token);
	}

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
	 * Builds a url based on given parts.
	 *
	 * @see http://php.net/manual/en/function.parse-url.php
	 * @param array $parts part(s) of an URL in form of a string or associative array like parse_url() returns.
	 * @return string
	 */
	public static function build_url(array $parts)
	{
		$scheme = isset($parts['scheme']) ? $parts['scheme'].'://' : '';
		$host = isset($parts['host']) ? $parts['host'] : '';
		$port = isset($parts['port']) ? ':'.$parts['port'] : '';
		$user = isset($parts['user']) ? $parts['user'] : '';
		$pass = isset($parts['pass']) ? ':'.$parts['pass']  : '';
		$pass = ($user || $pass) ? "$pass@" : '';
		$path = isset($parts['path']) ? $parts['path'] : '';
		$query = isset($parts['query']) ? '?'.$parts['query'] : '';
		$fragment = isset($parts['fragment']) ? '#'.$parts['fragment'] : '';
		return $scheme.$user.$pass.$host.$port.$path.$query.$fragment;
	}

	/**
	 * Parses the given url and returns the parts as an array.
	 *
	 * @see http://php.net/manual/en/function.parse-url.php
	 * @param string $url the url to parse.
	 * @return array the parsed url as an array.
	 */
	public static function parse_url($url)
	{
		return parse_url($url);
	}

	/**
	 * Replaces a parameter in a query string with given value.
	 *
	 * @param string $param the query param name to replace.
	 * @param mixed $value the query param value to replace.
	 * @param string $query_string the query string.
	 * @return string the updated query string.
	 */
	public static function replace_query_param($param, $value, $query_string)
	{
		if (empty($query_string))
			$parsed_query = array();
		else
			parse_str($query_string, $parsed_query);
		$parsed_query[$param] = $value;
		return http_build_query($parsed_query);
	}

	/**
	 * Sends a POST request.
	 *
	 * @param string $content
	 * @return NostoTaggingHttpResponse
	 */
	public function post($content)
	{
		return $this->send($this->url, array(
			'http' => array(
				'method' => 'POST',
				'header' => implode("\r\n", $this->headers),
				'content' => $content
			)
		));
	}

	/**
	 * Sends a GET request.
	 *
	 * @return NostoTaggingHttpResponse
	 */
	public function get()
	{
		return $this->send($this->url, array(
			'http' => array(
				'method' => 'GET',
				'header' => implode("\r\n", $this->headers),
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
		if (!empty($this->replace_params))
			$url = self::build_uri($url, $this->replace_params);
		if (!empty($this->query_params))
			$url .= '?'.http_build_query($this->query_params);
		$context = stream_context_create($options);
		$result = @file_get_contents($url, false, $context);
		$response = new NostoTaggingHttpResponse();
		if (isset($http_response_header))
			$response->setHttpResponseHeader($http_response_header);
		$response->setResult($result);
		return $response;
	}
}
