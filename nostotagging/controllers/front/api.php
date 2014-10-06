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

	/**
	 * Encrypts and outputs the string and ends the application flow.
	 *
	 * @param string $string the data to output as encrypted response.
	 */
	public function encryptOutput($string)
	{
		// todo: change this once we have a real secret transferred from Nosto.
		$token = $this->module->getSSOToken();
		if (!empty($token))
		{
			$secret = substr($token, 0, 16);
			if (!empty($secret))
			{
				$cipher = new NostoTaggingCipher($secret);
				$cipher_text = $cipher->encrypt($string);
				echo base64_encode($cipher_text);
			}
		}
		die;
	}
} 