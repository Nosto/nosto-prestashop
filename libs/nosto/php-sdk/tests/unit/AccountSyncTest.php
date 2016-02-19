<?php

require_once(dirname(__FILE__) . '/../_support/NostoOAuthClientMetaData.php');

class AccountSyncTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

	/**
	 * Tests that existing accounts can be synced from Nosto.
	 * Accounts are synced using OAuth2 Authorization Code method.
	 * We are only testing that we can start and act on the steps in the OAuth request cycle.
	 */
	public function testSyncingExistingAccount()
    {
		$meta = new NostoOAuthClientMetaData();
		$client = new NostoOAuthClient($meta);

		$this->specify('oauth authorize url can be created', function() use ($client) {
			$this->assertEquals('http://localhost:3000?client_id=client-id&redirect_uri=http%3A%2F%2Fmy.shop.com%2Fnosto%2Foauth&response_type=code&scope=sso products&lang=en', $client->getAuthorizationUrl());
		});

		$account = NostoAccount::syncFromNosto($meta, 'test123');

		$this->specify('account was created', function() use ($account, $meta) {
			$this->assertInstanceOf('NostoAccount', $account);
			$this->assertEquals('platform-00000000', $account->getName());
		});

		$this->specify('account has api token sso', function() use ($account, $meta) {
			$token = $account->getApiToken('sso');
			$this->assertInstanceOf('NostoApiToken', $token);
			$this->assertEquals('sso', $token->getName());
			$this->assertNotEmpty($token->getValue());
		});

		$this->specify('account has api token products', function() use ($account, $meta) {
			$token = $account->getApiToken('products');
			$this->assertInstanceOf('NostoApiToken', $token);
			$this->assertEquals('products', $token->getName());
			$this->assertNotEmpty($token->getValue());
		});

		$this->specify('account is connected to nosto', function() use ($account, $meta) {
			$this->assertTrue($account->isConnectedToNosto());
		});
    }
}
