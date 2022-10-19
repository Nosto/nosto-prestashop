{*
* 2013-2022 Nosto Solutions Ltd
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
* @copyright 2013-2022 Nosto Solutions Ltd
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<!--suppress JSUnresolvedFunction, Annotator, ES6ConvertVarToLetConst -->
<style type="text/css">
    .clickable {
        cursor: pointer;
        padding-right: 5px;
    }
</style>
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

    <div class="panel" id="nosto-settings">
        <div class="panel-heading container-fluid">
            {if count($nostotagging_languages) > 1}
                <div class="col-md-1">
                    {l s='Manage accounts:' mod='nostotagging'}
                </div>
                <div class="col-md-2">
                    <!--suppress HtmlFormInputWithoutLabel -->
                    <select id="nostotagging_language">
                        {foreach from=$nostotagging_languages item=language}
                            <option value="{$language.id_lang|escape:'htmlall':'UTF-8'}"
                                    {if $language.id_lang == $nostotagging_current_language.id_lang}selected="selected"{/if}>
                                {$language.name|escape:'htmlall':'UTF-8'}
                            </option>
                        {/foreach}
                    </select>
                </div>
            {else}
                <div class="col-md-3">
                </div>
            {/if}
            {if $nostotagging_account_authorized}
                <span class="pull-right clickable panel-collapsed">
                    <i class="icon-chevron-down"></i>
                    {l s='Settings' mod='nostotagging'}
                </span>
            {/if}
        </div>
        {if $nostotagging_account_authorized}
            <div class="panel-body" style="display:none">
                <div class="form-wrapper nostotagging_settings">
                    <div class="form-group">
                        <div class="col-lg-offset-3">
                            <div class="alert alert-info">
                                {$nostotagging_translations.installed_heading|escape:'htmlall':'UTF-8'}
                                &nbsp;{$nostotagging_translations.installed_subheading|escape:'htmlall':'UTF-8'}
                            </div>
                        </div>
                        <div class="col-lg-offset-3">
                            <button class="btn btn-danger btn-lg" type="submit"
                                    onclick="if(confirm('{l s='Are you sure you want to uninstall Nosto?' mod='nostotagging'}'))deleteNostoAccount();"
                                    name="submit_nostotagging_reset_account">
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
                                <button class="btn btn-default btn-warning btn-lg" type="submit"
                                        onclick="if(confirm('{l s='Are you sure you want to reconnect Nosto?' mod='nostotagging'}'))reconnectNostoAccount();"
                                        name="submit_nostotagging_authorize_account">
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
                            <span class="label-tooltip" data-toggle="tooltip" title=""
                                  data-original-title="{l s='Change this settings to be "Footer" if your theme does not have displayTop hook' mod='nostotagging'}">
                                {l s='Nosto tagging position' mod='nostotagging'}
                            </span>
                        </label>
                        <div class="col-lg-9">
                            <div class="radio">
                                <label for="simple_product">
                                    <input type="radio" name="nostotagging_position" value="top"
                                           {if $nostotagging_position==="top"}checked="checked"{/if}>
                                    {l s='Top' mod='nostotagging'}
                                </label>
                            </div>
                            <div class="radio">
                                <label for="pack_product">
                                    <input type="radio" name="nostotagging_position"
                                           value="footer"
                                           {if $nostotagging_position==="footer"}checked="checked"{/if}>
                                    {l s='Footer' mod='nostotagging'}
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Send cart update -->
                    <hr>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                                <span title="" data-toggle="tooltip" class="label-tooltip"
                                      data-original-title="{l s='Set this to no if you don\'t want to send real-time cart updates to Nosto via API' mod='nostotagging'}"
                                      data-html="true">
                                    {l s='Real-time cart updates to Nosto' mod='nostotagging'}
                                </span>
                        </label>
                        <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="nosto_cart_update_switch" id="nosto_cart_update_switch_on"
                                           value="1" {if $cart_update_enabled === true}checked="checked" {/if}/>
                                    <label for="nosto_cart_update_switch_on" class="radioCheck">Yes</label>
                                    <input type="radio" name="nosto_cart_update_switch"
                                           id="nosto_cart_update_switch_off" value="0"
                                           {if $cart_update_enabled !== true}checked="checked" {/if}/>
                                    <label for="nosto_cart_update_switch_off" class="radioCheck">No</label>
                                    <a class="slide-button btn"></a>
                                </span>
                        </div>
                    </div>

                    <hr>
                    <div class="form-group">
                        <div class="alert alert-danger col-lg-9 col-lg-offset-3 multi-currency-variation-alert">
                            <p>
                                {l s='Multi currency and price variation could not be enabled in the same time' mod='nostotagging'}
                            </p>
                        </div>
                        <label class="control-label col-lg-3" for="multi_currency_method">
                            {l s='Multi Currency Method' mod='nostotagging'}
                        </label>
                        <div class="col-lg-9">
                            <div class="radio ">
                                <label>
                                    <input type="radio" name="multi_currency_method" value="disabled"
                                           onchange="checkMultiCurrencyVariationConflict()"
                                           {if $multi_currency_method==="disabled"}checked="checked"{/if}/>
                                    {l s='Disabled' mod='nostotagging'}
                                </label>
                            </div>
                            <div class="radio ">
                                <label>
                                    <input type="radio" name="multi_currency_method" value="exchangeRates"
                                           onchange="checkMultiCurrencyVariationConflict()"
                                           {if $multi_currency_method==="exchangeRates"}checked="checked"{/if}/>
                                    {l s='Exchange rates' mod='nostotagging'}
                                </label>
                            </div>
                            <p class="help-block">
                                <i class="icon-warning-sign"></i>
                                {l s='Changing this setting to "Exchange rates" will enable multi currency feature in Nosto.' mod='nostotagging'}
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
                                    <button class="btn btn-default btn-info btn-lg"
                                            onclick="updateExchangeRates()"
                                            name="submit_nostotagging_update_exchange_rates"
                                            value="1">
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
                    <hr>
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                                <span title="" data-toggle="tooltip" class="label-tooltip"
                                      data-original-title="{l s='Send SKU data to Nosto for recommendation' mod='nostotagging'}"
                                      data-html="true">
                                    {l s='Send SKU data to Nosto' mod='nostotagging'}
                                </span>
                        </label>
                        <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="nosto_sku_switch" id="nosto_sku_switch_on" value="1"
                                           {if $sku_enabled === true}checked="checked" {/if}/>
                                    <label for="nosto_sku_switch_on" class="radioCheck">Yes</label>
                                    <input type="radio" name="nosto_sku_switch" id="nosto_sku_switch_off" value="0"
                                           {if $sku_enabled !== true}checked="checked" {/if}/>
                                    <label for="nosto_sku_switch_off" class="radioCheck">No</label>
                                    <a class="slide-button btn"></a>
                                </span>
                        </div>
                    </div>
                    <hr>
                    <div class="alert alert-danger col-lg-9 col-lg-offset-3 multi-currency-variation-alert">
                        <p>
                            {l s='Multi currency and price variation could not be enabled in the same time' mod='nostotagging'}
                        </p>
                    </div>

                    <!-- Price variation -->
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                                <span title="" data-toggle="tooltip" class="label-tooltip"
                                      data-original-title="{l s='Price variation' mod='nostotagging'}" data-html="true">
                                    {l s='Send price variation data to Nosto' mod='nostotagging'}
                                </span>
                        </label>
                        <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="nosto_variation_switch" id="nosto_variation_switch_on"
                                           value="1" {if $nostotagging_variation_switch === true}checked="checked" {/if}
                                           onchange="checkMultiCurrencyVariationConflict()"/>
                                    <label for="nosto_variation_switch_on" class="radioCheck">Yes</label>
                                    <input type="radio" name="nosto_variation_switch" id="nosto_variation_switch_off"
                                           value="0" {if $nostotagging_variation_switch !== true}checked="checked" {/if}
                                           onchange="checkMultiCurrencyVariationConflict()"/>
                                    <label for="nosto_variation_switch_off" class="radioCheck">No</label>
                                    <a class="slide-button btn"></a>
                                </span>
                        </div>
                    </div>

                    <!-- Price variation tax rule -->
                    <div class="form-group" id="nosto_variation_tax_rule_switch_div">
                        <label class="control-label col-lg-3">
                                <span title="" data-toggle="tooltip" class="label-tooltip"
                                      data-original-title="{l s='Include countries from tax rules for price variation ' mod='nostotagging'}"
                                      data-html="true">
                                    {l s='Include countries from tax rules for price variation' mod='nostotagging'}
                                </span>
                        </label>
                        <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="nosto_variation_tax_rule_switch"
                                           id="nosto_variation_tax_rule_switch_on" value="1"
                                           {if $nostotagging_variation_tax_rule_switch === true}checked="checked" {/if}/>
                                    <label for="nosto_variation_tax_rule_switch_on" class="radioCheck">Yes</label>
                                    <input type="radio" name="nosto_variation_tax_rule_switch"
                                           id="nosto_variation_tax_rule_switch_off" value="0"
                                           {if $nostotagging_variation_tax_rule_switch !== true}checked="checked" {/if}/>
                                    <label for="nosto_variation_tax_rule_switch_off" class="radioCheck">No</label>
                                    <a class="slide-button btn"></a>
                                </span>
                        </div>
                    </div>

                    <hr>
                    <!-- Customer information switch -->
                    <div class="form-group">
                        <label class="control-label col-lg-3">
                                <span title="" data-toggle="tooltip" class="label-tooltip"
                                      data-original-title="{l s='Send customer data to nosto' mod='nostotagging'}"
                                      data-html="true">
                                    {l s='Send customer data to nosto' mod='nostotagging'}
                                </span>
                        </label>
                        <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="nosto_customer_tagging_switch"
                                           id="nosto_customer_tagging_switch_on" value="1"
                                           {if $customer_tagging_switch === true}checked="checked" {/if} />
                                    <label for="nosto_customer_tagging_switch_on" class="radioCheck">Yes</label>
                                    <input type="radio" name="nosto_customer_tagging_switch"
                                           id="nosto_customer_tagging_switch_off" value="0"
                                           {if $customer_tagging_switch !== true}checked="checked" {/if} />
                                    <label for="nosto_customer_tagging_switch_off" class="radioCheck">No</label>
                                    <a class="slide-button btn"></a>
                                </span>
                        </div>
                    </div>
                    <!-- Escape search terms switch -->
                    <div class="form-group" id="nosto_variation_tax_rule_switch_div">
                        <label class="control-label col-lg-3">
                                <span title="" data-toggle="tooltip" class="label-tooltip"
                                      data-original-title="{l s='Disable escape search terms' mod='nostotagging'}"
                                      data-html="true">
                                    {l s='Disable escape search terms' mod='nostotagging'}
                                </span>
                        </label>
                        <div class="col-lg-9">
                                <span class="switch prestashop-switch fixed-width-lg">
                                    <input type="radio" name="nosto_tagging_disable_escape_search_terms_switch"
                                           id="nosto_tagging_disable_escape_search_terms_switch_on" value="1"
                                           {if $nosto_tagging_disable_escape_search_terms_switch === true}checked="checked" {/if}/>
                                    <label for="nosto_tagging_disable_escape_search_terms_switch_on" class="radioCheck">Yes</label>
                                    <input type="radio" name="nosto_tagging_disable_escape_search_terms_switch"
                                           id="nosto_tagging_disable_escape_search_terms_switch_off" value="0"
                                           {if $nosto_tagging_disable_escape_search_terms_switch !== true}checked="checked" {/if}/>
                                    <label for="nosto_tagging_disable_escape_search_terms_switch_off"
                                           class="radioCheck">No</label>
                                    <a class="slide-button btn"></a>
                                </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer" style="display:none;margin-bottom: 10px;">
                <button type="submit" onclick="saveAdvancedSettings()" value="1"
                        name="submit_nostotagging_advanced_settings"
                        id="submit_nostotagging_advanced_settings"
                        class="btn btn-default pull-right">
                    <i class="process-icon-save"></i> Save
                </button>
            </div>
        {/if}

        <div class="panel">
            <div class="row nostotagging_account_container">
                <button class="btn btn-lg"
                        onclick="openNostoAccount({if $nostotagging_account_authorized}{"true"}{else}{"false"}{/if});"
                        name="nostotagging_open_account">
                                <span class="ladda-label">
                                    {if $nostotagging_account_authorized}
                                        {l s='Open Nosto' mod='nostotagging'}
                                    {else}
                                        {l s='Install Nosto' mod='nostotagging'}
                                    {/if}
                                </span>
                    <span class="ladda-spinner"></span>
                </button>
            </div>
        </div>
    </div>
</form>
<script type="text/javascript"
        src="{$module_path|escape:'htmlall':'UTF-8'}views/js/nostotagging-admin-config.js"></script>
<!-- Toggle Nosto Settings -->
<script type="text/javascript">
    $(document).on('click', '.panel-heading span.clickable', function () {
        var $this = $(this);
        if (!$this.hasClass('panel-collapsed')) {
            $this.parents('.panel').find('.panel-body').slideUp();
            $this.parents('.panel').find('.panel-footer').slideUp();
            $this.addClass('panel-collapsed');
            $this.find('i').removeClass('icon-chevron-up').addClass('icon-chevron-down');
        } else {
            $this.parents('.panel').find('.panel-body').slideDown();
            $this.parents('.panel').find('.panel-footer').slideDown();
            $this.removeClass('panel-collapsed');
            $this.find('i').removeClass('icon-chevron-down').addClass('icon-chevron-up');
        }
    })
</script>
<!--suppress JSJQueryEfficiency -->
<script type="text/javascript">
    {literal}
        function submitAction(action) {
            $('#nosto_form_id').attr("action", action);
        }

        function targetBlank() {
            $('#nosto_form_id').attr("target", '_blank');
        }

        function openNostoAccount(newTab) {
            var action = "{/literal}{$NostoOpenAccountUrl|escape:'javascript':'UTF-8'}{literal}";
            if (newTab) {
                targetBlank();
            }
            submitAction(action);
        }

        function deleteNostoAccount() {
            $('#nosto_form_id').attr("target", '_self');
            var action = "{/literal}{$NostoDeleteAccountUrl|escape:'javascript':'UTF-8'}{literal}";
            submitAction(action);
        }

        function updateExchangeRates() {
            var action = "{/literal}{$NostoUpdateExchangeRateUrl|escape:'javascript':'UTF-8'}{literal}";
            submitAction(action);
        }

        function checkMultiCurrencyVariationConflict() {
            if ($("input[name='multi_currency_method']:checked").val() === 'exchangeRates'
                && $("input[name='nosto_variation_switch']:checked").val() === '1') {
                $('.multi-currency-variation-alert').show();
                $('#submit_nostotagging_advanced_settings').attr("disabled", "disabled");
            } else {
                $('.multi-currency-variation-alert').hide();
                $('#submit_nostotagging_advanced_settings').removeAttr("disabled");
            }

            if ($("input[name='nosto_variation_switch']:checked").val() === '1') {
                $('#nosto_variation_tax_rule_switch_div').show();
            } else {
                $('#nosto_variation_tax_rule_switch_div').hide();
            }
        }

        function saveAdvancedSettings() {
            var action = "{/literal}{$NostoAdvancedSettingUrl|escape:'javascript':'UTF-8'}{literal}";
            submitAction(action);
        }
    {/literal}
</script>
