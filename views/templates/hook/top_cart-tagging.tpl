{*
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
*}

{if isset($nosto_cart) && is_object($nosto_cart)}
	<div class="nosto_cart" style="display:none">
		{foreach from=$nosto_cart->line_items item=line_item}
			<div class="line_item">
				<span class="product_id">{$line_item.product_id|escape:'htmlall':'UTF-8'}</span>
				<span class="quantity">{$line_item.quantity|escape:'htmlall':'UTF-8'}</span>
				<span class="name">{$line_item.name|escape:'htmlall':'UTF-8'}</span>
				<span class="unit_price">{$line_item.unit_price|escape:'htmlall':'UTF-8'}</span>
				<span class="price_currency_code">{$line_item.price_currency_code|escape:'htmlall':'UTF-8'}</span>
			</div>
		{/foreach}
	</div>
{/if}
