<?php

/**
 * Front controller base class for modules. This is a drop in replacement in prestashop 1.4 where this does not exist.
 */
abstract class ModuleFrontController extends FrontController
{
	/**
	 * @var Module the module instance.
	 */
	public $module;

	/**
	 * @inheritdoc
	 */
	public function init()
	{
		parent::init();
		$this->module = Module::getInstanceByName(Tools::getValue('module'));
		if (!$this->module->active)
			Tools::redirect('index.php');
		$this->initContent();
	}

	/**
	 * Initializes the content.
	 */
	abstract public function initContent();
}
