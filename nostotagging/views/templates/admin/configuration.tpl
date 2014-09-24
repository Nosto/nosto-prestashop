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
                <label class="control-label col-lg-3 required">{l s='Account name' mod='nostotagging'}</label>
                <div class="col-lg-9 ">
                    <input type="text" name="nostotagging_account_name" id="nostotagging_account_name" value="{$nostotagging_account_name}" class="fixed-width-xxl" size="40" required="required">
                    <p class="help-block">{l s='Your Nosto marketing automation service account name.' mod='nostotagging'}</p>
                </div>
            </div>
            <div class="form-group" id="nostotagging_new_account_group" style="{if $nostotagging_has_account}display:none;{/if}">
                <label class="control-label col-lg-3">{l s='New account' mod='nostotagging'}</label>
                <div class="col-lg-9">
                    <button type="submit" value="1" class="btn btn-default" name="submit_nostotagging_new_account">{l s='Create' mod='nostotagging'}</button>
                    <p class="help-block">By creating a new account you agree to Nosto's <a href="http://www.nosto.com/terms" target="_blank">Terms and Conditions.</a></p>
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