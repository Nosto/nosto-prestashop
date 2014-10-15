<div class="row">
    <form class="form-horizontal nostotagging" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" novalidate="">
        <input type="hidden" id="nostotagging_current_language" name="nostotagging_current_language" value="{$nostotagging_current_language.id_lang}">
        <div class="panel">
            <div class="panel-heading">
                <div class="form-group form-group-lg">
                    <div class="col-xs-12 col-md-8">
                        <label class="col-sm-2  control-label" for="nostotagging_language">{l s='Edit different shop language:' mod='nostotagging'}</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="nostotagging_language">
                                {foreach from=$nostotagging_languages item=language}
                                    <option value="{$language.id_lang}" {if $language.id_lang == $nostotagging_current_language.id_lang}selected="selected"{/if}>{$language.name}</option>
                                {/foreach}
                            </select>
                        </div>
                    </div>
                    <div class="col-xs-6 col-md-4">
                        <a href="#" id="nostotagging_account_setup" style="color: #666;">{l s='Account setup' mod='nostotagging'} <i class="icon-cogs"></i></a>
                    </div>
                </div>
            </div>
            <div class="form-wrapper text-center">
                {if $nostotagging_account_authorized}
                    <div class="form-group" id="nostotagging_installed" style="{if !empty($iframe_url)}display: none;{/if}">
                        <h2>{l s='You have installed Nosto to your %s shop' mod='nostotagging' sprintf=[$nostotagging_current_language.name]}</h2>
                        <p>{l s='Your account ID is %s' mod='nostotagging' sprintf=[$nostotagging_account_name]}</p>
                        <p>{l s='If you want to change the account, you need to uninstall first' mod='nostotagging'}</p>
                        {if !empty($iframe_url)}<a id="nostotagging_back_to_iframe" class="btn btn-default" role="button">{l s='Back' mod='nostotagging'}</a>{/if}
                        <button type="submit" value="1" class="btn" style="color: white; background-color: rgb(217, 83, 79); border-color: rgb(217, 83, 79);" name="submit_nostotagging_reset_account">{l s='Uninstall Nosto' mod='nostotagging'}</button>
                    </div>
                    {if !empty($iframe_url)}
                        <iframe id="nostotagging_iframe" frameborder="0" scrolling="no" style="width:100%; height:1250px;" src="{$iframe_url}"></iframe>
                    {/if}
                {else}
                    <h2>{l s='Install Nosto to your %s shop' mod='nostotagging' sprintf=[$nostotagging_current_language.name]}</h2>
                    <p>{l s='Do you have an existing Nosto account?' mod='nostotagging'}</p>

                    <div class="form-group">
                        <div style="width: 160px; margin: auto;">
                        <span class="switch prestashop-switch fixed-width-lg">
                            <input type="radio" name="nostotagging_has_account" id="nostotagging_has_account_on" value="1" {if $nostotagging_has_account}checked="checked"{/if}>
                            <label for="nostotagging_has_account_on">{l s='Yes' mod='nostotagging'}</label>
                            <input type="radio" name="nostotagging_has_account" id="nostotagging_has_account_off" value="0" {if !$nostotagging_has_account}checked="checked"{/if}>
                            <label for="nostotagging_has_account_off">{l s='No' mod='nostotagging'}</label>
                            <a class="slide-button btn"></a>
                        </span>
                        </div>
                    </div>

                    <div class="form-group" id="nostotagging_existing_account_group" style="{if !$nostotagging_has_account}display:none;{/if}">
                        <button type="submit" value="1" class="btn" style="color: white; background-color: rgb(121, 189, 60); border-color: rgb(121, 189, 60);" name="submit_nostotagging_authorize_account">{l s='Connect to Nosto' mod='nostotagging'}</button>
                    </div>

                    <div class="form-group" id="nostotagging_new_account_group" style="{if $nostotagging_has_account}display:none;{/if}">
                        <label for="nostotagging_account_email" class="control-label">{l s='Email' mod='nostotagging'}</label>
                        <input type="text" name="nostotagging_account_email" id="nostotagging_account_email" value="{$nostotagging_account_email}" class="fixed-width-xxl" style="margin: auto;" size="40" required="required">
                        <p class="help-block">{l s='This email address will be used to activate your account, so please make sure it is in use.' mod='nostotagging'}</p>
                        <button type="submit" value="1" class="btn" style="color: white; background-color: rgb(121, 189, 60); border-color: rgb(121, 189, 60);" name="submit_nostotagging_new_account">{l s='Create new account' mod='nostotagging'}</button>
                        <p class="help-block">{l s='By creating a new account you agree to Nosto\'s %1$sTerms and Conditions%2$s.' sprintf=['<a href="http://www.nosto.com/terms" target="_blank">', '</a>']}</p>
                    </div>
                {/if}
            </div>
        </div>
    </form>
</div>
