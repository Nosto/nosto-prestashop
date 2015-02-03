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

require_once(dirname(__FILE__).'/nostotagging-http-request-adapter.php');
require_once(dirname(__FILE__).'/nostotagging-http-request-adapter-socket.php');
require_once(dirname(__FILE__).'/nostotagging-http-request-adapter-curl.php');

/**
 * Helper class for doing http requests and returning unified response including header info.
 */
class NostoTaggingHttpRequest
{
	const AUTH_BASIC = 'basic';
	const AUTH_BEARER = 'bearer';

	const PATH_ACCOUNT_DELETED = '/hub/uninstall';

	/**
	 * @var string base url for the nosto web hook requests.
	 */
	public static $base_url = 'https://my.nosto.com';

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
	 * @var NostoTaggingHttpRequestAdapter the adapter to use for making the request.
	 */
	private $_adapter;

	/**
	 * Constructor.
	 * Chooses request adapter based on what available in the environment.
	 */
	public function __construct()
	{
		if (function_exists('curl_exec'))
			$this->_adapter = new NostoTaggingHttpRequestAdapterCurl();
		else
			$this->_adapter = new NostoTaggingHttpRequestAdapterSocket();
	}

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
	 * Returns the registered headers.
	 *
	 * @return array
	 */
	public function getHeaders()
	{
		return $this->headers;
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
	 * Returns the registered query params.
	 *
	 * @return array
	 */
	public function getQueryParams()
	{
		return $this->query_params;
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
				// The use of base64 encoding for authorization headers follow the RFC 2617 standard for http
				// authentication (https://www.ietf.org/rfc/rfc2617.txt).
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
	public static function buildUri($uri, array $replace_params)
	{
		return strtr($uri, $replace_params);
	}

	/**
	 * Builds a url based on given parts.
	 *
	 * @see http://php.net/manual/en/function.parse-url.php
	 * @param array $parts part(s) of an URL in form of a string or associative array like parseUrl() returns.
	 * @return string
	 */
	public static function buildUrl(array $parts)
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
	public static function parseUrl($url)
	{
		return parse_url($url);
	}

	/**
	 * Parses the given query string and returns the parts as an assoc array.
	 *
	 * @see http://php.net/manual/en/function.parse-str.php
	 * @param string $query_string the query string to parse.
	 * @return array the parsed string as assoc array.
	 */
	public static function parseQueryString($query_string)
	{
		if (empty($query_string))
			return array();
		parse_str($query_string, $parsed_query_string);
		return $parsed_query_string;
	}

	/**
	 * Replaces a parameter in a query string with given value.
	 *
	 * @param string $param the query param name to replace.
	 * @param mixed $value the query param value to replace.
	 * @param string $query_string the query string.
	 * @return string the updated query string.
	 */
	public static function replaceQueryParam($param, $value, $query_string)
	{
		$parsed_query = self::parseQueryString($query_string);
		$parsed_query[$param] = $value;
		return http_build_query($parsed_query);
	}

	/**
	 * Replaces or adds a query parameter to a url.
	 *
	 * @param string $param the query param name to replace.
	 * @param mixed $value the query param value to replace.
	 * @param string $url the url.
	 * @return string the updated url.
	 */
	public static function replaceQueryParamInUrl($param, $value, $url)
	{
		$parsed_url = self::parseUrl($url);
		$query_string = isset($parsed_url['query']) ? $parsed_url['query'] : '';
		$query_string = NostoTaggingHttpRequest::replaceQueryParam($param, $value, $query_string);
		$parsed_url['query'] = $query_string;
		return NostoTaggingHttpRequest::buildUrl($parsed_url);
	}

	/**
	 * Replaces or adds a query parameters to a url.
	 *
	 * @param array $query_params the query params to replace.
	 * @param string $url the url.
	 * @return string the updated url.
	 */
	public static function replaceQueryParamsInUrl(array $query_params, $url)
	{
		if (empty($query_params))
			return $url;
		$parsed_url = self::parseUrl($url);
		$query_string = isset($parsed_url['query']) ? $parsed_url['query'] : '';
		foreach ($query_params as $param => $value)
			$query_string = NostoTaggingHttpRequest::replaceQueryParam($param, $value, $query_string);
		$parsed_url['query'] = $query_string;
		return NostoTaggingHttpRequest::buildUrl($parsed_url);
	}

	/**
	 * Sends a POST request.
	 *
	 * @param string $content
	 * @return NostoTaggingHttpResponse
	 */
	public function post($content)
	{
		$url = $this->url;
		if (!empty($this->replace_params))
			$url = self::buildUri($url, $this->replace_params);
		return $this->_adapter->post($url, array(
			'headers' => $this->headers,
			'content' => $content,
		));
	}

	/**
	 * Sends a GET request.
	 *
	 * @return NostoTaggingHttpResponse
	 */
	public function get()
	{
		$url = $this->url;
		if (!empty($this->replace_params))
			$url = self::buildUri($url, $this->replace_params);
		if (!empty($this->query_params))
			$url .= '?'.http_build_query($this->query_params);
		return $this->_adapter->get($url, array(
			'headers' => $this->headers,
		));
	}
}
