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
	 * Sets up this DTO.
	 *
	 * @param Product|ProductCore $product the PS product model.
	 * @param Context|null $context the PS context model.
	 */
	public function loadData(Product $product, Context $context = null)
	{
		if (!Validate::isLoadedObject($product))
			return;

		if (is_null($context))
			$context = Context::getContext();

		/** @var Language|LanguageCore $language */
		$language = $context->language;
		/** @var Shop|ShopCore $shop */
		$shop = $context->shop;

		/** @var NostoTaggingHelperCurrency $helper_currency */
		$helper_currency = Nosto::helper('nosto_tagging/currency');
		/** @var NostoTaggingHelperPrice $helper_price */
		$helper_price = Nosto::helper('nosto_tagging/price');
		/** @var NostoTaggingHelperUrl $helper_url */
		$helper_url = Nosto::helper('nosto_tagging/url');
		/** @var NostoTaggingHelperConfig $helper_config */
		$helper_config = Nosto::helper('nosto_tagging/config');

		$base_currency = $helper_currency->getBaseCurrency($context);
		$currencies = $helper_currency->getCurrencies($context);

		$this->url = $helper_url->getProductUrl($product, $language->id, $shop->id);
		$this->image_url = $helper_url->getProductImageUrl($product);
		$this->product_id = (int)$product->id;
		$this->name = $product->name;
		$this->price = $helper_price->getProductPriceInclTax($product, $context, $base_currency);
		$this->list_price = $helper_price->getProductListPriceInclTax($product, $context, $base_currency);
		$this->currency_code = new NostoCurrencyCode($base_currency->iso_code);
		$this->availability = $this->checkAvailability($product);
		$this->tags['tag1'] = $this->buildTags($product, $language);
		$this->categories = $this->buildCategories($product, $language);
		$this->short_description = $product->description_short;
		$this->description = $product->description;
		$this->date_published = new NostoDate(strtotime($product->date_add));
		$this->brand = (!empty($product->manufacturer_name)) ? $product->manufacturer_name : null;

		if (count($currencies) > 1)
		{
			$this->price_variation = new NostoPriceVariation($base_currency->iso_code);
			if ($helper_config->isMultiCurrencyMethodPriceVariation($language->id, $shop->id_shop_group, $shop->id))
				$this->price_variations = $this->buildPriceVariations($product, $context, $base_currency, $currencies);
		}

		// Execute hook `actionObjectNostoTaggingProductLoadAfter`, so that other modules can add/modify the product.
		// This is useful when wanting to add custom data, e.g. tag1, tag2, tag3, to the product that is automatically
		// included in both the tagging on the product pages and in the server-to-server API calls.
		if (_PS_VERSION_ >= '1.5')
			Hook::exec('actionObject'.get_class($this).'LoadAfter',
				array('nosto_product' => $this, 'product' => $product, 'context' => $context));
		else
			Module::hookExec('actionObject'.get_class($this).'LoadAfter',
				array('nosto_product' => $this, 'product' => $product, 'context' => $context));
	}

	/**
	 * Checks the availability of the product and returns it.
	 *
	 * The product is considered available if it is visible in the shop and is in stock.
	 *
	 * @param Product|ProductCore $product the PS product model.
	 * @return NostoProductAvailability the availability.
	 */
	protected function checkAvailability(Product $product)
	{
		$is_visible = (_PS_VERSION_ >= '1.5') ? ($product->visibility !== 'none') : true;
		return new NostoProductAvailability(($product->checkQty(1) && $is_visible)
			? NostoProductAvailability::IN_STOCK
			: NostoProductAvailability::OUT_OF_STOCK);
	}

	/**
	 * Builds the tag list for the product.
	 *
	 * Also includes the custom "add-to-cart" tag if the product can be added to the shopping cart directly without
	 * any action from the user, e.g. the product cannot have any variations or choices. This tag is then used in the
	 * recommendations to render the "Add to cart" button for the product when it is recommended to a user.
	 *
	 * @param Product|ProductCore $product the PS product model.
	 * @param Language|LanguageCore $language the PS language model.
	 * @return array the built tags.
	 */
	protected function buildTags(Product $product, Language $language)
	{
		$tags = array();
		if (($product_tags = $product->getTags($language->id)) !== '')
			$tags = explode(', ', $product_tags);

		// If the product has no attributes (color, size etc.), then we mark it as possible to add directly to cart.
		$product_attributes = $product->getAttributesGroups($language->id);
		if (empty($product_attributes))
			$tags[] = self::ADD_TO_CART;

		return $tags;
	}

	/**
	 * Builds the category paths the product belongs to and returns them.
	 *
	 * By "path" we mean the full tree path of the products categories and sub-categories.
	 *
	 * @param Product|ProductCore $product the PS product model.
	 * @param Language|LanguageCore $language the PS language model.
	 * @return array the built category paths.
	 */
	protected function buildCategories(Product $product, Language $language)
	{
		$categories = array();
		foreach ($product->getCategories() as $category_id)
		{
			$category = NostoTaggingCategory::buildCategoryString($category_id, $language->id);
			if (!empty($category))
				$categories[] = $category;
		}
		return $categories;
	}

	/**
	 * Builds all product price variations for the currencies.
	 *
	 * @param Product|ProductCore $product the PS product model.
	 * @param Context $context the PS context model.
	 * @param Currency|CurrencyCore $base_currency the PS currency model.
	 * @param array $currencies
	 * @return NostoProductPriceVariationInterface[] all price variations.
	 */
	protected function buildPriceVariations(Product $product, Context $context, Currency $base_currency, array $currencies)
	{
		/** @var NostoTaggingHelperPrice $helper_price */
		$helper_price = Nosto::helper('nosto_tagging/price');

		$variations = array();
		foreach ($currencies as $currency)
		{
			if ($base_currency->iso_code === $currency['iso_code'])
				continue;

			/** @var Currency|CurrencyCore $currency_model */
			$currency_model = new Currency($currency['id_currency']);
			$variation = new NostoTaggingProductVariation(new NostoPriceVariation($currency_model->iso_code),
				new NostoCurrencyCode($currency_model->iso_code),
				$helper_price->getProductPriceInclTax($product, $context, $currency_model),
				$helper_price->getProductListPriceInclTax($product, $context, $currency_model),
				$this->availability);
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
		if (!empty($this->short_description))
			$descriptions[] = $this->short_description;
		if (!empty($this->description))
			$descriptions[] = $this->description;
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

	/**
	 * Sets the product ID from given product.
	 *
	 * The product ID must be an integer above zero.
	 *
	 * Usage:
	 * $object->assignId(1);
	 *
	 * @param int $id the product ID.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setProductId($id)
	{
		if (!is_int($id) || !($id > 0))
			throw new InvalidArgumentException('ID must be an integer above zero.');

		$this->product_id = $id;
	}

	/**
	 * Sets the availability state of the product.
	 *
	 * The availability of the product must be either "InStock" or "OutOfStock", represented as a value object of class
	 * `NostoProductAvailability`.
	 *
	 * Usage:
	 * $object->setAvailability(new NostoProductAvailability(NostoProductAvailability::IN_STOCK));
	 *
	 * @param NostoProductAvailability $availability the availability.
	 */
	public function setAvailability(NostoProductAvailability $availability)
	{
		$this->availability = $availability;
	}

	/**
	 * Sets the currency code (ISO 4217) the product is sold in.
	 *
	 * The currency must be in ISO 4217 format, represented as a value object of class `NostoCurrencyCode`.
	 *
	 * Usage:
	 * $object->setCurrency(new NostoCurrencyCode('USD'));
	 *
	 * @param NostoCurrencyCode $currency the currency code.
	 */
	public function setCurrency(NostoCurrencyCode $currency)
	{
		$this->currency_code = $currency;
	}

	/**
	 * Sets the products published date.
	 *
	 * The date must be a UNIX timestamp, represented as a value object of class `NostoDate`.
	 *
	 * Usage:
	 * $object->setDatePublished(new NostoDate(strtotime('2015-01-01 00:00:00')));
	 *
	 * @param NostoDate $date the date.
	 */
	public function setDatePublished(NostoDate $date)
	{
		$this->date_published = $date;
	}

	/**
	 * Sets the product price.
	 *
	 * The price must be a numeric value, represented as a value object of class `NostoPrice`.
	 *
	 * Usage:
	 * $object->setPrice(new NostoPrice(99.99));
	 *
	 * @param NostoPrice $price the price.
	 */
	public function setPrice(NostoPrice $price)
	{
		$this->price = $price;
	}

	/**
	 * Sets the product list price.
	 *
	 * The price must be a numeric value, represented as a value object of class `NostoPrice`.
	 *
	 * Usage:
	 * $object->setListPrice(new NostoPrice(99.99));
	 *
	 * @param NostoPrice $list_price the price.
	 */
	public function setListPrice(NostoPrice $list_price)
	{
		$this->list_price = $list_price;
	}

	/**
	 * Sets the product price variation ID.
	 *
	 * The ID must be a non-empty string, represented as a value object of class `NostoPriceVariation`.
	 *
	 * Usage:
	 * $object->setPriceVariationId(new NostoPriceVariation('USD'));
	 *
	 * @param NostoPriceVariation $price_variation the price variation.
	 */
	public function setPriceVariationId(NostoPriceVariation $price_variation)
	{
		$this->price_variation = $price_variation;
	}

	/**
	 * Sets the product price variations.
	 *
	 * The variations represent the possible product prices in different currencies and must implement the
	 * `NostoProductPriceVariationInterface` interface.
	 * This is only used in multi currency environments when the multi currency method is set to "priceVariations".
	 *
	 * Usage:
	 * $object->setPriceVariations(array(NostoProductPriceVariationInterface $price_variation [, ... ]))
	 *
	 * @param NostoProductPriceVariationInterface[] $price_variations the price variations.
	 */
	public function setPriceVariations(array $price_variations)
	{
		$this->price_variations = array();
		foreach ($price_variations as $price_variation)
			$this->addPriceVariation($price_variation);
	}

	/**
	 * Adds a product price variation.
	 *
	 * The variation represents the product price in another currency than the base currency, and must implement the
	 * `NostoProductPriceVariationInterface` interface.
	 * This is only used in multi currency environments when the multi currency method is set to "priceVariations".
	 *
	 * Usage:
	 * $object->addPriceVariation(NostoProductPriceVariationInterface $price_variation);
	 *
	 * @param NostoProductPriceVariationInterface $price_variation the price variation.
	 */
	public function addPriceVariation(NostoProductPriceVariationInterface $price_variation)
	{
		$this->price_variations[] = $price_variation;
	}

	/**
	 * Sets all the tags to the `tag1` field.
	 *
	 * The tags must be an array of non-empty string values.
	 *
	 * Usage:
	 * $object->setTag1(array('customTag1', 'customTag2'));
	 *
	 * @param array $tags the tags.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setTag1(array $tags)
	{
		$this->tags['tag1'] = array();
		foreach ($tags as $tag)
			$this->addTag1($tag);
	}

	/**
	 * Adds a new tag to the `tag1` field.
	 *
	 * The tag must be a non-empty string value.
	 *
	 * Usage:
	 * $object->addTag1('customTag');
	 *
	 * @param string $tag the tag to add.
	 *
	 * @throws InvalidArgumentException
	 */
	public function addTag1($tag)
	{
		if (!is_string($tag) || empty($tag))
			throw new InvalidArgumentException('Tag must be a non-empty string value.');

		$this->tags['tag1'][] = $tag;
	}

	/**
	 * Sets all the tags to the `tag2` field.
	 *
	 * The tags must be an array of non-empty string values.
	 *
	 * Usage:
	 * $object->setTag2(array('customTag1', 'customTag2'));
	 *
	 * @param array $tags the tags.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setTag2(array $tags)
	{
		$this->tags['tag1'] = array();
		foreach ($tags as $tag)
			$this->addTag2($tag);
	}

	/**
	 * Adds a new tag to the `tag2` field.
	 *
	 * The tag must be a non-empty string value.
	 *
	 * Usage:
	 * $object->addTag2('customTag');
	 *
	 * @param string $tag the tag to add.
	 *
	 * @throws InvalidArgumentException
	 */
	public function addTag2($tag)
	{
		if (!is_string($tag) || empty($tag))
			throw new InvalidArgumentException('Tag must be a non-empty string value.');

		$this->tags['tag2'][] = $tag;
	}

	/**
	 * Sets all the tags to the `tag3` field.
	 *
	 * The tags must be an array of non-empty string values.
	 *
	 * Usage:
	 * $object->setTag3(array('customTag1', 'customTag2'));
	 *
	 * @param array $tags the tags.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setTag3(array $tags)
	{
		$this->tags['tag1'] = array();
		foreach ($tags as $tag)
			$this->addTag3($tag);
	}

	/**
	 * Adds a new tag to the `tag3` field.
	 *
	 * The tag must be a non-empty string value.
	 *
	 * Usage:
	 * $object->addTag3('customTag');
	 *
	 * @param string $tag the tag to add.
	 *
	 * @throws InvalidArgumentException
	 */
	public function addTag3($tag)
	{
		if (!is_string($tag) || empty($tag))
			throw new InvalidArgumentException('Tag must be a non-empty string value.');

		$this->tags['tag3'][] = $tag;
	}

	/**
	 * Sets the brand name of the product manufacturer.
	 *
	 * The name must be a non-empty string.
	 *
	 * Usage:
	 * $object->setBrand('Example');
	 *
	 * @param string $brand the brand name.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setBrand($brand)
	{
		if (!is_string($brand) || empty($brand))
			throw new InvalidArgumentException('Brand must be a non-empty string value.');

		$this->brand = $brand;
	}

	/**
	 * Sets the product categories.
	 *
	 * The categories must be an array of non-empty string values. The categories are expected to include the entire
	 * sub/parent category path, e.g. "clothes/winter/coats".
	 *
	 * Usage:
	 * $object->setCategories(array('clothes/winter/coats' [, ... ] ));
	 *
	 * @param array $categories the categories.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setCategories(array $categories)
	{
		$this->categories = array();
		foreach ($categories as $category)
			$this->addCategory($category);
	}

	/**
	 * Adds a category to the product.
	 *
	 * The category must be a non-empty string and is expected to include the entire sub/parent category path,
	 * e.g. "clothes/winter/coats".
	 *
	 * Usage:
	 * $object->addCategory('clothes/winter/coats');
	 *
	 * @param string $category the category.
	 *
	 * @throws InvalidArgumentException
	 */
	public function addCategory($category)
	{
		if (!is_string($category) || empty($category))
			throw new InvalidArgumentException('Category must be a non-empty string value.');

		$this->categories[] = $category;
	}

	/**
	 * Sets the product name.
	 *
	 * The name must be a non-empty string.
	 *
	 * Usage:
	 * $object->setName('Example');
	 *
	 * @param string $name the name.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setName($name)
	{
		if (!is_string($name) || empty($name))
			throw new InvalidArgumentException('Category must be a non-empty string value.');

		$this->name = $name;
	}

	/**
	 * Sets the URL for the product page in the shop frontend that shows this product.
	 *
	 * The URL must be absolute, i.e. must include the protocol http or https.
	 *
	 * Usage:
	 * $object->setUrl("http://my.shop.com/products/example.html");
	 *
	 * @param string $url the url.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setUrl($url)
	{
		if (!Validate::isUrl($url) || !Validate::isAbsoluteUrl($url))
			throw new InvalidArgumentException('URL must be valid and absolute.');

		$this->url = $url;
	}

	/**
	 * Sets the image URL for the product.
	 *
	 * The URL must be absolute, i.e. must include the protocol http or https.
	 *
	 * Usage:
	 * $object->setImageUrl("http://my.shop.com/media/example.jpg");
	 *
	 * @param string $image_url the url.
	 *
	 * @throws NostoInvalidArgumentException
	 */
	public function setImageUrl($image_url)
	{
		if (!Validate::isUrl($image_url) || !Validate::isAbsoluteUrl($image_url))
			throw new NostoInvalidArgumentException('Image URL must be valid and absolute.');

		$this->image_url = $image_url;
	}

	/**
	 * Sets the product description.
	 *
	 * The description must be a non-empty string.
	 *
	 * Usage:
	 * $object->setDescription('Lorem ipsum dolor sit amet, ludus possim ut ius, bonorum facilis mandamus nam ea. ... ');
	 *
	 * @param string $description the description.
	 *
	 * @throws NostoInvalidArgumentException
	 */
	public function setDescription($description)
	{
		if (!is_string($description) || empty($description))
			throw new InvalidArgumentException('Description must be a non-empty string value.');

		$this->description = $description;
	}

	/**
	 * Sets the product `short` description.
	 *
	 * The description must be a non-empty string.
	 *
	 * Usage:
	 * $object->setShortDescription('Lorem ipsum dolor sit amet, ludus possim ut ius.');
	 *
	 * @param string $short_description the `short` description.
	 *
	 * @throws InvalidArgumentException
	 */
	public function setShortDescription($short_description)
	{
		if (!is_string($short_description) || empty($short_description))
			throw new InvalidArgumentException('Short description must be a non-empty string value.');

		$this->short_description = $short_description;
	}
}
