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
    <div class="nosto_product" style="display: none">
        <span class="url">{$nosto_product->getUrl()|escape:'htmlall':'UTF-8'}</span>
        <span class="product_id">{$nosto_product->getProductId()|escape:'htmlall':'UTF-8'}</span>
        <span class="name">{$nosto_product->getName()|escape:'htmlall':'UTF-8'}</span>
        <span class="image_url">{$nosto_product->getImageUrl()|escape:'htmlall':'UTF-8'}</span>
        {if $nosto_product->getPrice()}
            <span class="price">{$nosto_product->getPrice()->getPrice()|number_format:2:'.':''|escape:'htmlall':'UTF-8'}</span>
        {/if}
        {if $nosto_product->getCurrency()}
            <span class="price_currency_code">{$nosto_product->getCurrency()->getCode()|escape:'htmlall':'UTF-8'}</span>
        {/if}
        {if $nosto_product->getAvailability()}
            <span class="availability">{$nosto_product->getAvailability()->getAvailability()|escape:'htmlall':'UTF-8'}</span>
        {/if}
        {foreach from=$nosto_product->getCategories() item=category}
            <span class="category">{$category|escape:'htmlall':'UTF-8'}</span>
        {/foreach}
        {if $nosto_product->getFullDescription()}
            <span class="description">{$nosto_product->getFullDescription()|escape:'htmlall':'UTF-8'}</span>
        {/if}
        {if $nosto_product->getListPrice()}
            <span class="list_price">{$nosto_product->getListPrice()->getPrice()|number_format:2:'.':''|escape:'htmlall':'UTF-8'}</span>
        {/if}
        {if $nosto_product->getBrand()}
            <span class="brand">{$nosto_product->getBrand()|escape:'htmlall':'UTF-8'}</span>
        {/if}
        {foreach from=$nosto_product->getTags() key=type item=tags}
            {foreach from=$tags item=tag}
                <span class="{$type|escape:'quotes'}">{$tag|escape:'htmlall':'UTF-8'}</span>
            {/foreach}
        {/foreach}
        {if $nosto_product->getDatePublished()}
            <span class="date_published">{$nosto_product->getDatePublished()->getTimestamp()|date_format:'%Y-%m-%d'|escape:'htmlall':'UTF-8'}</span>
        {/if}
        {if $nosto_product->getPriceVariationId()}
            <span class="variation_id">{$nosto_product->getPriceVariationId()|escape:'htmlall':'UTF-8'}</span>
            {foreach from=$nosto_product->getPriceVariations() item=variation}
                <div class="variation">
                    <span class="variation_id">{$variation->getId()->getId()|escape:'htmlall':'UTF-8'}</span>
                    <span class="price_currency_code">{$variation->getCurrency()->getCode()|escape:'htmlall':'UTF-8'}</span>
                    <span class="price">{$variation->getPrice()->getPrice()|number_format:2:'.':''|escape:'htmlall':'UTF-8'}</span>
                    <span class="list_price">{$variation->getListPrice()->getPrice()|number_format:2:'.':''|escape:'htmlall':'UTF-8'}</span>
                    <span class="availability">{$variation->getAvailability()->getAvailability()|escape:'htmlall':'UTF-8'}</span>
                </div>
            {/foreach}
        {/if}
    </div>
    {if isset($nosto_category) && is_object($nosto_category)}
        <div class="nosto_category" style="display:none">{$nosto_category->getCategory()|escape:'htmlall':'UTF-8'}</div>
    {/if}
{/if}
