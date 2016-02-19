<?php

require_once(dirname(__FILE__) . '/../_support/NostoProduct.php');

class ProductReCrawlTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests that product re-crawl API requests cannot be made without an API token.
     */
    public function testSendingProductReCrawlWithoutApiToken()
    {
		$account = new NostoAccount('platform-00000000');
        $product = new NostoProduct();

        $this->setExpectedException('NostoException');
        NostoProductReCrawl::send($product, $account);
    }

	/**
	 * Tests that product re-crawl API requests can be made.
	 */
	public function testSendingProductReCrawl()
    {
		$account = new NostoAccount('platform-00000000');
		$product = new NostoProduct();
		$token = new NostoApiToken('products', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$account->addApiToken($token);

		$result = NostoProductReCrawl::send($product, $account);

		$this->specify('successful product re-crawl', function() use ($result) {
			$this->assertTrue($result);
		});
    }

    /**
     * Tests that batch product re-crawl API requests cannot be made without an API token.
     */
    public function testSendingBatchProductReCrawlWithoutApiToken()
    {
		$account = new NostoAccount('platform-00000000');
        $product = new NostoProduct();
        $collection = new NostoExportProductCollection();
        $collection[] = $product;

        $this->setExpectedException('NostoException');
        NostoProductReCrawl::sendBatch($collection, $account);
    }

    /**
     * Tests that batch product re-crawl API requests can be made.
     */
    public function testSendingBatchProductReCrawl()
    {
		$account = new NostoAccount('platform-00000000');
        $product = new NostoProduct();
        $collection = new NostoExportProductCollection();
        $collection[] = $product;
		$token = new NostoApiToken('products', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$account->addApiToken($token);

        $result = NostoProductReCrawl::sendBatch($collection, $account);

        $this->specify('successful batch product re-crawl', function() use ($result) {
            $this->assertTrue($result);
        });
    }
}
