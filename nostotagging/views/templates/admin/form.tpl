{if $errors|@count gt 0}
<div class="error">
    <ul>
		{foreach from=$errors item=error}
            <li>{$error}</li>
		{/foreach}
    </ul>
</div>
{/if}

{if $messages|@count gt 0}
<div class="conf">
    <ul>
		{foreach from=$messages item=message}
            <li>{$message}</li>
		{/foreach}
    </ul>
</div>
{/if}

<h2>{l s='Nosto Tagging'}</h2>
<form action="{$form_action|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
    <fieldset>
        <legend>{l s='General Settings'}</legend>

        <label for="nosto-server-address">{l s='Server address'}</label>
        <div class="margin-form">
            <input id="nosto-server-address" type="text" name="nostotagging_server_address"
                   value="{$server_address|escape:'htmlall':'UTF-8'}"/>
            <sup>*</sup>
            <p class="preference_description">{l s='The server address for the Nosto marketing automation service.'}</p>
        </div>

        <label for="nosto-account-name">{l s='Account name'}</label>
        <div class="margin-form">
            <input id="nosto-account-name" type="text" name="nostotagging_account_name"
                   value="{$account_name|escape:'htmlall':'UTF-8'}"/>
			<sup>*</sup>
            <p class="preference_description">{l s='Your Nosto marketing automation service account name.'}</p>
        </div>

        <label>{l s='Default elements'}</label>
        <div class="margin-form">
            <input type="radio" id="nosto-default-elements-on" name="nostotagging_default_elements" value="1"/>
            <label class="t"  for="nosto-default-elements-on">
				<img src="../img/admin/enabled.gif" alt="Enabled" title="Enabled">
			</label>
            <input type="radio" id="nosto-default-elements-off" name="nostotagging_default_elements" value="0"/>
            <label class="t" for="nosto-default-elements-off">
                <img src="../img/admin/disabled.gif" alt="Disabled" title="Disabled">
            </label>
            <p class="preference_description">{l s='Use default placeholder elements for showing product recommendations.'}</p>
        </div>

        <div class="margin-form">
            <input class="button" type="submit" name="nostotagging_admin_submit" value="{l s='Save'}"/>
        </div>
    </fieldset>
</form>
