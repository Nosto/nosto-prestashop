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
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2014 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Block for tagging products.
 */
class NostoTaggingProduct extends NostoTaggingBlock
{
	const IN_STOCK = 'InStock';
	const OUT_OF_STOCK = 'OutOfStock';

	const ADD_TO_CART = 'add-to-cart';

	/**
	 * @var string absolute url to the product page.
	 */
	public $url;

	/**
	 * @var string product object id.
	 */
	public $product_id;

	/**
	 * @var string product name.
	 */
	public $name;

	/**
	 * @var string absolute url to the product image.
	 */
	public $image_url;

	/**
	 * @var string product price, discounted including vat.
	 */
	public $price;

	/**
	 * @var string product list price, including vat.
	 */
	public $list_price;

	/**
	 * @var string the currency iso code.
	 */
	public $price_currency_code;

	/**
	 * @var string product availability (use constants).
	 */
	public $availability;

	/**
	 * @var array list of product tags.
	 */
	public $tags = array();

	/**
	 * @var array list of product category strings.
	 */
	public $categories = array();

	/**
	 * @var string the product description.
	 */
	public $description;

	/**
	 * @var string the product brand name.
	 */
	public $brand;

	/**
	 * @var string the product publish date.
	 */
	public $date_published;

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
	 * @inheritdoc
	 */
	public function populate()
	{
		$product = $this->object;
		if (!Validate::isLoadedObject($product))
			return;

		$language_id = $this->context->language->id;
		$currency = $this->context->currency;
		$link = $this->context->link;

		$this->url = (string)$product->getLink();
		$this->product_id = (int)$product->id;
		$this->name = (string)$product->name;

		$image_id = $product->getCoverWs();
		if (ctype_digit((string)$image_id))
		{
			$type = (_PS_VERSION_ >= '1.5') ? ImageType::getFormatedName('large') : 'large';
			$image_url = $link->getImageLink($product->link_rewrite, $product->id.'-'.$image_id, $type);
		}
		else
			$image_url = '';
		$this->image_url = (string)$image_url;

		$this->price = NostoTaggingFormatter::formatPrice($product->getPrice(true, null));
		$this->price_currency_code = (string)$currency->iso_code;

		if ($product->checkQty(1))
			$this->availability = self::IN_STOCK;
		else
			$this->availability = self::OUT_OF_STOCK;

		if (($tags = $product->getTags($language_id)) !== '')
			$this->tags = explode(', ', $tags);

		// If the product has no attributes (color, size etc.), then we mark it as possible to add directly to cart.
		$product_attributes = $product->getAttributesGroups($language_id);
		if (empty($product_attributes)) {
			$this->tags[] = self::ADD_TO_CART;
		}

		foreach ($product->getCategories() as $category_id)
		{
			$category = NostoTaggingCategory::buildCategoryString($category_id, $language_id);
			if (!empty($category))
				$this->categories[] = (string)$category;
		}

		$this->description = (string)$product->description;
		$this->list_price = NostoTaggingFormatter::formatPrice($product->getPriceWithoutReduct(false, null));

		if (!empty($product->manufacturer_name))
			$this->brand = (string)$product->manufacturer_name;

		$this->date_published = NostoTaggingFormatter::formatDate($product->date_add);
	}
}
