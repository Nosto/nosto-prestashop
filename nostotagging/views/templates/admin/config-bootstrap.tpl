<form class="form-horizontal nostotagging" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" novalidate="">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon-cogs"></i> {l s='General Settings' mod='nostotagging'}
        </div>
        <div class="form-wrapper">

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Already have a Nosto account?' mod='nostotagging'}</label>
                <div class="col-lg-9 ">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="nostotagging_has_account" id="nostotagging_has_account_on" value="1" {if $nostotagging_has_account}checked="checked"{/if}>
                        <label for="nostotagging_has_account_on">{l s='Yes' mod='nostotagging'}</label>
                        <input type="radio" name="nostotagging_has_account" id="nostotagging_has_account_off" value="0" {if !$nostotagging_has_account}checked="checked"{/if}>
                        <label for="nostotagging_has_account_off">{l s='No' mod='nostotagging'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>

            <div class="form-group" id="nostotagging_account_name_group" style="{if !$nostotagging_has_account}display:none;{/if}">
                <div class="col-lg-3">&nbsp;</div>
                <div class="col-lg-9 ">
                    {if $is_account_authorized === false}
                        <button type="submit" value="1" class="btn btn-default" name="submit_nostotagging_authorize_account">{l s='Connect to Nosto' mod='nostotagging'}</button>
                        <p class="help-block">{l s='In order to use all Nosto features you need to connect your account with Nosto.' mod='nostotagging'}</p>
                    {else}
                        <p class="help-block">{l s='Your account is connected to Nosto.' mod='nostotagging'}</p>
                    {/if}
                </div>
            </div>

            <div class="form-group" id="nostotagging_new_account_group" style="{if $nostotagging_has_account}display:none;{/if}">
                <label class="control-label col-lg-3 required">{l s='Email' mod='nostotagging'}</label>
                <div class="col-lg-9 ">
                    <input type="text" name="nostotagging_account_email" id="nostotagging_account_email" value="{$nostotagging_account_email}" class="fixed-width-xxl" size="40" required="required">
                    <p class="help-block">{l s='This email address will be used to activate your account, so please make sure it is in use.' mod='nostotagging'}</p>
                </div>
                <div class="col-lg-3">&nbsp;</div>
                <div class="col-lg-9">
                    <button type="submit" value="1" class="btn btn-default" name="submit_nostotagging_new_account">{l s='Create new account' mod='nostotagging'}</button>
                    <p class="help-block">{l s='By creating a new account you agree to Nosto\'s %1$sTerms and Conditions%2$s.' sprintf=['<a href="http://www.nosto.com/terms" target="_blank">', '</a>']}</p>
                </div>
            </div>

            <div class="form-group">
                <label class="control-label col-lg-3">{l s='Use default nosto elements' mod='nostotagging'}</label>
                <div class="col-lg-9 ">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="nostotagging_use_defaults" id="nostotagging_use_defaults_on" value="1" {if $nostotagging_use_defaults}checked="checked"{/if}>
                        <label for="nostotagging_use_defaults_on">{l s='Yes' mod='nostotagging'}</label>
                        <input type="radio" name="nostotagging_use_defaults" id="nostotagging_use_defaults_off" value="0" {if !$nostotagging_use_defaults}checked="checked"{/if}>
                        <label for="nostotagging_use_defaults_off">{l s='No' mod='nostotagging'}</label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>

        </div>
        <div class="panel-footer">
            <button type="submit" value="1" id="configuration_form_submit_btn" name="submit_nostotagging_general_settings" class="button btn btn-default pull-right">
                <i class="process-icon-save"></i> {l s='Save' mod='nostotagging'}
            </button>
        </div>
    </div>
</form>
{if !empty($iframe_url)}
<iframe id="nosto_recs" frameborder="0" style="width:100%; height:1250px;" src="{$iframe_url}"></iframe>
{/if}
