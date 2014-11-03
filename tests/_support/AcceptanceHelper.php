<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class AcceptanceHelper extends \Codeception\Module
{
	const TEST_USER_EMAIL = 'devnull@nosto.com';
	const TEST_USER_FIRST_NAME = 'dev';
	const TEST_USER_LAST_NAME = 'null';

	/**
	 * @inheritdoc
	 */
	protected $requiredFields = array('prestashop');

	/**
	 * Asserts the global recommendation slots that are put one very page.
	 *
	 * @param \AcceptanceTester $I
	 */
	public function seeGlobalSlots(\AcceptanceTester $I)
	{
		$I->seeElement('div', array('id' => 'nosto-page-top'));
		$I->seeElement('div', array('id' => 'nosto-page-footer'));
	}

	/**
	 * Adds a product to the cart.
	 *
	 * @param \AcceptanceTester $I
	 */
	public function addProductToCart(\AcceptanceTester $I)
	{
		$I->wantTo('add product to cart');
		$I->amOnPage($I->getProductPageUrl());
		$I->submitForm('#buy_block', array());
	}

	/**
	 * Creates a new user account and logs in.
	 *
	 * @param \AcceptanceTester $I
	 */
	public function createAccountAndLogin(\AcceptanceTester $I)
	{
		$I->amOnPage('en/my-account');
		$I->fillField('#email_create', self::TEST_USER_EMAIL);
		$I->submitForm('#create-account_form', array());
		$I->see('success');
		// todo: implement
	}

	/**
	 * Orders a product and goes to the order confirmation page.
	 *
	 * @param \AcceptanceTester $I
	 */
	public function orderProduct(\AcceptanceTester $I)
	{
		$I->addProductToCart($I);
		$I->amOnPage($I->getCartPageUrl());
		// todo: implement
	}

	/**
	 * Returns a product page url for the PS installation currently being tested.
	 *
	 * @return string
	 */
	public function getProductPageUrl()
	{
		switch ($this->getPrestashopVersion())
		{
			case 1.6:
				return 'en/tshirts/1-faded-short-sleeve-tshirts.html';

			case 1.5;
				return 'en/music-ipods/1-ipod-nano.html';

			case 1.4;
				return 'en/music-ipods/1-ipod-nano.html';

			default;
				return '';
		}
	}

	/**
	 * Returns a category page url for the PS installation currently being tested.
	 *
	 * @return string
	 */
	public function getCategoryPageUrl()
	{
		switch ($this->getPrestashopVersion())
		{
			case 1.6:
				return 'en/3-women';

			case 1.5;
				return 'en/3-music-ipods';

			case 1.4;
				return 'en/3-music-ipods';

			default;
				return '';
		}
	}

	/**
	 * Returns the search page url for the PS installation currently being tested.
	 *
	 * @return string
	 */
	public function getSearchPageUrl()
	{
		switch ($this->getPrestashopVersion())
		{
			case 1.6:
				return 'en/search?controller=search&search_query=nosto';

			case 1.5;
				return 'en/search?controller=search&search_query=nosto';

			case 1.4;
				return 'en/search.php?controller=search&search_query=nosto';

			default;
				return '';
		}
	}

	/**
	 * Returns the cart page url for the PS installation currently being tested.
	 *
	 * @return string
	 */
	public function getCartPageUrl()
	{
		switch ($this->getPrestashopVersion())
		{
			case 1.6:
				return 'en/order';

			case 1.5;
				return 'en/order';

			case 1.4;
				return 'en/order.php';

			default;
				return '';
		}
	}

	/**
	 * Returns the configured prestashop version.
	 *
	 * @return float|null
	 */
	public function getPrestashopVersion()
	{
		return isset($this->config['prestashop']) ? substr((string)$this->config['prestashop'], 0, 3): null;
	}
}