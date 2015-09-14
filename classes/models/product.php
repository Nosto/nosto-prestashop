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
 * Model for tagging products.
 */
class NostoTaggingProduct extends NostoTaggingModel implements NostoProductInterface
{
	const ADD_TO_CART = 'add-to-cart';

	/**
	 * @var string absolute url to the product page.
	 */
	protected $url;

	/**
	 * @var string product object id.
	 */
	protected $product_id;

	/**
	 * @var string product name.
	 */
	protected $name;

	/**
	 * @var string absolute url to the product image.
	 */
	protected $image_url;

	/**
	 * @var NostoPrice product price, discounted including vat.
	 */
	protected $price;

	/**
	 * @var NostoPrice product list price, including vat.
	 */
	protected $list_price;

	/**
	 * @var NostoCurrencyCode the currency iso code.
	 */
	protected $currency_code;

	/**
	 * @var NostoProductAvailability product availability (use constants).
	 */
	protected $availability;

	/**
	 * @var array list of product tags.
	 */
	protected $tags = array(
		'tag1' => array(),
		'tag2' => array(),
		'tag3' => array(),
	);

	/**
	 * @var array list of product category strings.
	 */
	protected $categories = array();

	/**
	 * @var string the product short description.
	 */
	protected $short_description;

	/**
	 * @var string the product description.
	 */
	protected $description;

	/**
	 * @var string the product brand name.
	 */
	protected $brand;

	/**
	 * @var NostoDate the product publish date.
	 */
	protected $date_published;

	/**
	 * @var NostoPriceVariation the price variation that is currently in use.
	 */
	protected $price_variation;

	/**
	 * @var NostoProductPriceVariationInterface[] all price variations.
	 */
	protected $price_variations;

	/**
	 * Loads the product data from supplied context and product objects.
	 *
	 * @param Context $context the context object.
	 * @param Product|ProductCore $product the product object.
	 */
	public function loadData(Context $context, Product $product)
	{
		if (!Validate::isLoadedObject($product))
			return;

		/** @var Language|LanguageCore $lang */
		$lang = $context->language;
		/** @var Shop|ShopCore $shop */
		$shop = $context->shop;

		/** @var NostoTaggingHelperCurrency $currency_helper */
		$currency_helper = Nosto::helper('nosto_tagging/currency');
		/** @var NostoTaggingHelperPrice $price_helper */
		$price_helper = Nosto::helper('nosto_tagging/price');
		/** @var NostoTaggingHelperUrl $url_helper */
		$url_helper = Nosto::helper('nosto_tagging/url');
		/** @var NostoTaggingHelperConfig $config_helper */
		$config_helper = Nosto::helper('nosto_tagging/config');

		$base_currency = $currency_helper->getBaseCurrency($context);
		$currencies = $currency_helper->getCurrencies($context);

		$this->url = $url_helper->getProductUrl($product, $lang->id, $shop->id);
		$this->image_url = $url_helper->getProductImageUrl($product);
		$this->product_id = (int)$product->id;
		$this->name = $product->name;
		$this->price = $price_helper->getProductPriceInclTax($product, $context, $base_currency);
		$this->list_price = $price_helper->getProductListPriceInclTax($product, $context, $base_currency);
		$this->currency_code = new NostoCurrencyCode($base_currency->iso_code);
		$this->availability = $this->checkAvailability($product);
		$this->tags = $this->buildTags($product, $lang->id);
		$this->categories = $this->buildCategories($product, $lang->id);
		$this->short_description = $product->description_short;
		$this->description = $product->description;
		$this->brand = (!empty($product->manufacturer_name)) ? $product->manufacturer_name : null;
		$this->date_published = new NostoDate(strtotime($product->date_add));

		if (count($currencies) > 1)
		{
			$this->price_variation = new NostoPriceVariation($base_currency->iso_code);
			if ($config_helper->isMultiCurrencyMethodPriceVariation($lang->id, $shop->id_shop_group, $shop->id))
			{
				$this->price_variations = $this->buildPriceVariations(
					$product,
					$context,
					$base_currency,
					$currencies
				);
			}
		}
	}

	/**
	 * Assigns the product ID from given product.
	 *
	 * This method exists in order to expose a public API to change the ID.
	 *
	 * @param Product|ProductCore $product the product object.
	 */
	public function assignId(Product $product)
	{
		$this->product_id = (int)$product->id;
	}

	/**
	 * Checks the availability of the product and returns the "availability constant".
	 *
	 * The product is considered available if it is visible in the shop and is in stock.
	 *
	 * @param Product|ProductCore $product the product model.
	 * @return string the value, i.e. self::IN_STOCK or self::OUT_OF_STOCK.
	 */
	protected function checkAvailability(Product $product)
	{
		$is_visible = (_PS_VERSION_ >= '1.5') ? ($product->visibility !== 'none') : true;
		$value = ($product->checkQty(1) && $is_visible)
			? NostoProductAvailability::IN_STOCK
			: NostoProductAvailability::OUT_OF_STOCK;
		return new NostoProductAvailability($value);
	}

	/**
	 * Builds the tag list for the product.
	 *
	 * Also includes the custom "add-to-cart" tag if the product can be added to the shopping cart directly without
	 * any action from the user, e.g. the product cannot have any variations or choices. This tag is then used in the
	 * recommendations to render the "Add to cart" button for the product when it is recommended to a user.
	 *
	 * @param Product|ProductCore $product the product model.
	 * @param int $id_lang for which language ID to fetch the product tags.
	 * @return array the built tags.
	 */
	protected function buildTags(Product $product, $id_lang)
	{
		$tags = array();
		if (($product_tags = $product->getTags($id_lang)) !== '')
			$tags = explode(', ', $product_tags);

		// If the product has no attributes (color, size etc.), then we mark it as possible to add directly to cart.
		$product_attributes = $product->getAttributesGroups($id_lang);
		if (empty($product_attributes))
			$tags[] = self::ADD_TO_CART;

		return $tags;
	}

	/**
	 * Builds the category paths the product belongs to and returns them.
	 *
	 * By "path" we mean the full tree path of the products categories and sub-categories.
	 *
	 * @param Product|ProductCore $product the product model.
	 * @param int $id_lang for which language ID to fetch the categories.
	 * @return array the built category paths.
	 */
	protected function buildCategories(Product $product, $id_lang)
	{
		$categories = array();
		foreach ($product->getCategories() as $category_id)
		{
			$category = NostoTaggingCategory::buildCategoryString($category_id, $id_lang);
			if (!empty($category))
				$categories[] = $category;
		}
		return $categories;
	}

	/**
	 * Builds all product price variations for the currencies.
	 *
	 * @param Product|ProductCore $product the product model.
	 * @param Context|ContextCore $context the context model.
	 * @param Currency|CurrencyCore $base_currency the base currency model.
	 * @param array $currencies
	 * @return NostoProductPriceVariationInterface[] all price variations.
	 */
	protected function buildPriceVariations(Product $product, Context $context, Currency $base_currency, array $currencies)
	{
		$variations = array();
		foreach ($currencies as $currency)
		{
			if ($base_currency->iso_code === $currency['iso_code'])
				continue;

			$currency_model = new Currency($currency['id_currency']);
			$variation = new NostoTaggingProductVariation();
			$variation->loadData($product, $context, $currency_model, $this->availability);
			$variations[] = $variation;
		}
		return $variations;
	}

	/**
	 * Returns the absolute url to the product page in the shop frontend.
	 *
	 * @return string the url.
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Returns the product's unique identifier.
	 *
	 * @return int|string the ID.
	 */
	public function getProductId()
	{
		return $this->product_id;
	}

	/**
	 * Returns the name of the product.
	 *
	 * @return string the name.
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Returns the absolute url the one of the product images in the shop frontend.
	 *
	 * @return string the url.
	 */
	public function getImageUrl()
	{
		return $this->image_url;
	}

	/**
	 * Returns the absolute url to one of the product image thumbnails in the shop frontend.
	 *
	 * @return string the url.
	 */
	public function getThumbUrl()
	{
		return null;
	}

	/**
	 * Returns the price of the product including possible discounts and taxes.
	 *
	 * @return NostoPrice the price.
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * Returns the list price of the product without discounts but including possible taxes.
	 *
	 * @return NostoPrice the price.
	 */
	public function getListPrice()
	{
		return $this->list_price;
	}

	/**
	 * Returns the currency code (ISO 4217) the product is sold in.
	 *
	 * @return NostoCurrencyCode the currency code.
	 */
	public function getCurrency()
	{
		return $this->currency_code;
	}

	/**
	 * Returns the ID of the price variation that is currently in use.
	 *
	 * @return string the price variation ID.
	 */
	public function getPriceVariationId()
	{
		return (!is_null($this->price_variation))
			? $this->price_variation->getId()
			: null;
	}

	/**
	 * Returns the availability of the product, i.e. if it is in stock or not.
	 *
	 * @return NostoProductAvailability the availability.
	 */
	public function getAvailability()
	{
		return $this->availability;
	}

	/**
	 * Returns the tags for the product.
	 *
	 * @return array the tags array, e.g. array('tag1' => array("winter", "shoe")).
	 */
	public function getTags()
	{
		return $this->tags;
	}

	/**
	 * Returns the categories the product is located in.
	 *
	 * @return array list of category strings, e.g. array("/shoes/winter", "shoes/boots").
	 */
	public function getCategories()
	{
		return $this->categories;
	}

	/**
	 * Returns the product short description.
	 *
	 * @return string the short description.
	 */
	public function getShortDescription()
	{
		return $this->short_description;
	}

	/**
	 * Returns the product description.
	 *
	 * @return string the description.
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * Returns the full product description,
	 * i.e. both the "short" and "normal" descriptions concatenated.
	 *
	 * @return string the full descriptions.
	 */
	public function getFullDescription()
	{
		$descriptions = array();
		if (!empty($this->short_description)) {
			$descriptions[] = $this->short_description;
		}
		if (!empty($this->description)) {
			$descriptions[] = $this->description;
		}
		return implode(' ', $descriptions);
	}

	/**
	 * Returns the product brand name.
	 *
	 * @return string the brand name.
	 */
	public function getBrand()
	{
		return $this->brand;
	}

	/**
	 * Returns the product publication date in the shop.
	 *
	 * @return NostoDate the date.
	 */
	public function getDatePublished()
	{
		return $this->date_published;
	}

	/**
	 * Returns the product price variations if any exist.
	 *
	 * @return NostoProductPriceVariationInterface[] the price variations.
	 */
	public function getPriceVariations()
	{
		return $this->price_variations;
	}
}
