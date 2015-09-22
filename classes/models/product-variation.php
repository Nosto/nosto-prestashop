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
 * Model for tagging products variations.
 */
class NostoTaggingProductVariation implements NostoProductPriceVariationInterface
{
	/**
	 * @var NostoPriceVariation the variation ID.
	 */
	protected $id;

	/**
	 * @var NostoCurrencyCode the currency code (SIO 4217) for the price variation.
	 */
	protected $currency;

	/**
	 * @var NostoPrice the price of the variation including possible discounts and taxes.
	 */
	protected $price;

	/**
	 * @var NostoPrice the list price of the variation without discounts but incl taxes.
	 */
	protected $list_price;

	/**
	 * @var NostoProductAvailability the availability of the price variation, i.e. if it is in stock or not.
	 */
	protected $availability;

	/**
	 * Constructor.
	 *
	 * Sets up this value object.
	 *
	 * @param NostoPriceVariation $id the product variation ID.
	 * @param NostoCurrencyCode $currency the product ISO 4217 currency code.
	 * @param NostoPrice $price the product price.
	 * @param NostoPrice $list_price the product list price.
	 * @param NostoProductAvailability $availability the product availability.
	 */
	public function __construct(NostoPriceVariation $id, NostoCurrencyCode $currency, NostoPrice $price, NostoPrice $list_price, NostoProductAvailability $availability)
	{
		$this->id = $id;
		$this->currency = $currency;
		$this->price = $price;
		$this->list_price = $list_price;
		$this->availability = $availability;
	}

	/**
	 * Returns the price variation ID.
	 *
	 * @return NostoPriceVariation the variation ID.
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Returns the currency code (SIO 4217) for the price variation.
	 *
	 * @return NostoCurrencyCode the price currency code.
	 */
	public function getCurrency()
	{
		return $this->currency;
	}

	/**
	 * Returns the price of the variation including possible discounts and taxes.
	 *
	 * @return NostoPrice the price.
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * Returns the list price of the variation without discounts but incl taxes.
	 *
	 * @return NostoPrice the price.
	 */
	public function getListPrice()
	{
		return $this->list_price;
	}

	/**
	 * Returns the availability of the price variation, i.e. if it is in stock or not.
	 *
	 * @return NostoProductAvailability the availability.
	 */
	public function getAvailability()
	{
		return $this->availability;
	}
}
