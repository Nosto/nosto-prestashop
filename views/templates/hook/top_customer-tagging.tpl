{if isset($nosto_customer) && is_object($nosto_customer)}
	<div class="nosto_customer" style="display:none">
		<span class="first_name">{$nosto_customer->first_name|escape:'htmlall':'UTF-8'}</span>
		<span class="last_name">{$nosto_customer->last_name|escape:'htmlall':'UTF-8'}</span>
		<span class="email">{$nosto_customer->email|escape:'htmlall':'UTF-8'}</span>
	</div>
{/if}
