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
 * Represents a http request response returned by NostoTaggingHttpRequest.
 */
class NostoTaggingHttpResponse
{
	/**
	 * @var array the response headers if there are any.
	 */
	protected $http_response_header;

	/**
	 * @var mixed the request result raw body.
	 */
	protected $result;

	/**
	 * @var string possible request error message.
	 */
	protected $error_message;

	/**
	 * @param mixed $result
	 */
	public function setResult($result)
	{
		$this->result = $result;
	}

	/**
	 * @return mixed
	 */
	public function getResult()
	{
		return $this->result;
	}

	/**
	 * @param string $error_message
	 */
	public function setErrorMessage($error_message)
	{
		$this->error_message = $error_message;
	}

	/**
	 * @return string
	 */
	public function getErrorMessage()
	{
		return $this->error_message;
	}

	/**
	 * @param bool $assoc
	 * @return mixed
	 */
	public function getJsonResult($assoc = false)
	{
		return Tools::jsonDecode($this->result, $assoc);
	}

	/**
	 * @param array $http_response_header
	 */
	public function setHttpResponseHeader($http_response_header)
	{
		$this->http_response_header = $http_response_header;
	}

	/**
	 * Returns the http response code.
	 *
	 * @return int
	 */
	public function getCode()
	{
		$matches = array();
		if (isset($this->http_response_header) && isset($this->http_response_header[0]))
			preg_match('|HTTP/\d\.\d\s+(\d+)\s+.*|', $this->http_response_header[0], $matches);
		return isset($matches[1]) ? (int)$matches[1] : 0;
	}

	/**
	 * Returns the raw http status string.
	 *
	 * @return string the status string or empty if not set.
	 */
	public function getRawStatus()
	{
		if (isset($this->http_response_header) && isset($this->http_response_header[0]))
			return $this->http_response_header[0];
		return '';
	}
}
