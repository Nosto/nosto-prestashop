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
		if (defined('_PS_VERSION_'))
			return;
		$ps_dir = $this->getPsDir();
		require($ps_dir.'/config/config.inc.php');
		require($ps_dir.'/modules/nostotagging/nostotagging.php');
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
		/** @var \NostoTagging $module */
		$module = $this->getNostoTagging();
		return $module->getContext();
	}

	/**
	 * Returns the prestashop version being tested.
	 *
	 * @return string
	 */
	public function getPsVersion()
	{
		return isset($this->config['psVersion']) ? substr((string)$this->config['psVersion'], 0, 3): '';
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
}