<?php


class OauthTest extends \Codeception\TestCase\Test
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
	 * Tests the creation of the oauth authorize url.
	 */
	public function testAuthorizeUrl()
    {
		$module = $this->tester->getNostoTagging();
		$base_url = $this->tester->getOauthBaseUrl();
		$redirect_url = urlencode($module->getOAuth2ControllerUrl());
		$lang = $module->getContext()->language->iso_code;
		$tokens = NostoTaggingApiToken::$api_token_names;
		$scope = implode(' ', $tokens);

		$client = new NostoTaggingOAuth2Client();
		$client->setClientId('test');
		$client->setClientSecret('test');
		$client->setRedirectUrl($redirect_url);
		$client->setScopes($tokens);

		$query_params = 'client_id=test&redirect_uri='.$redirect_url.'&response_type=code&scope='.$scope.'&lang='.$lang;
		$this->assertEquals($base_url.'?'.$query_params, $client->getAuthorizationUrl());
    }

	/**
	 * Tests the authentication of the auth code.
	 */
	public function testAuthenticate()
	{
		$client = new NostoTaggingOAuth2Client();
		$client->setRedirectUrl('http://localhost');
		$token = $client->authenticate('dummy');
		$this->assertFalse($token);
	}

	/**
	 * Tests the creation of an oauth2 token instance.
	 */
	public function testTokenCreation()
	{
		$token = NostoTaggingOAuth2Token::create(array('access_token' => 'dummy'));
		$this->assertInstanceOf('NostoTaggingOAuth2Token', $token);
		$this->assertObjectHasAttribute('access_token', $token);
		$this->assertEquals($token->access_token, 'dummy');
	}

	/**
	 * Tests the exchange data with nosto method.
	 */
	public function testExchangeDataWithNosto()
	{
		$module = $this->tester->getNostoTagging();
		$token = NostoTaggingOAuth2Token::create(array('access_token' => 'dummy'));
		$result = $module->exchangeDataWithNosto($token);
		$this->assertFalse($result);
	}
}