{*
* 2013-2019 Nosto Solutions Ltd
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
* @copyright 2013-2019 Nosto Solutions Ltd
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

{if !empty($iframe_url) or !empty($iframe_installation_url)}
    <!--suppress HtmlUnknownTarget, JSUnresolvedFunction -->
    <form id="nosto_form_id" role="form" class="nostotagging form-horizontal"
          action="{$nostotagging_form_action|escape:'htmlall':'UTF-8'}" method="post" novalidate>
        <input type="hidden" id="nostotagging_current_language" name="nostotagging_current_language"
               value="{$nostotagging_current_language.id_lang|escape:'htmlall':'UTF-8'}">
        <input type="hidden" id="nostotagging_account_action" name="nostotagging_account_action"
               value="">
        <input type="hidden" id="nostotagging_account_email" name="nostotagging_account_email"
               value="">
        <input type="hidden" id="nostotagging_account_details" name="nostotagging_account_details"
               value="">

        <div class="container-fluid" id="nosto-settings">
            <div class="toolbar-placeholder">
                <div class="toolbarBox toolbarHead">
                    <ul>
                        <li id="submit_nostotagging_advanced_settings" style="display: none;position: absolute;right: 70px;">
                            <a id="desc-configuration-save" class="toolbar_btn" href="#" title="Save" onclick="Nosto.saveAdvancedSettings()">
                                <span class="process-icon-save"></span>
                                <div style="text-align:center">{l s='Save' mod='nostotagging'}</div>
                            </a>
                        </li>
                        {if $nostotagging_account_authorized}
                        <li id="nosto_settings_button" style="position: absolute;right: 10px;">
                            <a id="desc-configuration" class="toolbar_btn" onclick="Nosto.toggleSettings()" href="#" title="Settings">
                                <span class="process-icon-edit "></span>
                                <div style="text-align:center">{l s='Settings' mod='nostotagging'}</div>
                            </a>
                        </li>
                        {/if}
                    </ul>
                    {if count($nostotagging_languages) > 1}
                        <div style="margin-left: 10px;">
                            <h3>
                                <span id="current_obj" style="font-weight: normal;">
                                    <span > {l s='Manage accounts:' mod='nostotagging'}</span>
                                </span>
                                <!--suppress HtmlFormInputWithoutLabel -->
                                <select id="nostotagging_language">
                                    {foreach from=$nostotagging_languages item=language}
                                        <option value="{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                                {if $language.id_lang == $nostotagging_current_language.id_lang}selected="selected"{/if}>
                                            {$language.name|escape:'htmlall':'UTF-8'}
                                        </option>
                                    {/foreach}
                                </select>

                            </h3>
                        </div>
                    {/if}
                </div>
            </div>

            {if $nostotagging_account_authorized}
                <div class="panel-body panel-collapsed" id="nosto-settings-panel" style="display:none">
                    <div class="form-wrapper nostotagging_settings">
                        <fieldset id="fieldset_0">
                            <legend>
                                <img src="../modules/nostotagging/AdminNosto.gif" alt="Nosto settings">{l s='Settings' mod='nostotagging'}
                            </legend>
                            <div class="margin-form">
                                <p class="info">
                                    {$nostotagging_translations.installed_heading|escape:'htmlall':'UTF-8'}
                                    &nbsp;{$nostotagging_translations.installed_subheading|escape:'htmlall':'UTF-8'}
                                </p>
                                <a href="#" onclick="if(confirm('{l s='Are you sure you want to uninstall Nosto?' mod='nostotagging'}'))Nosto.deleteNostoAccount();" class="button">Remove Nosto</a>
                            </div>
                            <hr>

                            <label>{l s='Nosto tagging position' mod='nostotagging'}</label>
                            <div class="margin-form">
                                <input type="radio" name="nostotagging_position" id="nostotagging_position_0" value="top" {if $nostotagging_position!=="footer"}checked="checked"{/if}>
                                <label class="t" for="nostotagging_position_0">
                                    {l s='Top' mod='nostotagging'}
                                </label>
                                <br>
                                <input type="radio" name="nostotagging_position" id="nostotagging_position_1" value="footer" {if $nostotagging_position==="footer"}checked="checked"{/if}>
                                <label class="t" for="nostotagging_position_1">
                                    {l s='Footer' mod='nostotagging'}
                                </label>
                                <br>
                            </div>
                            <hr>

                            <label>{l s='Multi Currency Method' mod='nostotagging'}</label>
                            <div class="margin-form">
                                <div class="warn multi-currency-variation-alert">
                                    <p>
                                        {l s='Multi currency and price variation could not be enabled in the same time' mod='nostotagging'}
                                    </p>
                                </div>
                                <input type="radio" name="multi_currency_method" id="multi_currency_method_0" value="disabled" {if $multi_currency_method==="disabled"}checked="checked"{/if}
                                       onchange="Nosto.checkMultiCurrencyVariationConflict()"/>
                                <label class="t" for="multi_currency_method_0">
                                    {l s='Disabled' mod='nostotagging'}
                                </label>
                                <br>
                                <input type="radio" name="multi_currency_method" id="multi_currency_method_1" value="exchangeRates" {if $multi_currency_method==="exchangeRates"}checked="checked"{/if}
                                       onchange="Nosto.checkMultiCurrencyVariationConflict()"/>
                                <label class="t" for="multi_currency_method_1">
                                    {l s='Exchange rates' mod='nostotagging'}
                                </label>
                                <br>

                                <p class="preference_description">
                                    {l s='Changing this setting to "Exchange rates" will enable multi currency feature in Nosto.' mod='nostotagging'}
                                </p>
                            </div>
                            <hr>

                            {if $multi_currency_method==="exchangeRates"}
                                <div class="form-group">
                                    <div class="margin-form">
                                        <div class="info">
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
                                        <a href="#" onclick="Nosto.updateExchangeRates()" class="button">{l s='Synchronise exchange rates' mod='nostotagging'}</a>
                                    </div>
                                </div>
                            {/if}
                            <hr>

                            <label>{l s='Send SKU data to Nosto' mod='nostotagging'}</label>
                            <div class="margin-form">
                                <label class="t" for="nosto_sku_switch_on"><img src="../img/admin/enabled.gif" alt="Yes" title="Yes"></label>
                                <input type="radio" name="nosto_sku_switch" id="nosto_sku_switch_on" value="1" {if $sku_enabled === true}checked="checked" {/if}>
                                <label class="t" for="nosto_sku_switch_on"> {l s='Yes' mod='nostotagging'}</label>
                                <label class="t" for="nosto_sku_switch_off"><img src="../img/admin/disabled.gif" alt="No" title="No" style="margin-left: 10px;"></label>
                                <input type="radio" name="nosto_sku_switch" id="nosto_sku_switch_off" value="0" {if $sku_enabled !== true}checked="checked" {/if}>
                                <label class="t" for="nosto_sku_switch_off"> {l s='No' mod='nostotagging'}</label>
                                <p class="preference_description">{l s='Send SKU data to Nosto for recommendation' mod='nostotagging'}</p>
                            </div>
                            <hr>
                            <label>{l s='Send price varations to Nosto' mod='nostotagging'}</label>
                            <div class="margin-form">
                                <div class="warn multi-currency-variation-alert">
                                    <p>
                                        {l s='Multi currency and price variation could not be enabled in the same time' mod='nostotagging'}
                                    </p>
                                </div>

                                <label class="t" for="nosto_variation_switch_on"><img src="../img/admin/enabled.gif" alt="Yes" title="Yes"></label>
                                <input type="radio" name="nosto_variation_switch" id="nosto_variation_switch_on" value="1" {if $nostotagging_variation_switch === true}checked="checked" {/if}
                                       onchange="Nosto.checkMultiCurrencyVariationConflict()"/>
                                <label class="t" for="nosto_variation_switch_on"> {l s='Yes' mod='nostotagging'}</label>
                                <label class="t" for="nosto_variation_switch_off"><img src="../img/admin/disabled.gif" alt="No" title="No" style="margin-left: 10px;"></label>
                                <input type="radio" name="nosto_variation_switch" id="nosto_variation_switch_off" value="0" {if $nostotagging_variation_switch !== true}checked="checked" {/if}
                                       onchange="Nosto.checkMultiCurrencyVariationConflict()"/>
                                <label class="t" for="nosto_variation_switch_off"> {l s='No' mod='nostotagging'}</label>
                                <p class="preference_description">{l s='Send price variations to Nosto for recommendation' mod='nostotagging'}</p>
                            </div>
                            <hr>

                            <label class="nosto_variation_tax_rule_switch_div">{l s='Include countries from tax rules for price variation' mod='nostotagging'}</label>
                            <div class="margin-form nosto_variation_tax_rule_switch_div" >
                                <label class="t" for="nosto_variation_tax_rule_switch_on"><img src="../img/admin/enabled.gif" alt="Yes" title="Yes"></label>
                                <input type="radio" name="nosto_variation_tax_rule_switch" id="nosto_variation_tax_rule_switch_on" value="1" {if $nostotagging_variation_tax_rule_switch === true}checked="checked" {/if}/>
                                <label class="t" for="nosto_variation_tax_rule_switch_on"> {l s='Yes' mod='nostotagging'}</label>
                                <label class="t" for="nosto_variation_tax_rule_switch_off"><img src="../img/admin/disabled.gif" alt="No" title="No" style="margin-left: 10px;"></label>
                                <input type="radio" name="nosto_variation_tax_rule_switch" id="nosto_variation_tax_rule_switch_off" value="0" {if $nostotagging_variation_tax_rule_switch !== true}checked="checked" {/if}/>
                                <label class="t" for="nosto_variation_tax_rule_switch_off"> {l s='No' mod='nostotagging'}</label>
                                <p class="preference_description">{l s='Include countries from tax rules for price variation' mod='nostotagging'}</p>
                            </div>
                            <hr>

                        </fieldset>
                    </div>
                </div>
            {/if}

            <div class="panel">
                <div class="panel-heading">
                    <i class="icon-desktop"></i>
                </div>
                {if $nostotagging_account_authorized}
                    {if !empty($iframe_url)}
                        <div class="row nostotagging_iframe_container"
                             style="margin-left: -25px;margin-right: -25px;margin-top: 15px;">
                            <div class="col-md-12">
                                <!--suppress HtmlDeprecatedAttribute, HtmlDeprecatedAttribute -->
                                <iframe id="nostotagging_iframe" frameborder="0" width="100%"
                                        scrolling="no"
                                        src="{$iframe_url|escape:'htmlall':'UTF-8'}"></iframe>
                            </div>
                        </div>
                    {/if}
                {else}
                    <div class="row nostotagging_iframe_container"
                         style="margin-left: -25px;margin-right: -25px;margin-top: 15px;">
                        <div class="col-md-12">
                            <!--suppress HtmlDeprecatedAttribute, HtmlDeprecatedAttribute -->
                            <iframe id="nostotagging_iframe" frameborder="0" width="100%" scrolling="no"
                                    src="{$iframe_installation_url|escape:'htmlall':'UTF-8'}"></iframe>
                        </div>
                    </div>
                {/if}
            </div>
        </div>
    </form>
    <script type="text/javascript"
            src="{$module_path|escape:'htmlall':'UTF-8'}views/js/nostotagging-admin-config.js"></script>
    <script type="text/javascript"
            src="{$module_path|escape:'htmlall':'UTF-8'}views/js/iframeresizer.min.js"></script>
    <!--suppress JSUnresolvedFunction -->
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

                  var action = null;
                  if (data.type === 'newAccount') {
                        action = "{/literal}{$NostoCreateAccountUrl|escape:'javascript'}{literal}";
                    } else if (data.type === 'connectAccount' || data.type === 'syncAccount') {
                        action = "{/literal}{$NostoConnectAccountUrl|escape:'javascript'}{literal}";
                    } else if (data.type === 'removeAccount') {
                        Nosto.deleteNostoAccount();
                        return;
                    }
                    submitAction(action);
                }
            }

            // Define the "Nosto" global namespace if not already defined.
            window.Nosto = window.Nosto || {};

            function submitAction(action) {
                // noinspection JSJQueryEfficiency
                $('#nosto_form_id').attr("action", action);
                // noinspection JSJQueryEfficiency
                $('#nosto_form_id').submit();
            }

            window.Nosto.deleteNostoAccount = function () {
              var action = "{/literal}{$NostoDeleteAccountUrl|escape:'javascript'}{literal}";
              submitAction(action);
            };

            window.Nosto.updateExchangeRates = function () {
              var action = "{/literal}{$NostoUpdateExchangeRateUrl|escape:'javascript'}{literal}";
              submitAction(action);
            };

            window.Nosto.checkMultiCurrencyVariationConflict = function () {
                // noinspection JSJQueryEfficiency
                if ($("input[name='multi_currency_method']:checked").val() === 'exchangeRates'
                    && $("input[name='nosto_variation_switch']:checked").val() === '1' ) {
                    $('.multi-currency-variation-alert').show();
                    $('#desc-configuration-save').hide();
                } else {
                    $('.multi-currency-variation-alert').hide();
                    $('#desc-configuration-save').show();
                }
                // noinspection JSJQueryEfficiency
                if ($("input[name='nosto_variation_switch']:checked").val() === '1') {
                    $('.nosto_variation_tax_rule_switch_div').show();
                } else {
                    $('.nosto_variation_tax_rule_switch_div').hide();
                }
            }
            Nosto.checkMultiCurrencyVariationConflict();

            window.Nosto.saveAdvancedSettings = function () {
              var action = "{/literal}{$NostoAdvancedSettingUrl|escape:'javascript'}{literal}";
              submitAction(action);
            };

            window.Nosto.showVariationKeys = function () {
                console.log("Variation keys: {/literal}{$variation_keys|escape:'javascript'}{literal}");
                console.log("Countries from tax rules: {/literal}{$variation_countries_from_tax_rule|escape:'javascript'}{literal}");
                console.log("Countries from specific price rules: {/literal}{$variation_countries_from_price_rule|escape:'javascript'}{literal}");
                console.log("Groups from specific price rules: {/literal}{$variation_groups|escape:'javascript'}{literal}");
            };

            window.addEventListener("message", receiveMessage, false);

            window.Nosto.toggleSettings = function() {
                var hidden = $('.panel-collapsed#nosto-settings-panel');
                var shown = $('.panel-showing#nosto-settings-panel');
                hidden.slideDown().removeClass('panel-collapsed').addClass('panel-showing');
                shown.slideUp().removeClass('panel-showing').addClass('panel-collapsed');

                if (hidden.length > 0) {
                    $('#submit_nostotagging_advanced_settings').show();
                } else {
                    $('#submit_nostotagging_advanced_settings').hide();
                }
            }

        });
        {/literal}
    </script>
{/if}
