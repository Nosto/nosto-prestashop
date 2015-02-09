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

{if isset($nosto_product) && is_object($nosto_product)}
	<div class="nosto_product" style="display:none">
		<span class="url">{$nosto_product->getUrl()|escape:'htmlall':'UTF-8'}</span>
		<span class="product_id">{$nosto_product->getProductId()|escape:'htmlall':'UTF-8'}</span>
		<span class="name">{$nosto_product->getName()|escape:'htmlall':'UTF-8'}</span>
		{if $nosto_product->getImageUrl() neq ''}
			<span class="image_url">{$nosto_product->getImageUrl()|escape:'htmlall':'UTF-8'}</span>
		{/if}
		<span class="price">{$nosto_product->getPrice()|escape:'htmlall':'UTF-8'}</span>
        <span class="list_price">{$nosto_product->getListPrice()|escape:'htmlall':'UTF-8'}</span>
		<span class="price_currency_code">{$nosto_product->getCurrencyCode()|escape:'htmlall':'UTF-8'}</span>
		<span class="availability">{$nosto_product->getAvailability()|escape:'htmlall':'UTF-8'}</span>
		{foreach from=$nosto_product->getCategories() item=category}
			<span class="category">{$category|escape:'htmlall':'UTF-8'}</span>
		{/foreach}
		{if $nosto_product->getDescription() neq ''}
			<span class="description">{$nosto_product->getDescription()|escape:'htmlall':'UTF-8'}</span>
		{/if}
		{if $nosto_product->getBrand() neq ''}
			<span class="brand">{$nosto_product->getBrand()|escape:'htmlall':'UTF-8'}</span>
		{/if}
		{if $nosto_product->getDatePublished() neq ''}
			<span class="date_published">{$nosto_product->getDatePublished()|escape:'htmlall':'UTF-8'}</span>
		{/if}
		{foreach from=$nosto_product->getTags() item=tag}
            {if $tag neq ''}
            <span class="tag1">{$tag|escape:'htmlall':'UTF-8'}</span>
            {/if}
		{/foreach}
	</div>
    {if isset($nosto_category) && is_object($nosto_category)}
        <div class="nosto_category" style="display:none">{$nosto_category->category_string|escape:'htmlall':'UTF-8'}</div>
    {/if}
{/if}