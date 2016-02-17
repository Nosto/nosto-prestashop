<?php


class AccountDeleteTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Test the account deletion without the required SSO token.
     */
    public function testDeletingAccountWithoutToken()
    {
        $account = new NostoAccount('platform-test');

        $this->specify('account is NOT deleted', function() use ($account) {
            $this->setExpectedException('NostoException');
            $account->delete();
        });
    }

    /**
     * Test the account deletion with the required SSO token.
     */
    public function testDeletingAccountWithToken()
    {
        $account = new NostoAccount('platform-test');
        $token = new NostoApiToken('sso', '123');
		$account->addApiToken($token);

        $this->specify('account is deleted', function() use ($account) {
            $account->delete();
        });
    }
}