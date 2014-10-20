{if isset($nosto_brand) && is_object($nosto_brand)}
    <div class="nosto_category" style="display:none">{$nosto_brand->brand_string|escape:'htmlall':'UTF-8'}</div>
{/if}
