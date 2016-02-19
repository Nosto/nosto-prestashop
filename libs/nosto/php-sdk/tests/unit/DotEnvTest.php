<?php

class DotEnvTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

	/**
	 * Tests a .env file.
	 */
	public function testDotEnvFile()
    {
		$dotEnv = new NostoDotEnv();
		$dotEnv->init(__DIR__.'/../_support', '.env-test');

		$this->specify('dot-env variable TEST_VARIABLE assigned to $_ENV', function() {
			$this->assertArrayHasKey('TEST_VARIABLE', $_ENV);
			$this->assertEquals('test', $_ENV['TEST_VARIABLE']);
		});

		$this->specify('dot-env variable TEST_VARIABLE_QUOTED_VALUE assigned to $_ENV', function() {
			$this->assertArrayHasKey('TEST_VARIABLE_QUOTED_VALUE', $_ENV);
			$this->assertEquals('test', $_ENV['TEST_VARIABLE_QUOTED_VALUE']);
		});

		$this->specify('dot-env variable TEST_VARIABLE_NESTED assigned to $_ENV', function() {
			$this->assertArrayHasKey('TEST_VARIABLE_NESTED', $_ENV);
			$this->assertEquals('test/test', $_ENV['TEST_VARIABLE_NESTED']);
		});
    }
}
