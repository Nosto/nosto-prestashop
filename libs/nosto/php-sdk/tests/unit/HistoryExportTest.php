<?php

require_once(dirname(__FILE__) . '/../_support/NostoProduct.php');
require_once(dirname(__FILE__) . '/../_support/NostoOrder.php');

class HistoryExportTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

	/**
	 * @var NostoAccount
	 */
	protected $account;

	/**
	 * @inheritdoc
	 */
	protected function _before()
	{
		$this->account = new NostoAccount('platform-00000000');
		// The first 16 chars of the SSO token are used as the encryption key.
		$token = new NostoApiToken('sso', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$this->account->addApiToken($token);
	}

	/**
	 * Tests that product history data can be exported.
	 */
	public function testProductHistoryExport()
	{
		$collection = new NostoExportCollectionProduct();
		$collection[] = new NostoProduct();
		$cipher_text = NostoExporter::export($this->account, $collection);

		$this->specify('verify encrypted product export', function() use ($collection, $cipher_text) {
			$cipher = new NostoCipher();
			$cipher->setSecret('01098d0fc84ded7c');
			$cipher->setIV(substr($cipher_text, 0, 16));
			$plain_text = $cipher->decrypt(substr($cipher_text, 16));

			$this->assertEquals($collection->getJson(), $plain_text);
		});
	}

	/**
	 * Tests that order history data can be exported.
	 */
    public function testOrderHistoryExport()
    {
		$collection = new NostoExportCollectionOrder();
		$collection->append(new NostoOrder());
		$cipher_text = NostoExporter::export($this->account, $collection);

		$this->specify('verify encrypted order export', function() use ($collection, $cipher_text) {
			$cipher = new NostoCipher();
			$cipher->setSecret('01098d0fc84ded7c');
			$cipher->setIV(substr($cipher_text, 0, 16));
			$plain_text = $cipher->decrypt(substr($cipher_text, 16));

			$this->assertEquals($collection->getJson(), $plain_text);
		});
    }
}
