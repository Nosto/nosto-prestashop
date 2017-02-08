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
    <link rel="stylesheet" href="{$module_path|escape:'htmlall':'UTF-8'}views/css/nostotagging-admin-config.css">
    <form id="nosto_form_id" role="form" class="nostotagging form-horizontal" action="{$nostotagging_form_action|escape:'htmlall':'UTF-8'}" method="post" novalidate>
        <input type="hidden" id="nostotagging_current_language" name="nostotagging_current_language" value="{$nostotagging_current_language.id_lang|escape:'htmlall':'UTF-8'}">
        <input type="hidden" id="nostotagging_account_action" name="nostotagging_account_action" value="">
        <input type="hidden" id="nostotagging_account_email" name="nostotagging_account_email" value="">
        <input type="hidden" id="nostotagging_account_details" name="nostotagging_account_details" value="">
        <div class="panel" id="nosto-settings">
            <div class="panel-heading">
                <div class="row">
                    {if count($nostotagging_languages) > 1}
                        <div class="col-md-1">
                            {l s='Manage accounts:' mod='nostotagging'}
                        </div>
                        <div class="col-md-2">
                                <select id="nostotagging_language">
                                    {foreach from=$nostotagging_languages item=language}
                                        <option value="{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang == $nostotagging_current_language.id_lang}selected="selected"{/if}>
                                            {$language.name|escape:'htmlall':'UTF-8'}
                                        </option>
                                    {/foreach}
                                </select>
                        </div>
                    {else}
                        <div class="col-md-3">
                        </div>
                    {/if}
                    <div class="pull-right">
                        {if $nostotagging_account_authorized}
                            <p class="nostotagging_settings">
                                <a href="#" id="nostotagging_account_setup">
                                    <i class="icon-cog"></i>
                                    {l s='Account setup' mod='nostotagging'}
                                </a>
                            </p>
                        {/if}
                    </div>
                </div>
            </div>
            {if $nostotagging_account_authorized}
                <div class="form-wrapper nostotagging_settings">
                    <div class="form-group">
                        <div class="col-lg-offset-3">
                            <div class="alert alert-info">
                                {$nostotagging_translations.installed_heading|escape:'htmlall':'UTF-8'}&nbsp;{$nostotagging_translations.installed_subheading|escape:'htmlall':'UTF-8'}
                            </div>
                        </div>
                        <div class="col-lg-offset-3">
                            <button class="btn btn-danger btn-lg" type="submit" onclick="return confirm('{l s='Are you sure you want to uninstall Nosto?' mod='nostotagging'}');" name="submit_nostotagging_reset_account">
                                <span class="ladda-label">
                                    <i class="icon-remove"></i>
                                    {l s='Remove Nosto' mod='nostotagging'}
                                </span>
                                <span class="ladda-spinner"></span>
                            </button>
                        </div>
                    </div>
                    <hr>
                    {if $missing_tokens}
                        <div class="form-group">
                            <div class="col-lg-offset-3">
                                <div class="alert alert-warning">{l s='Your current installation is missing API tokens required for the multi currency settings. Please reconnect your account with Nosto by cliking the button below' mod='nostotagging'}</div>
                            </div>
                            <div class="col-lg-offset-3">
                                <button class="btn btn-default btn-warning btn-lg" type="submit" name="submit_nostotagging_authorize_account">
                                    <span class="ladda-label">
                                        <i class="icon-exchange"></i>
                                        {l s='Reconnect account' mod='nostotagging'}
                                    </span>
                                    <span class="ladda-spinner"></span>
                                </button>
                            </div>
                        </div>
                        <hr>
                    {/if}
                    <div class="form-group">
                        <label class="control-label col-lg-3" for="nostotagging_position">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Change this settings to be "Footer" if your theme does not have displayTop hook' mod='nostotagging'}">
                                {l s='Nosto tagging position' mod='nostotagging'}
                            </span>
                        </label>
                        <div class="col-lg-9">
                            <div class="radio">
                                <label for="simple_product">
                                    <input type="radio" name="nostotagging_position" value="top" {if $nostotagging_position==="top"}checked="checked"{/if}>
                                    {l s='Top' mod='nostotagging'}
                                </label>
                            </div>
                            <div class="radio">
                                <label for="pack_product">
                                    <input type="radio" name="nostotagging_position" value="footer" {if $nostotagging_position==="footer"}checked="checked"{/if}>
                                    {l s='Footer' mod='nostotagging'}
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label class="control-label col-lg-3" for="image_type">
                            <span class="label-tooltip" data-toggle="tooltip" title="" data-original-title="{l s='Choose which image type Nosto will use in recommendations' mod='nostotagging'}">
                                {l s='Image type for recommendations' mod='nostotagging'}
                            </span>
                        </label>

                        <div class="col-lg-9">
                            <div class="radio">
                            </div>
                            {foreach from=$image_types item=image_type}
                                <div class="radio ">
                                    <label>
                                        <input type="radio" name="image_type" value="{$image_type['id_image_type']|escape:'quotes':'UTF-8'}" {if $current_image_type===$image_type['id_image_type']}checked="checked"{/if}/>
                                        {$image_type['name']|escape:'quotes':'UTF-8'} ({$image_type['width']|escape:'quotes':'UTF-8'} x {$image_type['height']|escape:'quotes':'UTF-8'})
                                    </label>
                                </div>
                            {/foreach}
                            <div class="radio">
                                <label>
                                    <input type="radio" name="image_type" value="0" {if !$current_image_type}checked="checked"{/if}/>
                                    {l s='Not defined' mod='nostotagging'}
                                </label>
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="form-group">
                        <label class="control-label col-lg-3" for="multi_currency_method">
                            {l s='Multi Currency Method' mod='nostotagging'}
                        </label>
                        <div class="col-lg-9">
                            <div class="radio ">
                                <label>
                                    <input type="radio" name="multi_currency_method" value="disabled" {if $multi_currency_method==="disabled"}checked="checked"{/if}/>
                                    {l s='Disabled' mod='nostotagging'}
                                </label>
                            </div>
                            <div class="radio ">
                                <label>
                                    <input type="radio" name="multi_currency_method" value="exchangeRates" {if $multi_currency_method==="exchangeRates"}checked="checked"{/if}/>
                                    {l s='Exchange Rates' mod='nostotagging'}
                                </label>
                            </div>
                            <p class="help-block">
                                <i class="icon-warning-sign"></i>
                                {l s='Changing this setting to "Exchange Rates" will enable multi currency feature in Nosto.' mod='nostotagging'}
                            </p>
                        </div>
                    </div>
                    {if $multi_currency_method!=="disabled"}
                        <div class="form-group">
                            <div class="col-lg-9 col-lg-offset-3">
                                <div class="alert alert-info">
                                    <p>
                                        {l s='The exchange rates will be synchronised to Nosto automatically whene you log in to your store admin and when you update the exchange rates.' mod='nostotagging'}
                                    </p>
                                    <p>
                                        {l s='You can also set up the cron job for updating the exchange rates by adding the example below to your servers crontab, or by using the Prestashop `cronjob` module.' mod='nostotagging'}
                                    </p>
                                    <p style="font-style: italic;">{$nostotagging_translations.exchange_rate_crontab_example|escape:'quotes':'UTF-8'}</p>
                                </div>
                                <p>
                                    {l s='You can also synchronise the exchange rates to Nosto by clicking the button below.' mod='nostotagging'}
                                </p>
                                <div class="form-group">
                                    <button class="btn btn-default btn-info btn-lg" type="submit" name="submit_nostotagging_update_exchange_rates" value="1">
                                        <span class="ladda-label">
                                            <i class="icon-refresh"></i>
                                            {l s='Synchronise exchange rates' mod='nostotagging'}
                                        </span>
                                        <span class="ladda-spinner"></span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    {/if}
                </div>
                <div class="row nostotagging_settings">
                    <div class="col-md-12">
                        <button type="submit" value="1" name="submit_nostotagging_advanced_settings" class="btn btn-default pull-right">
                            <i class="process-icon-save"></i>
                            {l s='Save' mod='nostotagging'}
                        </button>
                    </div>
                </div>
                {if !empty($iframe_url)}
                    <div class="row nostotagging_iframe_container">
                        <div class="col-md-12">
                            <iframe id="nostotagging_iframe" frameborder="0" width="100%" scrolling="no" src="{$iframe_url|escape:'htmlall':'UTF-8'}"></iframe>
                        </div>
                    </div>
                {/if}
            {else}
                <div class="row nostotagging_iframe_container">
                    <div class="col-md-12">
                        <iframe id="nostotagging_iframe" frameborder="0" width="100%" scrolling="no" src="{$iframe_installation_url|escape:'htmlall':'UTF-8'}"></iframe>
                    </div>
                </div>
            {/if}
        </div>
    </form>
    <script type="text/javascript" src="{$module_path|escape:'htmlall':'UTF-8'}views/js/nostotagging-admin-config.js"></script>
    <script type="text/javascript" src="{$module_path|escape:'htmlall':'UTF-8'}views/js/iframeresizer.min.js"></script>
    <script type="text/javascript">
        {literal}
        $(document).ready(function() {
            iFrameResize({heightCalculationMethod : "bodyScroll"});
            function receiveMessage(event) {
                var originRegexp = new RegExp("{/literal}{$iframe_origin|escape:'htmlall':'UTF-8'}{literal}");
                if (!originRegexp.test(event.origin)) {
                    return;
                }
                if ((""+event.data).substr(0, 7) !== "[Nosto]") {
                    return;
                }
                var json = (""+event.data).substr(7);
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