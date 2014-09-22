<?php

/**
 * Represents a http request response returned by NostoTaggingHttpRequest.
 */
class NostoTaggingHttpResponse
{
	/**
	 * @var array
	 */
	protected $http_response_header;

	/**
	 * @var mixed
	 */
	protected $result;

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
		if (isset($this->http_response_header) &&  isset($this->http_response_header[0]))
			preg_match('|HTTP/\d\.\d\s+(\d+)\s+.*|', $this->http_response_header[0], $matches);
		return isset($matches[1]) ? (int)$matches[1] : 0;
	}
}
