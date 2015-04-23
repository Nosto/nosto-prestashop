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
	const IN_STOCK = 'InStock';
	const OUT_OF_STOCK = 'OutOfStock';
	const ADD_TO_CART = 'add-to-cart';
	const OPTIMAL_IMAGE_WIDTH = 450;

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
	 * @var string product price, discounted including vat.
	 */
	protected $price;

	/**
	 * @var string product list price, including vat.
	 */
	protected $list_price;

	/**
	 * @var string the currency iso code.
	 */
	protected $price_currency_code;

	/**
	 * @var string product availability (use constants).
	 */
	protected $availability;

	/**
	 * @var array list of product tags.
	 */
	protected $tags = array();

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
	 * @var string the product publish date.
	 */
	protected $date_published;

	/**
	 * @inheritdoc
	 */
	public function getRequiredItems()
	{
		return array(
			'url',
			'product_id',
			'name',
			'price',
			'list_price',
			'price_currency_code',
			'availability',
		);
	}

	/**
	 * Setter for the product url.
	 *
	 * @param string $url the url.
	 */
	public function setUrl($url)
	{
		$this->url = $url;
	}

	/**
	 * @inheritdoc
	 */
	public function getUrl()
	{
		return $this->url;
	}

	/**
	 * Setter for the unique product id.
	 *
	 * @param int $product_id the product id.
	 */
	public function setProductId($product_id)
	{
		$this->product_id = $product_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getProductId()
	{
		return $this->product_id;
	}

	/**
	 * @inheritdoc
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * @inheritdoc
	 */
	public function getImageUrl()
	{
		return $this->image_url;
	}

	/**
	 * @inheritdoc
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @inheritdoc
	 */
	public function getListPrice()
	{
		return $this->list_price;
	}

	/**
	 * @inheritdoc
	 */
	public function getCurrencyCode()
	{
		return $this->price_currency_code;
	}

	/**
	 * @inheritdoc
	 */
	public function getAvailability()
	{
		return $this->availability;
	}

	/**
	 * @inheritdoc
	 */
	public function getTags()
	{
		return $this->tags;
	}

	/**
	 * @inheritdoc
	 */
	public function getCategories()
	{
		return $this->categories;
	}

	/**
	 * @inheritdoc
	 */
	public function getShortDescription()
	{
		return $this->short_description;
	}

	/**
	 * @inheritdoc
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @inheritdoc
	 */
	public function getBrand()
	{
		return $this->brand;
	}

	/**
	 * @inheritdoc
	 */
	public function getDatePublished()
	{
		return $this->date_published;
	}

	/**
	 * Loads the product data from supplied context and product objects.
	 *
	 * @param Context $context the context object.
	 * @param Product $product the product object.
	 */
	public function loadData(Context $context, Product $product)
	{
		if (!Validate::isLoadedObject($product))
			return;

		$language_id = $context->language->id;
		$currency = $context->currency;
		$link = $context->link;
		$cookie = $context->cookie;

		$this->url = (string)$product->getLink();
		$this->product_id = (int)$product->id;
		$this->name = (string)$product->name;

		$image_id = $product->getCoverWs();
		$image_type = $this->chooseOptimalImageType();
		$this->image_url = (ctype_digit((string)$image_id) && !empty($image_type))
			? (string)$link->getImageLink($product->link_rewrite, $product->id.'-'.$image_id, $image_type)
			: '';

		// We need to check if taxes are to be included in the prices, given that they are configured.
		// This is determined by the "Price display method" setting of the active user group.
		// Possible values are 1, tax excluded, and 0, tax included.
		$price_display_method = Product::getTaxCalculationMethod((int)$cookie->id_customer);
		$price = $product->getPrice((bool)!$price_display_method, null);
		$list_price = $product->getPriceWithoutReduct((bool)$price_display_method, null);

		$this->price = Nosto::helper('price')->format($price);
		$this->price_currency_code = (string)$currency->iso_code;

		$is_visible = (_PS_VERSION_ >= '1.5') ? ($product->visibility !== 'none') : true;
		if ($product->checkQty(1) && $is_visible)
			$this->availability = self::IN_STOCK;
		else
			$this->availability = self::OUT_OF_STOCK;

		if (($tags = $product->getTags($language_id)) !== '')
			$this->tags = explode(', ', $tags);

		// If the product has no attributes (color, size etc.), then we mark it as possible to add directly to cart.
		$product_attributes = $product->getAttributesGroups($language_id);
		if (empty($product_attributes))
			$this->tags[] = self::ADD_TO_CART;

		foreach ($product->getCategories() as $category_id)
		{
			$category = NostoTaggingCategory::buildCategoryString($category_id, $language_id);
			if (!empty($category))
				$this->categories[] = (string)$category;
		}

		$this->short_description = (string)$product->description_short;
		$this->description = (string)$product->description;
		$this->list_price = Nosto::helper('price')->format($list_price);

		if (!empty($product->manufacturer_name))
			$this->brand = (string)$product->manufacturer_name;

		$this->date_published = Nosto::helper('date')->format($product->date_add);
	}

	/**
	 * Chooses the "optimal" image type to use for product image urls.
	 *
	 * The type is chosen based on which image type has a width closest to `self::OPTIMAL_IMAGE_WIDTH`.
	 *
	 * @return string|false the image type name or false if not found.
	 */
	protected function chooseOptimalImageType()
	{
		$definition = (_PS_VERSION_ >= '1.5') ? ObjectModel::getDefinition('ImageType') : array();
		$table_name = isset($definition['table']) ? $definition['table'] : 'image_type';
		$available_image_types = Db::getInstance()->executeS('
			SELECT * FROM `'._DB_PREFIX_.pSQL($table_name).'`
			WHERE `products` = 1
			ORDER BY `width` ASC
		');
		$optimal = self::OPTIMAL_IMAGE_WIDTH;
		$found = array();
		foreach ($available_image_types as $available)
			if (empty($found) || abs($optimal - (int)$found['width']) > abs((int)$available['width'] - $optimal))
				$found = $available;
		return isset($found['name']) ? $found['name'] : false;
	}
}
