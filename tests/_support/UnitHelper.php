<?php
namespace Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class UnitHelper extends \Codeception\Module
{
	/**
	 * @var \NostoTagging
	 */
	private $_nostotagging;

	/**
	 * Initializes the prestashop version being tested, i.e. loads the configurations.
	 * Also requires the nostotagging module file, in order to load all it's dependencies.
	 */
	public function initPs()
	{
		$ps_dir = $this->getPsDir();
		require_once($ps_dir.'/config/config.inc.php');
		require_once($ps_dir.'/modules/nostotagging/nostotagging.php');
		if (!defined('_PS_BASE_URL_'))
			define('_PS_BASE_URL_', \Tools::getShopDomain(true));
		if (!defined('_PS_BASE_URL_SSL_'))
			define('_PS_BASE_URL_SSL_', \Tools::getShopDomainSsl(true));
		$this->initContext();
		\NostoTaggingApiRequest::$base_url = $this->getApiBaseUrl();
		\NostoTaggingOAuth2Client::$base_url = $this->getOauthBaseUrl();
	}

	/**
	 * Initializes the context with required objects.
	 */
	public function initContext()
	{
		global $smarty;
		$context = $this->getContext();
		if (empty($context->controller))
			$context->controller = new \FrontController();
		if (!\Validate::isLoadedObject($context->language))
			$context->language = new \Language((int)\Configuration::get('PS_LANG_DEFAULT'));
		if (!\Validate::isLoadedObject($context->currency))
			$context->currency = new \Currency((int)\Configuration::get('PS_CURRENCY_DEFAULT'));
		if (!\Validate::isLoadedObject($context->country))
			$context->country = new \Country((int)\Configuration::get('PS_COUNTRY_DEFAULT'));
		$smarty = $context->smarty;
	}

	/**
	 * Returns the nostotagging module instance.
	 *
	 * @return \NostoTagging
	 */
	public function getNostoTagging()
	{
		if ($this->_nostotagging !== null)
			return $this->_nostotagging;
		return $this->_nostotagging = new \NostoTagging();
	}

	/**
	 * Returns the module context object.
	 *
	 * @return \Context
	 */
	public function getContext()
	{
		return $this->getNostoTagging()->getContext();
	}

	/**
	 * Returns the prestashop version being tested.
	 *
	 * @return string
	 */
	public function getPsVersion()
	{
		return isset($this->config['psVersion']) ? substr((string)$this->config['psVersion'], 0, 3) : '';
	}

	/**
	 * Returns the absolute path to the prestashop directory for the version being tested.
	 *
	 * @return string
	 */
	public function getPsDir()
	{
		return isset($this->config['psDir']) ? rtrim($this->config['psDir'], '/') : '';
	}

	/**
	 * Returns the nosto api base url that we can run test request against. defaults to localhost.
	 *
	 * @return string
	 */
	public function getApiBaseUrl()
	{
		return isset($this->config['apiBaseUrl']) ? $this->config['apiBaseUrl'] : 'http://localhost:9000/api';
	}

	/**
	 * Returns the nosto oauth endpoint base url that we can run test request against. defaults to localhost.
	 *
	 * @return string
	 */
	public function getOauthBaseUrl()
	{
		return isset($this->config['oauthBaseUrl']) ? $this->config['oauthBaseUrl'] : 'http://localhost:9000/oauth';
	}

	/**
	 * Creates a new employee and returns it.
	 *
	 * @return \Employee
	 */
	public function createEmployee()
	{
		$employee = new \Employee();
		$employee->firstname = 'dev';
		$employee->lastname = 'null';
		$employee->email = 'devnull@nosto.com';
		return $employee;
	}

	/**
	 * Loads product with id 1 and language id 1 form db and returns it.
	 *
	 * @return \Product
	 */
	public function createProduct()
	{
		return new \Product(1, true, 1);
	}

	/**
	 * Loads category with id 3 from db and returns it.
	 *
	 * @return \Category
	 */
	public function createCategory()
	{
		return new \Category(3);
	}

	/**
	 * Loads cart with id 1 from db and returns it.
	 *
	 * @return \Cart
	 */
	public function createCart()
	{
		return new \Cart(1);
	}

	/**
	 * Loads manufacturer with id 1 from db and returns it.
	 *
	 * @return \Manufacturer
	 */
	public function createManufacturer()
	{
		return new \Manufacturer(1);
	}

	/**
	 * Loads order with id 1 from db and returns it.
	 *
	 * @return \Order
	 */
	public function createOrder()
	{
		return new \Order(1);
	}
}