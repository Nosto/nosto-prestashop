{if isset($nosto_line_items) && is_array($nosto_line_items)}
	<div class="nosto_cart" style="display:none">
		{foreach from=$nosto_line_items item=line_item}
			<div class="line_item">
				<span class="product_id">{$line_item.product_id}</span>
				<span class="quantity">{$line_item.quantity}</span>
				<span class="name">{$line_item.name|escape:'htmlall':'UTF-8'}</span>
				<span class="unit_price">{$line_item.unit_price}</span>
				<span class="price_currency_code">{$line_item.price_currency_code}</span>
			</div>
		{/foreach}
	</div>
{/if}
