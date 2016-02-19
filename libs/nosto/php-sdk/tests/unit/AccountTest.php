<?php

class AccountTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

	/**
	 * Tests the "isConnectedToNosto" method for the NostoAccount class.
	 */
	public function testAccountIsConnected()
	{
		$account = new NostoAccount('platform-test');

		$this->specify('account is not connected', function() use ($account) {
			$this->assertFalse($account->isConnectedToNosto());
		});

		$token = new NostoApiToken('sso', '123');
		$account->addApiToken($token);

		$token = new NostoApiToken('products', '123');
		$account->addApiToken($token);

		$this->specify('account is NOT connected', function() use ($account) {
			$this->assertFalse($account->isConnectedToNosto());
		});

        $token = new NostoApiToken('rates', '123');
        $account->addApiToken($token);

        $token = new NostoApiToken('settings', '123');
        $account->addApiToken($token);

        $this->specify('account IS connected', function() use ($account) {
            $this->assertTrue($account->isConnectedToNosto());
        });
	}

	/**
	 * Tests the "getApiToken" method for the NostoAccount class.
	 */
	public function testAccountApiToken()
	{
		$account = new NostoAccount('platform-test');

		$this->specify('account does not have sso token', function() use ($account) {
			$this->assertNull($account->getApiToken('sso'));
		});

		$token = new NostoApiToken('sso', '123');
		$account->addApiToken($token);

		$this->specify('account has sso token', function() use ($account) {
			$this->assertEquals('123', $account->getApiToken('sso')->getValue());
		});
	}

    /**
     * Test that you cannot create a nosto account object with an invalid name.
     */
    public function testInvalidAccountName()
    {
        $this->setExpectedException('NostoInvalidArgumentException');

        new NostoAccount(null);
    }

    /**
     * Test the account object equals method.
     */
    public function testAccountEquality()
    {
        $oldAccount = new NostoAccount('platform-test');
        $newAccount = new NostoAccount('platform-test');

        $this->specify('two accounts are equal', function() use ($oldAccount, $newAccount) {
                $this->assertTrue($newAccount->equals($oldAccount));
                $this->assertTrue($oldAccount->equals($newAccount));
            });
    }

    /**
     * Tests that account tokens can be fetched.
     */
    public function testAccountTokenGetter()
    {
        $account = new NostoAccount('platform-test');
        $token = new NostoApiToken('sso', '123');
        $account->addApiToken($token);
        $tokens = $account->getTokens();

        $this->specify('account tokens were retreived', function() use ($tokens) {
                $this->assertNotEmpty($tokens);
            });
    }
}
