{if isset($customer)}
	<div class="nosto_customer" style="display:none">
		<span class="first_name">{$customer->firstname|escape:'htmlall':'UTF-8'}</span>
		<span class="last_name">{$customer->lastname|escape:'htmlall':'UTF-8'}</span>
		<span class="email">{$customer->email|escape:'htmlall':'UTF-8'}</span>
	</div>
{/if}
