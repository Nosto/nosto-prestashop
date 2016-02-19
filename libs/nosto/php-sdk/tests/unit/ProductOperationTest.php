<?php

require_once(dirname(__FILE__) . '/../_support/NostoProduct.php');

class ProductOperationTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

	/**
	 * Tests that product upsert API requests cannot be made without an API token.
	 */
	public function testSendingProductUpsertWithoutApiToken()
	{
		$account = new NostoAccount('platform-00000000');
		$product = new NostoProduct();

		$this->setExpectedException('NostoException');
		$op = new NostoOperationProduct($account);
		$op->addProduct($product);
		$op->upsert();
	}

	/**
	 * Tests that product upsert API requests cannot be made without products.
	 */
	public function testSendingProductUpsertWithoutProduct()
	{
		$account = new NostoAccount('platform-00000000');
		$token = new NostoApiToken('products', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$account->addApiToken($token);

		$this->setExpectedException('NostoException');
		$op = new NostoOperationProduct($account);
		$op->upsert();
	}

	/**
	 * Tests that product upsert API requests can be made.
	 */
	public function testSendingProductUpsert()
	{
		$account = new NostoAccount('platform-00000000');
		$product = new NostoProduct();
		$token = new NostoApiToken('products', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$account->addApiToken($token);

		$op = new NostoOperationProduct($account);
		$op->addProduct($product);
		$result = $op->upsert();

		$this->specify('successful product upsert', function() use ($result) {
			$this->assertTrue($result);
		});
	}

    /**
     * Tests that product update API requests cannot be made without an API token.
     */
    public function testSendingProductUpdateWithoutApiToken()
    {
        $account = new NostoAccount('platform-00000000');
        $product = new NostoProduct();

        $this->setExpectedException('NostoException');
        $op = new NostoOperationProduct($account);
        $op->addProduct($product);
        $op->update();
    }

    /**
     * Tests that product update API requests cannot be made without products.
     */
    public function testSendingProductUpdateWithoutProduct()
    {
        $account = new NostoAccount('platform-00000000');
        $token = new NostoApiToken('products', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
        $account->addApiToken($token);

        $this->setExpectedException('NostoException');
        $op = new NostoOperationProduct($account);
        $op->update();
    }

    /**
     * Tests that product update API requests can be made.
     */
    public function testSendingProductUpdate()
    {
        $account = new NostoAccount('platform-00000000');
        $product = new NostoProduct();
		$token = new NostoApiToken('products', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$account->addApiToken($token);

        $op = new NostoOperationProduct($account);
        $op->addProduct($product);
        $result = $op->update();

        $this->specify('successful product update', function() use ($result) {
            $this->assertTrue($result);
        });
    }

    /**
     * Tests that product create API requests cannot be made without an API token.
     */
    public function testSendingProductCreateWithoutApiToken()
    {
        $account = new NostoAccount('platform-00000000');
        $product = new NostoProduct();

        $this->setExpectedException('NostoException');
        $op = new NostoOperationProduct($account);
        $op->addProduct($product);
        $op->create();
    }


    /**
     * Tests that product create API requests cannot be made without products.
     */
    public function testSendingProductCreateWithoutProduct()
    {
        $account = new NostoAccount('platform-00000000');
		$token = new NostoApiToken('products', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$account->addApiToken($token);

        $this->setExpectedException('NostoException');
        $op = new NostoOperationProduct($account);
        $op->create();
    }

    /**
     * Tests that product create API requests can be made.
     */
    public function testSendingProductCreate()
    {
        $account = new NostoAccount('platform-00000000');
        $product = new NostoProduct();
		$token = new NostoApiToken('products', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$account->addApiToken($token);

        $op = new NostoOperationProduct($account);
        $op->addProduct($product);
        $result = $op->create();

        $this->specify('successful product create', function() use ($result) {
            $this->assertTrue($result);
        });
    }

    /**
     * Tests that product delete API requests cannot be made without an API token.
     */
    public function testSendingProductDeleteWithoutApiToken()
    {
        $account = new NostoAccount('platform-00000000');
        $product = new NostoProduct();

        $this->setExpectedException('NostoException');
        $op = new NostoOperationProduct($account);
        $op->addProduct($product);
        $op->delete();
    }


    /**
     * Tests that product delete API requests cannot be made without products.
     */
    public function testSendingProductDeleteWithoutProduct()
    {
        $account = new NostoAccount('platform-00000000');
		$token = new NostoApiToken('products', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$account->addApiToken($token);

        $this->setExpectedException('NostoException');
        $op = new NostoOperationProduct($account);
        $op->delete();
    }

    /**
     * Tests that product delete API requests can be made.
     */
    public function testSendingProductDelete()
    {
        $account = new NostoAccount('platform-00000000');
        $product = new NostoProduct();
		$token = new NostoApiToken('products', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$account->addApiToken($token);

        $op = new NostoOperationProduct($account);
        $op->addProduct($product);
        $result = $op->delete();

        $this->specify('successful product delete', function() use ($result) {
            $this->assertTrue($result);
        });
    }
}
