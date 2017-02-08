{*
* 2013-2016 Nosto Solutions Ltd
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to contact@nosto.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
* @author    Nosto Solutions Ltd <contact@nosto.com>
* @copyright 2013-2016 Nosto Solutions Ltd
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
{if !empty($iframe_url) or !empty($iframe_installation_url)}
    <link rel="stylesheet" href="{$module_path|escape:'htmlall':'UTF-8'}views/css/tw-bs-v3.1.1.css">
    <link rel="stylesheet" href="{$module_path|escape:'htmlall':'UTF-8'}views/css/nostotagging-admin-config.css">
    <script type="text/javascript" src="{$module_path|escape:'htmlall':'UTF-8'}views/js/iframeresizer.min.js"></script>
    <script type="text/javascript"
            src="{$module_path|escape:'htmlall':'UTF-8'}views/js/nostotagging-admin-config.js"></script>
    <div class="tw-bs {$nostotagging_ps_version_class|escape:'htmlall':'UTF-8'}">
        <div class="container-fluid">
            <div class="row">
                <form class="nostotagging" id="nosto_form_id" role="form"
                      action="{$nostotagging_form_action|escape:'htmlall':'UTF-8'}" method="post"
                      enctype="multipart/form-data" novalidate="">
                    <input type="hidden" id="nostotagging_current_language" name="nostotagging_current_language"
                           value="{$nostotagging_current_language.id_lang|escape:'htmlall':'UTF-8'}">
                    <input type="hidden" id="nostotagging_account_action" name="nostotagging_account_action" value="">
                    <input type="hidden" id="nostotagging_account_email" name="nostotagging_account_email" value="">
                    <input type="hidden" id="nostotagging_account_details" name="nostotagging_account_details" value="">
                    <div class="panel panel-default">
                        {if count($nostotagging_languages) > 1 || $nostotagging_account_authorized}
                            <div class="panel-heading">
                                <div class="col-xs-8">
                                    {if count($nostotagging_languages) > 1}
                                        <label for="nostotagging_language">{l s='Manage accounts:' mod='nostotagging'}
                                            <select class="form-control" id="nostotagging_language">
                                                {foreach from=$nostotagging_languages item=language}
                                                    <option value="{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                                            {if $language.id_lang == $nostotagging_current_language.id_lang}selected="selected"{/if}>
                                                        {$language.name|escape:'htmlall':'UTF-8'}
                                                    </option>
                                                {/foreach}
                                            </select>
                                        </label>
                                    {/if}
                                </div>
                                <div class="col-xs-4 text-right">
                                    {if $nostotagging_account_authorized}
                                        <a href="#" id="nostotagging_account_setup">{l s='Account setup' mod='nostotagging'}
                                            <span class="glyphicon glyphicon-cog">&nbsp;</span>
                                        </a>
                                    {/if}
                                </div>
                            </div>
                        {/if}
                        <div class="panel-body text-center">
                            {if $nostotagging_account_authorized}
                                <div id="nostotagging_installed" class="nostotagging_settings"
                                     style="{if !empty($iframe_url)}display: none;{/if}">
                                    <h2>{$nostotagging_translations.installed_heading|escape:'htmlall':'UTF-8'}</h2>
                                    <p>{$nostotagging_translations.installed_subheading|escape:'htmlall':'UTF-8'}</p>
                                    <div class="panes">
                                        <p>{l s='If you want to change the account, you need to remove the existing one first' mod='nostotagging'}</p>
                                        {if !empty($iframe_url)}
                                            <a id="nostotagging_back_to_iframe" class="btn btn-default"
                                               role="button">{l s='Back' mod='nostotagging'}</a>
                                        {/if}
                                        <button type="submit"
                                                onclick="return confirm('{l s='Are you sure you want to uninstall Nosto?' mod='nostotagging'}');"
                                                value="1" class="btn btn-red"
                                                name="submit_nostotagging_reset_account">{l s='Remove Nosto' mod='nostotagging'}</button>
                                    </div>
                                    <hr>
                                    {if $missing_tokens === true}
                                        <div class="row-fluid">
                                            <div class="col-xs-12">
                                                <div class="alert alert-warning">
                                                    {l s='Your current installation is missing API tokens required for the multi currency settings. Please reconnect your account with Nosto by ' mod='nostotagging'}
                                                    <button type="submit" value="1" class="btn-link"
                                                            name="submit_nostotagging_authorize_account">{l s='clicking here' mod='nostotagging'}</button>
                                                </div>
                                            </div>
                                        </div>
                                    {/if}
                                    <div class="row-fluid">
                                        <div class="col-xs-6">
                                            <div class="panel" style="box-shadow: none;">
                                                <div class="panel-heading">{l s='Advanced settings' mod='nostotagging'}</div>
                                                <div class="panel-body">
                                                    <div class="form-group">
                                                        <label for="nostotagging_position">{l s='Nosto tagging position' mod='nostotagging'}</label>
                                                        <select id="nostotagging_position" name="nostotagging_position"
                                                                class="form-control input-sm">
                                                            <option value="top"
                                                                    {if $nostotagging_position==="top"}selected="selected"{/if}>{l s='Top' mod='nostotagging'}</option>
                                                            <option value="footer"
                                                                    {if $nostotagging_position==="footer"}selected="selected"{/if}>{l s='Footer' mod='nostotagging'}</option>
                                                        </select>
                                                        <p class="help-block">{l s='Change this settings to be "Footer" if your theme does not have displayTop hook' mod='nostotagging'}</p>
                                                        <label for="image_type">{l s='Image type for recommendations' mod='nostotagging'}</label>
                                                        <select id="image_type" name="image_type"
                                                                class="form-control input-sm">
                                                            {foreach from=$image_types item=image_type}
                                                                <option value="{$image_type['id_image_type']|escape:'quotes':'UTF-8'}"
                                                                        {if $current_image_type===$image_type['id_image_type']}selected="selected"{/if}>{$image_type['name']|escape:'quotes':'UTF-8'} ({$image_type['width']|escape:'quotes':'UTF-8'} x {$image_type['height']|escape:'quotes':'UTF-8'})</option>
                                                            {/foreach}
                                                            <option value="0"
                                                                    {if !$current_image_type}selected="selected"{/if}>{l s='Not defined' mod='nostotagging'}</option>

                                                        </select>
                                                        <p class="help-block">{l s='Choose which image type Nosto will use in recommendations' mod='nostotagging'}</p>

                                                        <label for="multi_currency_method">{l s='Multi Currency Method' mod='nostotagging'}</label>
                                                        <select id="multi_currency_method" name="multi_currency_method"
                                                                class="form-control input-sm"
                                                                {if $missing_tokens === true}disabled="disabled"{/if}>
                                                            <option value="disabled"
                                                                    {if $multi_currency_method==="disabled"}selected="selected"{/if}>{l s='Disabled' mod='nostotagging'}</option>
                                                            <option value="exchangeRates"
                                                                    {if $multi_currency_method==="exchangeRates"}selected="selected"{/if}>{l s='Exchange Rates' mod='nostotagging'}</option>
                                                        </select>
                                                        <p class="help-block">{l s='Changing this setting to "Exchange Rates" will enable multi currency feature in Nosto.' mod='nostotagging'}</p>
                                                        <button name="submit_nostotagging_advanced_settings"
                                                                class="btn btn-default pull-right nosto-footer"
                                                                type="submit" value="1"
                                                                {if $missing_tokens === true}disabled="disabled"{/if}>
                                                            <i class="process-icon-save"></i> {l s='Save' mod='nostotagging'}
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-xs-6">
                                            {if $multi_currency_method!=="disabled"}
                                                <div class="panel" style="box-shadow: none;">
                                                    <div class="panel-heading">{l s='Exchange rates' mod='nostotagging'}</div>
                                                    <div class="panel-body">
                                                        <p class="help-block">
                                                            {l s='The exchange rates will be synchronised to Nosto automatically whene you log in to your store admin and when you update the exchange rates.'
                                                            mod='nostotagging'}
                                                        </p>
                                                        <p class="help-block">
                                                            {l s='You can set up the cron job for updating the exchange rates by adding the example below to your servers crontab, or by using the Prestashop `cronjob` module.' mod='nostotagging'}
                                                        </p>
                                                        <p class="help-block">{$nostotagging_translations.exchange_rate_crontab_example|escape:'quotes':'UTF-8'}</p>
                                                        <p class="help-block">
                                                            {l s='You can also synchronise the exchange rates to Nosto by clicking the button below.' mod='nostotagging'}
                                                        </p>

                                                        <div class="form-group">
                                                            <button name="submit_nostotagging_update_exchange_rates"
                                                                    class="btn btn-blue" type="submit" value="1"
                                                                    {if $missing_tokens === true}disabled="disabled"{/if}>
                                                                {l s='Update exchange rates' mod='nostotagging'}
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            {/if}
                                        </div>
                                    </div>
                                </div>
                            {if !empty($iframe_url)}
                                <iframe id="nostotagging_iframe" frameborder="0" width="100%" scrolling="no"
                                        src="{$iframe_url|escape:'htmlall':'UTF-8'}"></iframe>
                            {/if}
                            {else}
                                <iframe id="nostotagging_iframe" frameborder="0" width="100%" scrolling="no"
                                        src="{$iframe_installation_url|escape:'htmlall':'UTF-8'}"></iframe>
                                <script type="text/javascript">
                                    {literal}
                                    $(document).ready(function () {
                                        iFrameResize({heightCalculationMethod: "bodyScroll"});
                                        function receiveMessage(event) {
                                            var originRegexp = new RegExp("{/literal}{$iframe_origin|escape:'htmlall':'UTF-8'}{literal}");
                                            if (!originRegexp.test(event.origin)) {
                                                return;
                                            }
                                            if (("" + event.data).substr(0, 7) !== "[Nosto]") {
                                                return;
                                            }
                                            var json = ("" + event.data).substr(7);
                                            var data = JSON.parse(json);
                                            if (typeof data === "object" && data.type) {
                                                $('#nostotagging_account_action').val(data.type);
                                                if (data.params) {
                                                    if (data.params.email) {
                                                        $('#nostotagging_account_email').val(data.params.email);
                                                    } else {
                                                        $('#nostotagging_account_email').val('');
                                                    }
                                                    if (data.params.details) {
                                                        $('#nostotagging_account_details').val(JSON.stringify(data.params.details));
                                                    } else {
                                                        $('#nostotagging_account_details').val('');
                                                    }
                                                }
                                                $('#nosto_form_id').submit();
                                            }
                                        }

                                        window.addEventListener("message", receiveMessage, false);
                                    });
                                    {/literal}
                                </script>
                            {/if}
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
{/if}