<?php

/**
 * Base controller for all Nosto API front controllers.
 *
 * @property NostoTagging $module
 * @property Context $context
 */
abstract class NostoTaggingApiModuleFrontController extends ModuleFrontController
{
	/**
	 * @var int the amount of items to fetch.
	 */
	public $limit = 100;

	/**
	 * @var int the offset of items to fetch.
	 */
	public $offset = 0;

	/**
	 * @inheritdoc
	 */
	public function __construct()
	{
		parent::__construct();

		if (($limit = Tools::getValue('limit')) !== false && !empty($limit))
			$this->limit = (int)$limit;

		if (($offset = Tools::getValue('offset')) !== false && !empty($offset))
			$this->offset = (int)$offset;
	}
} 