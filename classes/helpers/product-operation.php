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
 *
 * @todo there are a couple of issues with "multi-shop" installation URL generations:
 *
 * 1. The product page urls will not respect the "friendly urls" if one shop has it enabled and the other one does not.
 * This will cause all urls to be either friendly or not, depending on what setting the first seen shop has. This seems
 * to be caused by the `Dispatcher`.`use_routes` property that is set upon instance construction and cached due to the
 * use of the Singleton pattern.
 *
 * 2. The product image urls will always point to the first seen shops url.
 * This is due to the internal shop url cache handling and the fact the context cannot be overridden when populating it.
 * The image urls also have an issue with the "friendly urls", which is due to the internal handling of the
 * `Link`.`allow` property that is tied to the current context and cannot be overridden.
 *
 * The "friendly urls" issue SHOULD NOT be a real issue, as it is very unlikely the "friendly urls" setting would be
 * enabled in one shop and disabled in another.
 *
 * The image url issue IS a real issue IF the product images are different in the shops. The image url will always point
 * to an existing image, but that image may or may not be visible on the product page for the shop in question.
 *
 * How all this affects Nosto:
 *
 * 1. The "friendly url" issue can cause the products displayed in the recommendations to be broken if the shop does not
 * have the setting enabled.
 *
 * 2. The product image displayed in the recommendation may not be visible on the product page it that shop.
 */
class NostoTaggingHelperProductOperation
{
	/**
	 * @var array runtime cache for products that have already been processed during this request to avoid sending the
	 * info to Nosto many times during the same request. This will otherwise happen as PrestaShop will sometime invoke
	 * the hook callback methods multiple times when saving a product.
	 */
	private static $_processed_products = array();

	/**
	 * Sends a product create API request to Nosto.
	 *
	 * @param Product $product the product that has been created.
	 */
	public function create(Product $product)
	{
		if (!Validate::isLoadedObject($product) || in_array($product->id, self::$_processed_products))
			return;

		self::$_processed_products[] = $product->id;
		foreach ($this->getAccountData() as $data)
		{
			list($account, $id_shop, $id_lang) = $data;

			$nosto_product = $this->loadNostoProduct((int)$product->id, $id_lang, $id_shop);
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
		if (!Validate::isLoadedObject($product) || in_array($product->id, self::$_processed_products))
			return;

		self::$_processed_products[] = $product->id;
		foreach ($this->getAccountData() as $data)
		{
			list($account, $id_shop, $id_lang) = $data;

			$nosto_product = $this->loadNostoProduct((int)$product->id, $id_lang, $id_shop);
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
		if (!Validate::isLoadedObject($product) || in_array($product->id, self::$_processed_products))
			return;

		self::$_processed_products[] = $product->id;
		foreach ($this->getAccountData() as $data)
		{
			list($account) = $data;

			$nosto_product = new NostoTaggingProduct();
			$nosto_product->assignId($product);

			try
			{
				$op = new NostoOperationProduct($account);
				$op->addProduct($nosto_product);
				$op->delete();
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
	 * Returns Nosto accounts based on active shops.
	 *
	 * The result is formatted as follows:
	 *
	 * array(
	 *   array(object(NostoAccount), int(id_shop), int(id_lang))
	 * )
	 *
	 * @return NostoAccount[] the account data.
	 */
	protected function getAccountData()
	{
		$data = array();
		/** @var NostoTaggingHelperAccount $account_helper */
		$account_helper = Nosto::helper('nosto_tagging/account');
		foreach ($this->getShops() as $shop) {
			$id_shop = (int)$shop['id_shop'];
			$id_shop_group = (int)$shop['id_shop_group'];
			foreach (LanguageCore::getLanguages(true, $id_shop) as $language)
			{
				$id_lang = (int)$language['id_lang'];
				$account = $account_helper->find($id_lang, $id_shop_group, $id_shop);
				if ($account === null || !$account->isConnectedToNosto())
					continue;

				$data[] = array($account, $id_shop, $id_lang);
			}
		}
		return $data;
	}

	/**
	 * Returns the shops that are affected by the current context.
	 *
	 * @return array list of shop data.
	 */
	protected function getShops()
	{
		if (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
			if (Shop::getContext() === Shop::CONTEXT_GROUP)
				return Shop::getShops(true, Shop::getContextShopGroupID());
			else
				return Shop::getShops(true);
		} else {
			$ctx = Context::getContext();
			return array(
				(int)$ctx->shop->id => array(
					'id_shop' => (int)$ctx->shop->id,
					'id_shop_group' => (int)$ctx->shop->id_shop_group,
				),
			);
		}
	}

	/**
	 * Loads a Nosto product model for given PS product ID, language ID and shop ID.
	 *
	 * @param int $id_product the PS product ID.
	 * @param int $id_lang the language ID.
	 * @param int $id_shop the shop ID.
	 * @return NostoTaggingProduct|null the product or null if could not be loaded.
	 */
	protected function loadNostoProduct($id_product, $id_lang, $id_shop)
	{
		$product = new Product($id_product, false, $id_lang, $id_shop);
		if (!Validate::isLoadedObject($product))
			return null;

		if (isset($product->visibility) && $product->visibility === 'none')
			return null;

		$nosto_product = new NostoTaggingProduct();
		$nosto_product->loadData($this->makeContext($id_lang, $id_shop), $product);

		$validator = new NostoModelValidator();
		if (!$validator->validate($nosto_product))
			return null;

		return $nosto_product;
	}

	/**
	 * Clones the current context and replace the info related to shop, language and currency.
	 *
	 * We need this when generating the product data for the different shops and languages.
	 * The currency will be the first found for the shop, but it defaults to the PS default currency
	 * if no shop specific one is found.
	 *
	 * @param int $id_lang the language ID to add to the new context.
	 * @param int $id_shop the shop ID to add to the new context.
	 * @return Context the new context.
	 */
	protected function makeContext($id_lang, $id_shop)
	{
		$ctx = Context::getContext()->cloneContext();
		$ctx->language = new Language($id_lang);
		$ctx->shop = new Shop($id_shop);
		$currency = null;
		if (_PS_VERSION_ >= '1.5')
			foreach (Currency::getCurrenciesByIdShop($id_shop) as $row)
				if ($row['deleted'] === '0' && $row['active'] === '1')
				{
					$currency = new Currency($row['id_currency']);
					break;
				}
		if (is_null($currency))
			$currency = Currency::getDefaultCurrency();
		$ctx->currency = $currency;
		return $ctx;
	}
}
