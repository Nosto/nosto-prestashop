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
    }

    /**
     * @inheritdoc
     */
    protected function _after()
    {
        \AspectMock\test::clean();
    }

	/**
	 * Tests setting query params to the request.
	 */
	public function testHttpRequestQueryParams()
	{
		$request = new NostoHttpRequest();
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
	public function testHttpRequestAuthBasic()
	{
		$request = new NostoHttpRequest();
		$request->setAuthBasic('test', 'test');
		$headers = $request->getHeaders();
		$this->assertContains('Authorization: Basic '.base64_encode(implode(':', array('test', 'test'))), $headers);
	}

	/**
	 * Tests setting the bearer auth type.
	 */
	public function testHttpRequestAuthBearer()
	{
		$request = new NostoHttpRequest();
		$request->setAuthBearer('test');
		$headers = $request->getHeaders();
		$this->assertContains('Authorization: Bearer test', $headers);
	}

	/**
	 * Tests setting an invalid auth type.
	 */
	public function testHttpRequestAuthInvalid()
	{
		$this->setExpectedException('NostoException');
		$request = new NostoHttpRequest();
		$request->setAuth('test', 'test');
	}

	/**
	 * Tests the "buildUri" helper method.
	 */
	public function testHttpRequestBuildUri()
	{
		$uri = NostoHttpRequest::buildUri(
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
	public function testHttpRequestBuildUrl()
	{
		$url_parts = NostoHttpRequest::parseUrl('http://localhost:9000/tmp/?param1=first&param2=second#fragment1=test');
		$url = NostoHttpRequest::buildUrl($url_parts);
		$this->assertEquals('http://localhost:9000/tmp/?param1=first&param2=second#fragment1=test', $url);
	}

	/**
	 * Tests the "parseQueryString" helper method.
	 */
	public function testHttpRequestParseQueryString()
	{
		$query_string_parts = NostoHttpRequest::parseQueryString('param1=first&param2=second');
		$this->assertArrayHasKey('param1', $query_string_parts);
		$this->assertContains('first', $query_string_parts);
		$this->assertArrayHasKey('param2', $query_string_parts);
		$this->assertContains('second', $query_string_parts);
	}

	/**
	 * Tests the "replaceQueryParamInUrl" helper method.
	 */
	public function testHttpRequestReplaceQueryParamInUrl()
	{
		$url = NostoHttpRequest::replaceQueryParamInUrl('param1', 'replaced_first', 'http://localhost:9000/tmp/?param1=first&param2=second');
		$this->assertEquals('http://localhost:9000/tmp/?param1=replaced_first&param2=second', $url);
	}

    /**
     * Tests the "replaceQueryParamsInUrl" helper method.
     */
    public function testHttpRequestReplaceQueryParamsInUrl()
    {
        $url = NostoHttpRequest::replaceQueryParamsInUrl(array('param1' => 'replaced_first', 'param2' => 'replaced_second'), 'http://localhost:9000/tmp/?param1=first&param2=second');
        $this->assertEquals('http://localhost:9000/tmp/?param1=replaced_first&param2=replaced_second', $url);
    }

	/**
	 * Tests the "replaceQueryParam" helper method.
	 */
	public function testHttpRequestReplaceQueryParam()
	{
		$query_string = NostoHttpRequest::replaceQueryParam('param2', 'replaced_second', 'param1=first&param2=second');
		$this->assertEquals('param1=first&param2=replaced_second', $query_string);
	}

	/**
	 * Tests the http request response result.
	 */
	public function testHttpRequestResponseResult()
	{
		$response = new NostoHttpResponse(array(), json_encode(array('test' => true)));
		$this->assertEquals('{"test":true}', $response->getResult());
		$result = $response->getJsonResult(true);
		$this->assertArrayHasKey('test', $result);
		$this->assertContains(true, $result);
	}

	/**
	 * Tests the http request response error message.
	 */
	public function testHttpRequestResponseErrorMessage()
	{
		$response = new NostoHttpResponse(array(), '', 'error');
		$this->assertEquals('error', $response->getMessage());
	}

	/**
	 * Tests the http request curl adapter.
	 */
	public function testHttpRequestCurlAdapter()
	{
		$request = new NostoHttpRequest(new NostoHttpRequestAdapterCurl());
		$request->setUrl('http://localhost:3000');
		$response = $request->get();
		$this->assertEquals(404, $response->getCode());
		$response = $request->post('test');
		$this->assertEquals(404, $response->getCode());
        $response = $request->put('test');
        $this->assertEquals(404, $response->getCode());
        $response = $request->delete();
        $this->assertEquals(404, $response->getCode());
		$request->setUrl('http://localhost:9000');
		$response = $request->get();
		$this->assertEquals('Failed to connect to localhost port 9000: Connection refused', $response->getMessage());
	}

	/**
	 * Tests the http request socket adapter.
	 */
	public function testHttpRequestSocketAdapter()
	{
		$request = new NostoHttpRequest(new NostoHttpRequestAdapterSocket());
		$request->setUrl('http://localhost:3000');
		$response = $request->get();
		$this->assertEquals(404, $response->getCode());
		$response = $request->post('test');
		$this->assertEquals(404, $response->getCode());
        $response = $request->put('test');
        $this->assertEquals(404, $response->getCode());
        $response = $request->delete();
        $this->assertEquals(404, $response->getCode());
	}

    /**
     * Tests to create a http request with adapter set to "auto" while not having curl enabled.
     */
    public function testHttpRequestAutoAdapterWithoutCurlEnabled()
    {
        $mock = \AspectMock\test::double('NostoHttpRequest', ['canUseCurl' => false]);

        $request = new NostoHttpRequest();
        $mock->verifyInvoked('canUseCurl');
        $request->setUrl('http://localhost:3000');
        $response = $request->get();
        $this->assertEquals(404, $response->getCode());
    }

    /**
     * Tests the http request __toString method.
     */
    public function testHttpRequestToString()
    {
        $request = new NostoHttpRequest();
        $request->setUrl('http://localhost:3000/{path}');
        $request->setContentType('application/json');
        $request->setReplaceParams(array('{path}' => 'test'));
        $this->assertEquals('a:3:{s:3:"url";s:26:"http://localhost:3000/test";s:7:"headers";a:1:{i:0;s:30:"Content-type: application/json";}s:4:"body";s:0:"";}', $request->__toString());
    }

    /**
     * Tests the http response __toString method.
     */
    public function testHttpResponseToString()
    {
        $response = new NostoHttpResponse(array('HTTP/1.1 404 Not Found', 'Content-type: application/json'), '{}');
        $this->assertEquals('a:3:{s:7:"headers";a:2:{i:0;s:22:"HTTP/1.1 404 Not Found";i:1;s:30:"Content-type: application/json";}s:4:"body";s:2:"{}";s:5:"error";N;}', $response->__toString());
    }
}
