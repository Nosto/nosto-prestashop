<?php
/**
 * 2013-2015 Nosto Solutions Ltd
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
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2015 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
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
	 * @var int id of the record to fetch. If present only single record will be returned
	 */
	private $id;

	/**
	 * @var array ids of the records to be fetched. If present only records with these ids will be returned
	 * 	Ids can can be passed as an array or in a comma separated string.
	 *
	 * Examples:
	 * &ids=1,2,3...
	 * &ids[]=1&ids[]=2&ids[]=3

	 */
	private $ids = array();

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

		if (($id = Tools::getValue('id')) !== false && !empty($id))
			$this->id = $id;

		if (($ids = Tools::getValue('ids')) !== false && !empty($ids))
			$this->ids = $this->convertToArray($ids);
	}

	/**
	 * Encrypts and outputs the data and ends the application flow.
	 * Only send the response if we can encrypt it, i.e. we have an shared encryption secret with nosto.
	 *
	 * @param NostoExportCollectionInterface $collection the data collection to output as encrypted response.
	 */
	public function encryptOutput(NostoExportCollectionInterface $collection)
	{
		/** @var NostoAccount $account */
		$account = Nosto::helper('nosto_tagging/account')->find($this->module->getContext()->language->id);
		if ($account)
		{
			$cipher_text = NostoExporter::export($account, $collection);
			echo $cipher_text;
		}
		// It is important to stop the script execution after the export,
		// in order to avoid any additional data being outputted.
		die();
	}

	/**
	 * Convert a comma separated string into array
	 *
	 * @param mixed $ids
	 * @return array
	 */
	private function convertToArray($ids)
	{
		if (!is_array($ids)) {
			$ids = explode(',', $ids);
		}
		if (!is_array($ids)) {
			return array();
		}

		return array_unique($ids);
	}

	/**
	 * Returns the id parameter
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * Returns the orderIds parameter
	 * @return array
	 */
	public function getIds() {
		return $this->ids;
	}

	/**
	 * Sanitizes / escapes a value for sql
	 *
	 * @param mixed $value
	 * @return array
	 */
	public function sanitizeValue($value)
	{
		return pSQL($value);
	}
}
