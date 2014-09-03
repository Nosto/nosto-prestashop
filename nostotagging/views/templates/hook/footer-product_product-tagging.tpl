{if isset($nosto_product) && is_array($nosto_product)}
	<div class="nosto_product" style="display:none">
		<span class="url">{$nosto_product.url|escape:'htmlall':'UTF-8'}</span>
		<span class="product_id">{$nosto_product.product_id}</span>
		<span class="name">{$nosto_product.name|escape:'htmlall':'UTF-8'}</span>
		{if $nosto_product.image_url neq ''}
			<span class="image_url">{$nosto_product.image_url|escape:'htmlall':'UTF-8'}</span>
		{/if}
		<span class="price">{$nosto_product.price}</span>
        <span class="list_price">{$nosto_product.list_price}</span>
		<span class="price_currency_code">{$nosto_product.price_currency_code}</span>
		<span class="availability">{$nosto_product.availability|escape:'htmlall':'UTF-8'}</span>
		{foreach from=$nosto_product.categories item=category}
			<span class="category">{$category|escape:'htmlall':'UTF-8'}</span>
		{/foreach}
		{if $nosto_product.description neq ''}
			<span class="description">{$nosto_product.description|escape:'htmlall':'UTF-8'}</span>
		{/if}
		{if $nosto_product.brand neq ''}
			<span class="brand">{$nosto_product.brand|escape:'htmlall':'UTF-8'}</span>
		{/if}
		{if $nosto_product.date_published neq ''}
			<span class="date_published">{$nosto_product.date_published}</span>
		{/if}
	</div>
{/if}
{if $nosto_product.current_category neq ''}
	<div class="nosto_category" style="display:none">{$nosto_product.current_category|escape:'htmlall':'UTF-8'}</div>
{/if}
