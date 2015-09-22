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
 * Model for creating snapshots of the the current PS context.
 */
class NostoTaggingContextSnapshot
{
	/**
	 * @var int the shop context.
	 */
	private $shop_context;

	/**
	 * @var Context the context model.
	 */
	private $context;

	/**
	 * Constructor.
	 *
	 * Creates a new snapshot of the current PS context.
	 */
	public function __construct()
	{
		$this->shop_context = (_PS_VERSION_ >= '1.5') ? ShopCore::getContext() : null;
		$this->context = Context::getContext()->cloneContext();
	}

	/**
	 * Restores the snapshot as the current PS context.
	 */
	public function restore()
	{
		$current_context = Context::getContext();
		$current_context->language = $this->context->language;
		$current_context->shop = $this->context->shop;
		$current_context->link = $this->context->link;
		$current_context->currency = $this->context->currency;

		if (_PS_VERSION_ >= '1.5')
		{
			Shop::setContext($this->shop_context, $current_context->shop->id);
			Dispatcher::$instance = null;
			if (method_exists('ShopUrl', 'resetMainDomainCache'))
				ShopUrl::resetMainDomainCache();
		}
	}
}
