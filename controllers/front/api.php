<?php
/**
 * 2013-2014 Nosto Solutions Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@nosto.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 *  @author Nosto Solutions Ltd <contact@nosto.com>
 *  @copyright  2013-2014 Nosto Solutions Ltd
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

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
		$token = NostoTaggingApiToken::get('sso', $this->module->getContext()->language->id);
		if (!empty($token))
		{
			$secret = Tools::substr($token, 0, 16);
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