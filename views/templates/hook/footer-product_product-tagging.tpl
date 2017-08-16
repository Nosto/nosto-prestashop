{*
* 2013-2016 Nosto Solutions Ltd
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
* @copyright 2013-2016 Nosto Solutions Ltd
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
		<span class="price_currency_code">{$nosto_product->getPriceCurrencyCode()|escape:'htmlall':'UTF-8'}</span>
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
		{if $nosto_product->getTag1()|is_array}
			{foreach from=$nosto_product->getTag1() item=tagValue}
				<span class="tag1">{$tagValue|escape:'htmlall':'UTF-8'}</span>
			{/foreach}
		{/if}
        {if $nosto_product->getTag2()|is_array}
            {foreach from=$nosto_product->getTag2() item=tagValue}
				<span class="tag1">{$tagValue|escape:'htmlall':'UTF-8'}</span>
            {/foreach}
        {/if}
        {if $nosto_product->getTag3()|is_array}
            {foreach from=$nosto_product->getTag3() item=tagValue}
				<span class="tag1">{$tagValue|escape:'htmlall':'UTF-8'}</span>
            {/foreach}
        {/if}
		{if $nosto_product->getVariationId()}
			<span class="variation_id">{$nosto_product->getVariationId()|escape:'htmlall':'UTF-8'}</span>
		{/if}
		{foreach from=$nosto_product->getAlternateImageUrls() key=index item=imageUrl}
			<span class="alternate_image_url">{$imageUrl|escape:'htmlall':'UTF-8'}</span>
		{/foreach}
		{if $nosto_product->getReviewCount() neq ''}
			<span class="review_count">{$nosto_product->getReviewCount()|escape:'htmlall':'UTF-8'}</span>
		{/if}
		{if $nosto_product->getRatingValue() neq ''}
			<span class="rating_value">{$nosto_product->getRatingValue()|escape:'htmlall':'UTF-8'}</span>
		{/if}
		{foreach from=$nosto_product->getSkus() item=sku}
		<span class="nosto_sku">
                <span class="id">{$sku->getId()|escape:'htmlall':'UTF-8'}</span>
                <span class="name">{$sku->getName()|escape:'htmlall':'UTF-8'}</span>
                <span class="price">{$sku->getPrice()|escape:'htmlall':'UTF-8'}</span>
                <span class="list_price">{$sku->getListPrice()|escape:'htmlall':'UTF-8'}</span>
                <span class="url">{$sku->getUrl()|escape:'htmlall':'UTF-8'}</span>
                <span class="image_url">{$sku->getImageUrl()|escape:'htmlall':'UTF-8'}</span>
                <span class="gtin">{$sku->getGtin()|escape:'htmlall':'UTF-8'}</span>
                <span class="availability">{$sku->getAvailability()|escape:'htmlall':'UTF-8'}</span>
                <span class="custom_fields">
                {if is_array($sku->getCustomFields())}
                    {foreach from=$sku->getCustomFields() key=key item=val}
                        <span class="{$key|escape:'htmlall':'UTF-8'}">{$val|escape:'htmlall':'UTF-8'}</span>
					{/foreach}
                {/if}
                </span>
            </span>
        {/foreach}
    </div>
	{if isset($nosto_category) && is_object($nosto_category)}
		<div class="nosto_category" style="display:none">{$nosto_category->getCategory()|escape:'htmlall':'UTF-8'}</div>
	{/if}

{/if}