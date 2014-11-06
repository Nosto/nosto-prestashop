<?php


class HttpRequestTest extends \Codeception\TestCase\Test
{
   /**
    * @var \UnitTester
    */
    protected $tester;

	/**
	 * @inheritdoc
	 */
	protected function _before()
	{
		$this->tester->initPs();
	}

	/**
	 * @inheritdoc
	 */
	protected function _after()
	{
	}

	/**
	 * Tests setting query params to the request.
	 */
	public function testQueryParams()
	{
		$request = new NostoTaggingHttpRequest();
		$request->setQueryParams(array(
			'param1' => 'first',
			'param2' => 'second',
		));
		$params = $request->getQueryParams();
		$this->assertArrayHasKey('param1', $params);
		$this->assertContains('first', $params);
		$this->assertArrayHasKey('param2', $params);
		$this->assertContains('second', $params);
	}

	/**
	 * Tests setting the basic auth type.
	 */
	public function testAuthBasic()
    {
		$request = new NostoTaggingHttpRequest();
		$request->setAuthBasic('test', 'test');
		$headers = $request->getHeaders();
		$this->assertContains('Authorization: Basic '.base64_encode(implode(':', array('test', 'test'))), $headers);
    }

	/**
	 * Tests setting the bearer auth type.
	 */
	public function testAuthBearer()
	{
		$request = new NostoTaggingHttpRequest();
		$request->setAuthBearer('test');
		$headers = $request->getHeaders();
		$this->assertContains('Authorization: Bearer test', $headers);
	}

	/**
	 * Tests the "buildUri" helper method.
	 */
	public function testBuildUri()
	{
		$uri = NostoTaggingHttpRequest::buildUri(
			'http://localhost:9000?param1={p1}&param2={p2}',
			array(
				'{p1}' => 'first',
				'{p2}' => 'second'
			)
		);
		$this->assertEquals('http://localhost:9000?param1=first&param2=second', $uri);
	}

	/**
	 * Tests the "buildUrl" helper method.
	 */
	public function testBuildUrl()
	{
		$url_parts = NostoTaggingHttpRequest::parseUrl('http://localhost:9000/tmp/?param1=first&param2=second#fragment1=test');
		$url = NostoTaggingHttpRequest::buildUrl($url_parts);
		$this->assertEquals('http://localhost:9000/tmp/?param1=first&param2=second#fragment1=test', $url);
	}

	/**
	 * Tests the "parseQueryString" helper method.
	 */
	public function testParseQueryString()
	{
		$query_string_parts = NostoTaggingHttpRequest::parseQueryString('param1=first&param2=second');
		$this->assertArrayHasKey('param1', $query_string_parts);
		$this->assertContains('first', $query_string_parts);
		$this->assertArrayHasKey('param2', $query_string_parts);
		$this->assertContains('second', $query_string_parts);
	}

	/**
	 * Tests the "replaceQueryParam" helper method.
	 */
	public function testReplaceQueryParam()
	{
		$query_string = NostoTaggingHttpRequest::replaceQueryParam('param2', 'replaced_second', 'param1=first&param2=second');
		$this->assertEquals('param1=first&param2=replaced_second', $query_string);
	}

	/**
	 * Tests the http request response status code header.
	 */
	public function testResponseStatusCodeHeader()
	{
		$response = new NostoTaggingHttpResponse();
		$response->setHttpResponseHeader(array(0 => 'HTTP/1.1 404 Not Found'));
		$this->assertEquals(404, $response->getCode());
		$this->assertEquals('HTTP/1.1 404 Not Found', $response->getRawStatus());
	}

	/**
	 * Tests the http request response result.
	 */
	public function testResponseResult()
	{
		$response = new NostoTaggingHttpResponse();
		$response->setResult(json_encode(array('test' => true)));
		$this->assertEquals('{"test":true}', $response->getResult());
		$result = $response->getJsonResult(true);
		$this->assertArrayHasKey('test', $result);
		$this->assertContains(true, $result);
	}
}