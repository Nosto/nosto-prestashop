<?php

class ApiTokenTest extends \Codeception\TestCase\Test
{
    use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

    /**
     * Tests that a valid token can be created.
     */
    public function testTokenFormat()
    {
        $token = new NostoApiToken('sso', '123');

        $this->specify('api token is of valid format', function() use ($token) {
                $this->assertEquals('sso', $token->getName());
                $this->assertEquals('123', $token->getValue());
            });
    }

    /**
     * Tests that a invalid token cannot be created.
     */
    public function testMissingTokenName()
    {
        $this->specify('api token name is missing', function() {
            $this->setExpectedException('NostoInvalidArgumentException');
            new NostoApiToken(null, '123');
        });
    }

    /**
     * Tests that a invalid token cannot be created.
     */
    public function testMissingTokenValue()
    {
        $this->specify('api token value is missing', function() {
            $this->setExpectedException('NostoInvalidArgumentException');
            new NostoApiToken('sso', null);
        });
    }

    /**
     * Tests that a invalid token cannot be created.
     */
    public function testInvalidTokenName()
    {
        $this->specify('api token name is invalid', function() {
            $this->setExpectedException('NostoInvalidArgumentException');
            new NostoApiToken('foo', '123');
        });
    }

    /**
     * Tests that a invalid token cannot be created.
     */
    public function testInvalidTokenValue()
    {

        $this->specify('api token value is invalid', function() {
            $this->setExpectedException('NostoInvalidArgumentException');
            new NostoApiToken('sso', 123);
        });
    }

    /**
     * Tests that all valid token names are return by the static getter "getApiTokenNames".
     */
    public function testTokenNames()
    {
        $names = NostoApiToken::getApiTokenNames();

        $this->specify('api token names are valid', function() use ($names) {
            foreach (array('sso', 'products', 'settings', 'rates') as $validName) {
                $this->assertContains($validName, $names);
            }
        });
    }
}
