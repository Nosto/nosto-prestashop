{*
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
*  @author Nosto Solutions Ltd <contact@nosto.com>
*  @copyright  2013-2014 Nosto Solutions Ltd
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if isset($nosto_order) && is_object($nosto_order)}
	<div class="nosto_purchase_order" style="display:none">
		<span class="order_number">{$nosto_order->order_number}</span>
		<div class="buyer">
			<span class="first_name">{$nosto_order->buyer.first_name|escape:'htmlall':'UTF-8'}</span>
			<span class="last_name">{$nosto_order->buyer.last_name|escape:'htmlall':'UTF-8'}</span>
			<span class="email">{$nosto_order->buyer.email|escape:'htmlall':'UTF-8'}</span>
		</div>
		<div class="purchased_items">
			{foreach from=$nosto_order->purchased_items item=item}
				<div class="line_item">
					<span class="product_id">{$item.product_id}</span>
					<span class="quantity">{$item.quantity}</span>
					<span class="name">{$item.name|escape:'htmlall':'UTF-8'}</span>
					<span class="unit_price">{$item.unit_price}</span>
					<span class="price_currency_code">{$item.price_currency_code}</span>
				</div>
			{/foreach}
		</div>
	</div>
{/if}
