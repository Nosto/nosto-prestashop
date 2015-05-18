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
 * Helper class for sending product create/update/delete events to Nosto.
 */
class NostoTaggingHelperProductOperation
{
	/**
	 * Sends a product create API request to Nosto.
	 *
	 * @param Product $product the product that has been created.
	 */
	public function create(Product $product)
	{
		if (!Validate::isLoadedObject($product))
			return;

		foreach ($this->getAccountsPerLanguage() as $id_lang => $account)
		{
			$nosto_product = $this->loadNostoProduct((int)$product->id, $id_lang);
			if (is_null($nosto_product))
				continue;

			try
			{
				$op = new NostoOperationProduct($account);
				$op->addProduct($nosto_product);
				$op->create();
			}
			catch (NostoException $e)
			{
				Nosto::helper('nosto_tagging/logger')->error(
					__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(),
					$e->getCode(),
					get_class($product),
					(int)$product->id
				);
			}
		}
	}

	/**
	 * Sends a product update API request to Nosto.
	 *
	 * @param Product $product the product that has been updated.
	 */
	public function update(Product $product)
	{
		if (!Validate::isLoadedObject($product))
			return;

		foreach ($this->getAccountsPerLanguage() as $id_lang => $account)
		{
			$nosto_product = $this->loadNostoProduct((int)$product->id, $id_lang);
			if (is_null($nosto_product))
				continue;

			try
			{
				$op = new NostoOperationProduct($account);
				$op->addProduct($nosto_product);
				$op->update();
			}
			catch (NostoException $e)
			{
				Nosto::helper('nosto_tagging/logger')->error(
					__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(),
					$e->getCode(),
					get_class($product),
					(int)$product->id
				);
			}
		}
	}

	/**
	 * Sends a product delete API request to Nosto.
	 *
	 * @param Product $product the product that has been deleted.
	 */
	public function delete(Product $product)
	{
		if (!Validate::isLoadedObject($product))
			return;

		foreach ($this->getAccountsPerLanguage() as $account)
		{
			$nosto_product = new NostoTaggingProduct();
			$nosto_product->assignId($product);

			try
			{
				$op = new NostoOperationProduct($account);
				$op->addProduct($nosto_product);
				$op->update();
			}
			catch (NostoException $e)
			{
				Nosto::helper('nosto_tagging/logger')->error(
					__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(),
					$e->getCode(),
					get_class($product),
					(int)$product->id
				);
			}
		}
	}

	/**
	 * Returns Nosto accounts mapped by their language ID for current shop.
	 *
	 * @return NostoAccount[] the map.
	 */
	protected function getAccountsPerLanguage()
	{
		$accounts = array();
		foreach (Language::getLanguages() as $language)
		{
			$id_lang = (int)$language['id_lang'];
			/** @var NostoAccount $account */
			$account = Nosto::helper('nosto_tagging/account')->find($id_lang);
			if ($account === null || !$account->isConnectedToNosto())
				continue;

			$accounts[$id_lang] = $account;
		}
		return $accounts;
	}

	/**
	 * Loads a Nosto product model for given PS product ID and language.
	 *
	 * @param int $id_product the PS product ID.
	 * @param int $id_lang the language ID.
	 * @return NostoTaggingProduct|null the product or null if could not be loaded.
	 */
	protected function loadNostoProduct($id_product, $id_lang)
	{
		$product = new Product($id_product, true, $id_lang);
		if (!Validate::isLoadedObject($product))
			return null;

		if ($product->visibility === 'none')
			return null;

		$nosto_product = new NostoTaggingProduct();
		$nosto_product->loadData(Context::getContext(), $product);

		$validator = new NostoModelValidator();
		if (!$validator->validate($nosto_product))
			return null;

		return $nosto_product;
	}
}
