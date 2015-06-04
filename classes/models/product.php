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
class NostoTaggingProduct extends NostoTaggingModel implements NostoProductInterface, NostoValidatableInterface
{
	const IN_STOCK = 'InStock';
	const OUT_OF_STOCK = 'OutOfStock';
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
	protected $currency_code;

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
	public function getValidationRules()
	{
		return array(
			array(
				array(
					'url',
					'product_id',
					'name',
					'image_url',
					'price',
					'list_price',
					'currency_code',
					'availability',
				),
				'required',
			)
		);
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

		/** @var NostoTaggingHelperUrl $url_helper */
		$url_helper = Nosto::helper('nosto_tagging/url');

		$id_lang = $context->language->id;
		$id_shop = $context->shop->id;
		$currency_iso_code = $context->currency->iso_code;

		$this->url = $url_helper->getProductUrl($product, $id_lang, $id_shop);
		$this->image_url = $url_helper->getProductImageUrl($product);
		$this->product_id = (int)$product->id;
		$this->name = $product->name;
		$this->price = $this->calcPrice($product, $context);
		$this->list_price = $this->calcPrice($product, $context, false /*no discounts*/);
		$this->currency_code = strtoupper($currency_iso_code);
		$this->availability = $this->checkAvailability($product);
		$this->tags = $this->buildTags($product, $id_lang);
		$this->categories = $this->buildCategories($product, $id_lang);
		$this->short_description = $product->description_short;
		$this->description = $product->description;
		$this->brand = (!empty($product->manufacturer_name)) ? $product->manufacturer_name : null;
		$this->date_published = Nosto::helper('date')->format($product->date_add);
	}

	/**
	 * Assigns the product ID from given product.
	 *
	 * This method exists in order to expose a public API to change the ID.
	 *
	 * @param Product $product the product object.
	 */
	public function assignId(Product $product)
	{
		$this->product_id = (int)$product->id;
	}

	/**
	 * Calculates the price (including tax if applicable) and returns it.
	 *
	 * We need to check if taxes are to be included in the prices, given that they are configured.
	 * This is determined by the "Price display method" setting of the active user group.
	 * Possible values are 1, tax excluded, and 0, tax included.
	 *
	 * @param Product $product the product model.
	 * @param Context $context the context to calculate the price on (currency conversion).
	 * @param bool $discounted_price if discounts should be applied.
	 * @return string the calculated price.
	 */
	protected function calcPrice(Product $product, Context $context, $discounted_price = true)
	{
		$incl_tax = (bool)!Product::getTaxCalculationMethod((int)$context->cookie->id_customer);
		$specific_price_output = null;
		$value = Product::getPriceStatic(
			(int)$product->id,
			$incl_tax,
			null, // $id_product_attribute
			6, // $decimals
			null, // $divisor
			false, // $only_reduction
			$discounted_price, // $user_reduction
			1, // $quantity
			false, // $force_associated_tax
			null, // $id_customer
			null, // $id_cart
			null, // $id_address
			$specific_price_output, // $specific_price_output
			true, // $with_eco_tax
			true, // $use_group_reduction
			$context,
			true // $use_customer_price
		);
		return Nosto::helper('price')->format($value);
	}

	/**
	 * Checks the availability of the product and returns the "availability constant".
	 *
	 * The product is considered available if it is visible in the shop and is in stock.
	 *
	 * @param Product $product the product model.
	 * @return string the value, i.e. self::IN_STOCK or self::OUT_OF_STOCK.
	 */
	protected function checkAvailability(Product $product)
	{
		$is_visible = (_PS_VERSION_ >= '1.5') ? ($product->visibility !== 'none') : true;
		return ($product->checkQty(1) && $is_visible) ? self::IN_STOCK : self::OUT_OF_STOCK;
	}

	/**
	 * Builds the tag list for the product.
	 *
	 * Also includes the custom "add-to-cart" tag if the product can be added to the shopping cart directly without
	 * any action from the user, e.g. the product cannot have any variations or choices. This tag is then used in the
	 * recommendations to render the "Add to cart" button for the product when it is recommended to a user.
	 *
	 * @param Product $product the product model.
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
	 * @param Product $product the product model.
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
	 * @inheritdoc
	 */
	public function getUrl()
	{
		return $this->url;
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
		return $this->currency_code;
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
}
