{if isset($nosto_order) && is_array($nosto_order)}
	<div class="nosto_purchase_order" style="display:none">
		<span class="order_number">{$nosto_order.order_number}</span>
		<div class="buyer">
			<span class="first_name">{$nosto_order.customer.first_name|escape:'htmlall':'UTF-8'}</span>
			<span class="last_name">{$nosto_order.customer.last_name|escape:'htmlall':'UTF-8'}</span>
			<span class="email">{$nosto_order.customer.email|escape:'htmlall':'UTF-8'}</span>
		</div>
		<div class="purchased_items">
			{foreach from=$nosto_order.purchased_items item=item}
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
