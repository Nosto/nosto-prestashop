<?php

require_once(dirname(__FILE__) . '/../_support/NostoOAuthClientMetaData.php');

class OauthTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

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
    }

    /**
     * Test the OAuth client authorization url creation.
     */
    public function testOauthAuthorizationUrl()
    {
        $meta = new NostoOAuthClientMetaData();
        $client = new NostoOAuthClient($meta);

        $this->specify('oauth authorize url can be created', function() use ($client) {
                $this->assertEquals('http://localhost:3000?client_id=client-id&redirect_uri=http%3A%2F%2Fmy.shop.com%2Fnosto%2Foauth&response_type=code&scope=sso products&lang=en', $client->getAuthorizationUrl());
            });
    }

    /**
     * Test the OAuth client authorization url creation with existing nosto account.
     */
    public function testOauthAuthorizationUrlWithMerchant()
    {
        $meta = new NostoOAuthClientMetaData();
        $meta->setAccount(new NostoAccount('platform-test'));
        $client = new NostoOAuthClient($meta);

        $this->specify('oauth authorize url with merchant can be created', function() use ($client) {
                $this->assertEquals('http://localhost:3000?client_id=client-id&redirect_uri=http%3A%2F%2Fmy.shop.com%2Fnosto%2Foauth&response_type=code&scope=sso+products&lang=en&merchant=platform-test', $client->getAuthorizationUrl());
            });
    }

	/**
	 * Test the OAuth client authenticate without a authorize code.
	 */
	public function testOauthAuthenticateWithoutCode()
    {
		$meta = new NostoOAuthClientMetaData();
		$client = new NostoOAuthClient($meta);

		$this->specify('failed oauth authenticate', function() use ($client) {
			$this->setExpectedException('NostoException');
			$client->authenticate('');
		});
    }

    /**
     * Tests that oauth tokens can be created.
     */
    public function testValidOauthToken()
    {
        $token = new NostoOAuthToken('platform-test', '123');

        $this->specify('oauth token format is valid', function() use ($token) {
                $this->assertEquals('platform-test', $token->getMerchantName());
                $this->assertEquals('123', $token->getAccessToken());
            });
    }

    /**
     * Tests that oauth tokens with invalid merchant name cannot created.
     */
    public function testInvalidOauthTokenMerchant()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoOAuthToken('', '123');
    }

    /**
     * Tests that oauth tokens with invalid token values cannot created.
     */
    public function testInvalidOauthTokenValue()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoOAuthToken('platform-test', '');
    }
}
