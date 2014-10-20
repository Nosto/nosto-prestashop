<form id="configuration_form" class="defaultForm nostotagging" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data">
    <fieldset id="fieldset_0">
        <legend>{l s='General Settings' mod='nostotagging'}</legend>

        <label>{l s='Already have a Nosto account?' mod='nostotagging'}</label>
        <div class="margin-form">
            <input type="radio" name="nostotagging_has_account" id="nostotagging_has_account_on" value="1" {if $nostotagging_has_account}checked="checked"{/if}>
            <label class="t" for="nostotagging_defaults_on">
                <img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='nostotagging'}" title="{l s='Yes' mod='nostotagging'}">
            </label>
            <input type="radio" name="nostotagging_has_account" id="nostotagging_has_account_off" value="0" {if !$nostotagging_has_account}checked="checked"{/if}>
            <label class="t" for="nostotagging_defaults_off">
                <img src="../img/admin/disabled.gif" alt="{l s='No' mod='nostotagging'}" title="{l s='No' mod='nostotagging'}">
            </label>
        </div>
        <div class="clear"></div>

        <div id="nostotagging_account_name_group" style="{if !$nostotagging_has_account}display:none;{/if}">
            <div class="margin-form">
                {if $is_account_authorized === false}
                    <button type="submit" value="1" class="btn btn-default" name="submit_nostotagging_authorize_account">{l s='Authorize account' mod='nostotagging'}</button>
                    <p class="preference_description">{l s='You need to authorize your account in order to use all features provided by Nosto.' mod='nostotagging'}</p>
                {else}
                    <p class="preference_description">{l s='Your account is authorized.' mod='nostotagging'}</p>
                {/if}
            </div>
            <div class="clear"></div>
        </div>

        <div id="nostotagging_new_account_group" style="{if $nostotagging_has_account}display:none;{/if}">
            <label>{l s='Email' mod='nostotagging'}</label>
            <div class="margin-form">
                <input type="text" name="nostotagging_account_email" id="nostotagging_account_email" value="{$nostotagging_account_email}" size="40">
                <sup>*</sup>
                <p class="preference_description">{l s='This email address will be used to activate your account, so please make sure it is in use.' mod='nostotagging'}</p>
            </div>
            <div class="clear"></div>

            <div class="margin-form">
                <button type="submit" value="1" class="button" name="submit_nostotagging_new_account">{l s='Create new account' mod='nostotagging'}</button>
                <p class="preference_description">{l s='By creating a new account you agree to Nosto\'s %1$sTerms and Conditions%2$s.' sprintf=['<a href="http://www.nosto.com/terms" target="_blank">', '</a>']}</p>
            </div>
            <div class="clear"></div>
        </div>

        <div id="nostotagging_use_defaults_group" style="display:none;">
            <label>{l s='Use default nosto elements' mod='nostotagging'}</label>
            <div class="margin-form">
                <input type="radio" name="nostotagging_use_defaults" id="nostotagging_use_defaults_on" value="1" {if $nostotagging_use_defaults}checked="checked"{/if}>
                <label class="t" for="nostotagging_use_defaults_on">
                    <img src="../img/admin/enabled.gif" alt="{l s='Yes' mod='nostotagging'}" title="{l s='Yes' mod='nostotagging'}">
                </label>
                <input type="radio" name="nostotagging_use_defaults" id="nostotagging_use_defaults_off" value="0" {if !$nostotagging_use_defaults}checked="checked"{/if}>
                <label class="t" for="nostotagging_use_defaults_off">
                    <img src="../img/admin/disabled.gif" alt="{l s='No' mod='nostotagging'}" title="{l s='No' mod='nostotagging'}">
                </label>
            </div>
            <div class="clear"></div>
        </div>

        <div class="margin-form">
            <input type="submit" id="configuration_form_submit_btn" value="{l s='Save' mod='nostotagging'}" name="submit_nostotagging_general_settings" class="button">
        </div>
    </fieldset>
</form>