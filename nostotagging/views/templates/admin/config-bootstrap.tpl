<div class="tw-bs">
    <div class="container-fluid">
        <div class="row">
            <form class="nostotagging" role="form" action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" novalidate="">
                <input type="hidden" id="nostotagging_current_language" name="nostotagging_current_language" value="{$nostotagging_current_language.id_lang}">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <div class="col-md-8">
                            {if count($nostotagging_languages) > 1}
                                <label for="nostotagging_language">{l s='Manage accounts:' mod='nostotagging'}
                                    <select class="form-control" id="nostotagging_language">
                                        {foreach from=$nostotagging_languages item=language}
                                            <option value="{$language.id_lang}" {if $language.id_lang == $nostotagging_current_language.id_lang}selected="selected"{/if}>{$language.name}</option>
                                        {/foreach}
                                    </select>
                                </label>
                            {/if}
                        </div>
                        <div class="col-md-4 text-right">
                            {if $nostotagging_account_authorized}
                                <a href="#" id="nostotagging_account_setup">{l s='Account setup' mod='nostotagging'}
                                    <span class="glyphicon glyphicon-cog">&nbsp;</span>
                                </a>
                            {/if}
                        </div>
                    </div>
                    <div class="panel-body text-center">
                        {if $nostotagging_account_authorized}
                            <div id="nostotagging_installed" style="{if !empty($iframe_url)}display: none;{/if}">
                                <h2>{l s='You have installed Nosto to your %1$s shop' mod='nostotagging' sprintf=[$nostotagging_current_language.name]}</h2>
                                <p>{l s='Your account ID is %s' mod='nostotagging' sprintf=[$nostotagging_account_name]}</p>
                                <div class="panes">
                                    <p>{l s='If you want to change the account, you need to uninstall first' mod='nostotagging'}</p>
                                    {if !empty($iframe_url)}<a id="nostotagging_back_to_iframe" class="btn btn-default" role="button">{l s='Back' mod='nostotagging'}</a>{/if}
                                    <button type="submit" value="1" class="btn btn-red" name="submit_nostotagging_reset_account">{l s='Uninstall Nosto' mod='nostotagging'}</button>
                                </div>
                            </div>
                            {if !empty($iframe_url)}
                                <iframe id="nostotagging_iframe" frameborder="0" scrolling="no" src="{$iframe_url}"></iframe>
                            {/if}
                        {else}
                            <h2>{l s='Install Nosto to your' mod='nostotagging'} {$nostotagging_current_language.name} {l s='shop' mod='nostotagging'}</h2>
                            <p>{l s='Do you have an existing Nosto account?' mod='nostotagging'}</p>

                            <div class="form-group">
                                <div class="switch-container">
                                    <span class="switch prestashop-switch fixed-width-lg">
                                        <input type="radio" name="nostotagging_has_account" id="nostotagging_has_account_on" value="1" checked="checked">
                                        <label for="nostotagging_has_account_on">{l s='Yes' mod='nostotagging'}</label>
                                        <input type="radio" name="nostotagging_has_account" id="nostotagging_has_account_off" value="0">
                                        <label for="nostotagging_has_account_off">{l s='No' mod='nostotagging'}</label>
                                        <a class="slide-button btn"></a>
                                    </span>
                                </div>
                            </div>

                            <div class="panes">
                                <div id="nostotagging_existing_account_group">
                                    <button type="submit" value="1" class="btn btn-green" name="submit_nostotagging_authorize_account">{l s='Connect to Nosto' mod='nostotagging'}</button>
                                </div>
                                <div id="nostotagging_new_account_group" style="display:none;">
                                    <div class="form-group">
                                        <label for="nostotagging_account_email">{l s='Email' mod='nostotagging'}</label>
                                        <input type="text" name="nostotagging_account_email" class="form-control" id="nostotagging_account_email" value="{$nostotagging_account_email}">
                                    </div>
                                    <button type="submit" value="1" class="btn btn-green" name="submit_nostotagging_new_account">{l s='Create new account' mod='nostotagging'}</button>
                                    <p class="help-block">
                                        {l s='By creating a new account you agree to Nosto\'s' mod='nostotagging'} <a href="http://www.nosto.com/terms" target="_blank">{l s='Terms and Conditions' mod='nostotagging'}</a>
                                    </p>
                                </div>
                            </div>
                        {/if}
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
