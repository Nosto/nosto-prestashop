<?php


class CipherTest extends \Codeception\TestCase\Test
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
	 * Tests encrypting and decrypting a string using the nosto cipher class.
	 */
	public function testEncryptDecrypt()
    {
		$cipher = new NostoTaggingCipher('test');
		$cipher_text = $cipher->encrypt('test');
		$plain_text = $cipher->decrypt($cipher_text);
		$this->assertEquals('test', $plain_text);
    }
}