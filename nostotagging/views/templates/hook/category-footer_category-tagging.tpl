{if isset($nosto_category) && is_object($nosto_category)}
	<div class="nosto_category" style="display:none">{$nosto_category->category_string|escape:'htmlall':'UTF-8'}</div>
{/if}
