<?php

class NostoTaggingApiModuleFrontController extends ModuleFrontController
{
	public $limit = 100000;
	public $offset = 0;

	public function __construct()
	{
		parent::__construct();

		if (($limit = Tools::getValue('limit')) !== false && !empty($limit))
			$this->limit = (int)$limit;

		if (($offset = Tools::getValue('offset')) !== false && !empty($offset))
			$this->offset = (int)$offset;
	}
} 