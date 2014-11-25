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
 * Adapter class for making http requests using curl.
 * This adapter requires curl to be installed.
 */
class NostoTaggingHttpRequestAdapterCurl extends NostoTaggingHttpRequestAdapter
{
	/**
	 * @inheritdoc
	 */
	public function get($url, array $options = array())
	{
		$this->init($options);
		return $this->send(array(
			CURLOPT_URL => $url,
			CURLOPT_HEADER => 1,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_TIMEOUT => 4,
		));
	}

	/**
	 * @inheritdoc
	 */
	public function post($url, array $options = array())
	{
		$this->init($options);
		return $this->send(array(
			CURLOPT_URL => $url,
			CURLOPT_POSTFIELDS => $this->content,
			CURLOPT_POST => 1,
			CURLOPT_HEADER => 1,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_TIMEOUT => 4,
		));
	}

	/**
	 * Sends the request and creates a NostoTaggingHttpResponse instance containing the response headers and body.
	 *
	 * @param array $curl_options options for curl_setopt_array().
	 * @return NostoTaggingHttpResponse
	 */
	protected function send(array $curl_options)
	{
		if (!empty($this->headers))
			$curl_options[CURLOPT_HTTPHEADER] = $this->headers;
		$ch = curl_init();
		curl_setopt_array($ch, $curl_options);
		$result = curl_exec($ch);
		$response = new NostoTaggingHttpResponse();
		if ($result !== false)
		{
			$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
			$header = Tools::substr($result, 0, $header_size);
			$header = explode("\r\n", $header);
			$body = Tools::substr($result, $header_size);
			if (!empty($header))
				$response->setHttpResponseHeader($header);
			$response->setResult($body);
		}
		else
		{
			$response->setErrorMessage(curl_error($ch));
		}
		return $response;
	}
}
