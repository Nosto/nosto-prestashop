<?php

require_once(dirname(__FILE__) . '/../_support/NostoAccountMetaDataIframe.php');

class IframeAuthTest extends \Codeception\TestCase\Test
{
	use \Codeception\Specify;

    /**
     * @var \UnitTester
     */
    protected $tester;

	/**
	 * Tests that we can build an authenticated url for the config iframe.
	 */
	public function testIframeUrlAuthentication()
    {
		/** @var NostoAccount $account */
		$account = new NostoAccount('platform-00000000');
		$meta = new NostoAccountMetaDataIframe();

		$url = $account->getIframeUrl($meta);

		$this->specify('install iframe url was created', function() use ($url) {
			$baseUrl = isset($_ENV['NOSTO_WEB_HOOK_BASE_URL']) ? $_ENV['NOSTO_WEB_HOOK_BASE_URL'] : NostoHttpRequest::$baseUrl;
			$this->assertEquals($baseUrl.'/hub/platform/install?lang=en&ps_version=1.0.0&nt_version=1.0.0&product_pu=http%3A%2F%2Fmy.shop.com%2Fproducts%2Fproduct123%3Fnostodebug%3Dtrue&category_pu=http%3A%2F%2Fmy.shop.com%2Fproducts%2Fcategory123%3Fnostodebug%3Dtrue&search_pu=http%3A%2F%2Fmy.shop.com%2Fsearch%3Fquery%3Dred%3Fnostodebug%3Dtrue&cart_pu=http%3A%2F%2Fmy.shop.com%2Fcart%3Fnostodebug%3Dtrue&front_pu=http%3A%2F%2Fmy.shop.com%3Fnostodebug%3Dtrue&shop_lang=en&shop_name=Shop+Name&unique_id=123&fname=James&lname=Kirk&email=james.kirk%40example.com', $url);
		});

		$token = new NostoApiToken('sso', '01098d0fc84ded7c4226820d5d1207c69243cbb3637dc4bc2a216dafcf09d783');
		$account->addApiToken($token);

		$url = $account->getIframeUrl($meta);

		$this->specify('auth iframe url was created', function() use ($url) {
			$this->assertEquals('https://nosto.com/auth/sso/sso%2Bplatform-00000000@nostosolutions.com/xAd1RXcmTMuLINVYaIZJJg?r=%2Fhub%2Fplatform%2Fplatform-00000000%3Flang%3Den%26ps_version%3D1.0.0%26nt_version%3D1.0.0%26product_pu%3Dhttp%253A%252F%252Fmy.shop.com%252Fproducts%252Fproduct123%253Fnostodebug%253Dtrue%26category_pu%3Dhttp%253A%252F%252Fmy.shop.com%252Fproducts%252Fcategory123%253Fnostodebug%253Dtrue%26search_pu%3Dhttp%253A%252F%252Fmy.shop.com%252Fsearch%253Fquery%253Dred%253Fnostodebug%253Dtrue%26cart_pu%3Dhttp%253A%252F%252Fmy.shop.com%252Fcart%253Fnostodebug%253Dtrue%26front_pu%3Dhttp%253A%252F%252Fmy.shop.com%253Fnostodebug%253Dtrue%26shop_lang%3Den%26shop_name%3DShop%2BName%26unique_id%3D123%26fname%3DJames%26lname%3DKirk%26email%3Djames.kirk%2540example.com', $url);
		});
    }
}
