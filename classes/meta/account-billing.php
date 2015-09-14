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
 * Meta data class for account billing related information needed when creating new accounts.
 */
class NostoTaggingMetaAccountBilling implements NostoAccountMetaBillingInterface
{
	/**
	 * @var NostoCountryCode country ISO (ISO 3166-1 alpha-2) code for billing details.
	 */
	protected $country;

	/**
	 * Loads the meta data from the given context.
	 *
	 * @param Context $context the context to use as data source.
	 */
	public function loadData($context)
	{
		$this->country = new NostoCountryCode($context->country->iso_code);
	}

	/**
	 * The 2-letter ISO code (ISO 3166-1 alpha-2) for the country used in account's billing details.
	 *
	 * @return NostoCountryCode the country code.
	 */
	public function getCountry()
	{
		return $this->country;
	}
}
