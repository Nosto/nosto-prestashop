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
}
