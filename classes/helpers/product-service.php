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
class NostoTaggingHelperProductService
{
	/**
	 * @var array runtime cache for products that have already been processed during this request to avoid sending the
	 * info to Nosto many times during the same request. This will otherwise happen as PrestaShop will sometime invoke
	 * the hook callback methods multiple times when saving a product.
	 */
	private static $processed_products = array();

	/**
	 * Sends a product create API request to Nosto.
	 *
	 * @param Product|ProductCore $product the product that has been created.
	 */
	public function create(Product $product)
	{
		if (!Validate::isLoadedObject($product) || in_array($product->id, self::$processed_products))
			return;

		self::$processed_products[] = $product->id;
		foreach ($this->getAccountData() as $data)
		{
			list($account, $id_shop, $id_lang) = $data;

			$nosto_product = $this->loadNostoProduct((int)$product->id, $id_lang, $id_shop);
			if (is_null($nosto_product))
				continue;

			try
			{
				$service = new NostoServiceProduct($account);
				$service->addProduct($nosto_product);
				$service->create();
			}
			catch (NostoException $e)
			{
				/** @var NostoTaggingHelperLogger $logger */
				$logger = Nosto::helper('nosto_tagging/logger');
				$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode(), get_class($product),
					(int)$product->id);
			}
		}
	}

	/**
	 * Sends a product update API request to Nosto.
	 *
	 * @param Product|ProductCore $product the product that has been updated.
	 */
	public function update(Product $product)
	{
		if (!Validate::isLoadedObject($product) || in_array($product->id, self::$processed_products))
			return;

		self::$processed_products[] = $product->id;
		foreach ($this->getAccountData() as $data)
		{
			list($account, $id_shop, $id_lang) = $data;

			$nosto_product = $this->loadNostoProduct((int)$product->id, $id_lang, $id_shop);
			if (is_null($nosto_product))
				continue;

			try
			{
				$service = new NostoServiceProduct($account);
				$service->addProduct($nosto_product);
				$service->update();
			}
			catch (NostoException $e)
			{
				/** @var NostoTaggingHelperLogger $logger */
				$logger = Nosto::helper('nosto_tagging/logger');
				$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode(), get_class($product),
					(int)$product->id);
			}
		}
	}

	/**
	 * Sends a product delete API request to Nosto.
	 *
	 * @param Product|ProductCore $product the product that has been deleted.
	 */
	public function delete(Product $product)
	{
		if (!Validate::isLoadedObject($product) || in_array($product->id, self::$processed_products))
			return;

		self::$processed_products[] = $product->id;
		foreach ($this->getAccountData() as $data)
		{
			list($account) = $data;

			try
			{
				$nosto_product = new NostoTaggingProduct();
				$nosto_product->setProductId((int)$product->id);
				$service = new NostoServiceProduct($account);
				$service->addProduct($nosto_product);
				$service->delete();
			}
			catch (NostoException $e)
			{
				/** @var NostoTaggingHelperLogger $logger */
				$logger = Nosto::helper('nosto_tagging/logger');
				$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode(), get_class($product),
					(int)$product->id);
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
		foreach ($this->getContextShops() as $shop)
		{
			$id_shop = (int)$shop['id_shop'];
			$id_shop_group = (int)$shop['id_shop_group'];
			foreach (LanguageCore::getLanguages(true, $id_shop) as $language)
			{
				$id_lang = (int)$language['id_lang'];
				$account = $account_helper->find($id_lang, $id_shop_group, $id_shop);
				if ($account === null)
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
	protected function getContextShops()
	{
		if (_PS_VERSION_ >= '1.5' && Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP)
		{
			if (Shop::getContext() === Shop::CONTEXT_GROUP)
				return Shop::getShops(true, Shop::getContextShopGroupID());
			else
				return Shop::getShops(true);
		}
		else
		{
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

		/** @var NostoTaggingHelperContextFactory $factory */
		$factory = Nosto::helper('nosto_tagging/context_factory');
		$snapshot = new NostoTaggingContextSnapshot();

		try {
			$nosto_product = new NostoTaggingProduct();
			$nosto_product->loadData($product, $factory->forgeContext($id_lang, $id_shop));
		} catch (NostoException $e) {
			$nosto_product = null;
			/** @var NostoTaggingHelperLogger $logger */
			$logger = Nosto::helper('nosto_tagging/logger');
			$logger->error(__CLASS__.'::'.__FUNCTION__.' - '.$e->getMessage(), $e->getCode(), get_class($product),
				(int)$product->id);
		}

		$snapshot->restore();

		return $nosto_product;
	}
}
