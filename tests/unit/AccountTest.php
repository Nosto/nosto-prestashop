<?php


class AccountTest extends \Codeception\TestCase\Test
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
		NostoTaggingApiRequest::$base_url = $this->tester->getApiBaseUrl();
		$context = $this->tester->getContext();
		$context->employee = $this->tester->createEmployee();
	}

	/**
	 * @inheritdoc
	 */
	protected function _after()
	{
	}

	/**
	 * Tests creating a new account.
	 */
	public function testAccountCreation()
    {
		$id_lang = 1;
		$result = NostoTaggingAccount::create($this->tester->getContext(), null, $id_lang);
		$this->assertFalse($result);
    }
}