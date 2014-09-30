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
	 * @param bool $assoc
	 * @return mixed
	 */
	public function getJsonResult($assoc = false)
	{
		return json_decode($this->result, $assoc);
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

	/**
	 * Returns the raw http status string.
	 *
	 * @return string the status string or empty if not set.
	 */
	public function getRawStatus()
	{
		if (isset($this->http_response_header) &&  isset($this->http_response_header[0]))
			return $this->http_response_header[0];
		return '';
	}
}
