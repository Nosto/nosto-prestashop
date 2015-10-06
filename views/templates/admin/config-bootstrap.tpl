{*
* 2013-2015 Nosto Solutions Ltd
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
* @copyright 2013-2015 Nosto Solutions Ltd
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}

<div class="tw-bs {$nostotagging_ps_version_class|escape:'htmlall':'UTF-8'}">
    <div class="container-fluid">
        <div class="row">
            <form class="nostotagging" role="form" action="{$nostotagging_form_action|escape:'htmlall':'UTF-8'}" method="post" enctype="multipart/form-data" novalidate="">
                <input type="hidden" id="nostotagging_current_language" name="nostotagging_current_language" value="{$nostotagging_current_language.id_lang|escape:'htmlall':'UTF-8'}">
                <div class="panel panel-default">
                    {if count($nostotagging_languages) > 1 || $nostotagging_account_authorized}
                        <div class="panel-heading">
                            <div class="col-xs-8">
                                {if count($nostotagging_languages) > 1}
                                    <label for="nostotagging_language">{l s='Manage accounts:' mod='nostotagging'}
                                        <select class="form-control" id="nostotagging_language">
                                            {foreach from=$nostotagging_languages item=language}
                                                <option value="{$language.id_lang|escape:'htmlall':'UTF-8'}" {if $language.id_lang == $nostotagging_current_language.id_lang}selected="selected"{/if}>
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
                    {if $nostotagging_account_authorized}
                        <div id="nostotagging_view_iframe" class="panel-body text-center nosto-view-iframe" style="{if empty($iframe_url)}display: none;{/if}">
                            {if !empty($iframe_url)}
                                <iframe id="nostotagging_iframe" frameborder="0" width="100%" scrolling="no" src="{$iframe_url|escape:'htmlall':'UTF-8'}"></iframe>
                            {/if}
                        </div>
                        <div id="nostotagging_view_settings" class="panel-body text-center" style="{if !empty($iframe_url)}display: none;{/if}">
                            <div class="row-fluid">
                                <h2>{$nostotagging_translations.installed_heading|escape:'htmlall':'UTF-8'}</h2>
                                <p>{$nostotagging_translations.installed_subheading|escape:'htmlall':'UTF-8'}</p>
                                <div class="panes">
                                    <p>{l s='If you want to change the account, you need to remove the existing one first' mod='nostotagging'}</p>
                                    {if !empty($iframe_url)}
                                        <a id="nostotagging_back_to_iframe" class="btn btn-default" role="button">{l s='Back' mod='nostotagging'}</a>
                                    {/if}
                                    <button type="submit" onclick="return confirm('{l s='Are you sure you want to uninstall Nosto?' mod='nostotagging'}');"
                                            value="1" class="btn btn-red" name="submit_nostotagging_reset_account">{l s='Remove Nosto' mod='nostotagging'}</button>
                                </div>
                            </div>
                            <hr>
                            <div class="row-fluid">
                                <div class="col-xs-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{l s='Update account settings' mod='nostotagging'}</div>
                                        <div class="panel-body">
                                            <p class="help-block">{l s='Synchronise shop settings with Nosto by clicking the button below. This will send information like currency settings to Nosto.' mod='nostotagging'}</p>
                                            <div class="help-block">
                                                {l s='Preview of the current currency formats extracted from Prestashop.' mod='nostotagging'}
                                                <ul class="currency_preview">
                                                    {foreach from=$nostotagging_currency_formats item=currency_format}
                                                        <li>{$currency_format|escape:'htmlall':'UTF-8'}</li>
                                                    {/foreach}
                                                </ul>
                                            </div>
                                            <div class="form-group">
                                                <button name="submit_nostotagging_update_account" class="btn btn-blue" type="submit" value="1">
                                                    {l s='Update account' mod='nostotagging'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{l s='Update exchange rates' mod='nostotagging'}</div>
                                        <div class="panel-body">
                                            <p class="help-block">{l s='Synchronise currency exchange rates with Nosto by clicking the button below. Note that there is also a cron controller available for periodically updating the rates. You can set up the cron job by adding the example below to your servers crontab, or by using the Prestashop `cronjob` module.' mod='nostotagging'}</p>
                                            <p class="help-block">{$nostotagging_translations.exchange_rate_crontab_example|escape:'quotes':'UTF-8'}</p>
                                            <div class="form-group">
                                                <button name="submit_nostotagging_update_exchange_rates" class="btn btn-blue" type="submit" value="1">
                                                    {l s='Update exchange rates' mod='nostotagging'}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-xs-6">
                                    <div class="panel panel-default">
                                        <div class="panel-heading">{l s='Advanced settings' mod='nostotagging'}</div>
                                        <div class="panel-body">
                                            <div class="form-group">
                                                <label for="nostotagging_multi_currency_method">{l s='Multi Currency Method' mod='nostotagging'}</label>
                                                <select id="nostotagging_multi_currency_method" name="nostotagging_multi_currency_method" class="form-control input-sm">
                                                    <option value="exchangeRates" {if $nostotagging_multi_currency_method==="exchangeRates"}selected="selected"{/if}>{l s='Exchange Rates' mod='nostotagging'}</option>
                                                    <option value="priceVariation" {if $nostotagging_multi_currency_method==="priceVariation"}selected="selected"{/if}>{l s='Price Variations' mod='nostotagging'}</option>
                                                </select>
                                                <p class="help-block">{l s='By default Nosto uses the currency exchange rates from the shop to display recommendations in the correct currency. Changing this setting to "Price Variations" allows you to tag all the different prices on the product page. This can be useful when having specialized price rules configured that do not depend on the exchange rates for the currencies.' mod='nostotagging'}</p>
                                            </div>
                                            <div class="form-group">
                                                <label for="nostotagging_use_direct_include">{l s='Use direct include (Beta)' mod='nostotagging'}</label>
                                                <select id="nostotagging_use_direct_include" name="nostotagging_use_direct_include" class="form-control input-sm">
                                                    <option value="0" {if $nostotagging_use_direct_include===0}selected="selected"{/if}>{l s='No' mod='nostotagging'}</option>
                                                    <option value="1" {if $nostotagging_use_direct_include===1}selected="selected"{/if}>{l s='Yes' mod='nostotagging'}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="panel-footer">
                                            <button name="submit_nostotagging_advanced_settings" class="btn btn-default pull-right" type="submit"  value="1">
                                                <i class="process-icon-save"></i> {l s='Save' mod='nostotagging'}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {else}
                        <div class="panel-body text-center nosto-view-install">
                            <div class="row-fluid">
                                <div class="col-md-6 col-md-push-6 right-block">
                                    <div class="content-block">
                                        <div class="content-panel">
                                            <div class="panel panel-default panel-install">
                                                <div class="panel-body">
                                                    <div class="login-block">
                                                        <img src="https://my.nosto.com/public/images/nosto/logoslogan.svg" class="img-logo">
                                                        <h2 class="h4 content-header">{l s='Unlock Your 14-Day Free Trial' mod='nostotagging'}</h2>
                                                        <p class="content-subheader">{$nostotagging_translations.not_installed_subheading|escape:'htmlall':'UTF-8'}</p>
                                                        <div class="panes">
                                                            <div id="nostotagging_new_account_group">
                                                                <div class="form-group">
                                                                    <input type="text" name="nostotagging_account_email" placeholder="{l s='Your email address' mod='nostotagging'}"
                                                                           value="{$nostotagging_account_email|escape:'htmlall':'UTF-8'}">
                                                                </div>
                                                                <button type="submit" value="1" class="btn btn-blue" name="submit_nostotagging_new_account">{l s='Install' mod='nostotagging'}</button>
                                                            </div>
                                                            <div id="nostotagging_existing_account_group" class="link-wrap">
                                                                {l s='If you already have a Nosto account,' mod='nostotagging'}
                                                                <button type="submit" value="1" class="btn-link" name="submit_nostotagging_authorize_account">{l s='click here' mod='nostotagging'}</button>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <p class="terms-block">
                                                {l s='By installing you agree to Nosto\'s' mod='nostotagging'} <a href="http://www.nosto.com/terms" target="_blank">{l s='Terms and Conditions' mod='nostotagging'}</a>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6 col-md-pull-6">
                                    <div class="content-block">
                                        <div class="content-panel">
                                            <div class="panel panel-default">
                                                <div class="panel-body">
                                                    <div class="row">
                                                        <div class="col-sxs-12">
                                                            <h2>{l s='Welcome to Nosto.' mod='nostotagging'}</h2>
                                                            <!-- extras.platforms.install.welcomeNosto-->
                                                            <p class="content-text">
                                                                {l s='A full personalization solution, Nosto is the easiest way to deliver your customers personalized shopping experiences - wherever they are. ' mod='nostotagging'} <br><br>
                                                                {l s='Join the 10,000+ retailers, in over 100 countries, who are using Nosto to delight their customers and grow their business.' mod='nostotagging'}
                                                            <!-- extras.platforms.install.installMessage -->
                                                            </p>
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sxs-12 col-sm-6">
                                                            <h6>{l s='Facebook Ads' mod='nostotagging'}</h6>
                                                            <!-- extras.platforms.install.facebookAds -->
                                                            <img src="https://my.nosto.com/public/platform/img/install-feature-facebook.jpg" alt="" class="img-responsive">
                                                        </div>
                                                        <div class="col-sxs-12 col-sm-6">
                                                            <h6>{l s='Product Recommendations' mod='nostotagging'}</h6>
                                                            <!-- extras.platforms.install.productRecommendations -->
                                                            <img src="https://my.nosto.com/public/platform/img/install-feature-recommendations.jpg" alt="" class="img-responsive">
                                                        </div>
                                                    </div>
                                                    <div class="row">
                                                        <div class="col-sxs-12 col-sm-6">
                                                            <h6>{l s='Behavioural Pop-ups' mod='nostotagging'}</h6>
                                                            <!-- extras.platforms.install.behaviouralPopups -->
                                                            <img src="https://my.nosto.com/public/platform/img/install-feature-popups.jpg" alt="" class="img-responsive">
                                                        </div>
                                                        <div class="col-sxs-12 col-sm-6">
                                                            <h6>{l s='Triggered Emails' mod='nostotagging'}</h6>
                                                            <!-- extras.platforms.install.triggeredEmails -->
                                                            <img src="https://my.nosto.com/public/platform/img/install-feature-recommendations.jpg" class="img-responsive">
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    {/if}
                </div>
            </form>
        </div>
    </div>
</div>