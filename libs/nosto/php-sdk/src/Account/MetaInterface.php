<?php
/**
 * Copyright (c) 2016, Nosto Solutions Ltd
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without modification,
 * are permitted provided that the following conditions are met:
 *
 * 1. Redistributions of source code must retain the above copyright notice,
 * this list of conditions and the following disclaimer.
 *
 * 2. Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 *
 * 3. Neither the name of the copyright holder nor the names of its contributors
 * may be used to endorse or promote products derived from this software without
 * specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR
 * ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON
 * ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @author Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2016 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

/**
 * Interface for the meta data model used when creating new Nosto accounts.
 */
interface NostoAccountMetaInterface
{
    /**
     * The shops name for which the account is to be created for.
     *
     * @return string the name.
     */
    public function getTitle();

    /**
     * The name of the account to create.
     * This has to follow the pattern of "[platform name]-[8 character lowercase alpha numeric string]".
     *
     * @return string the account name.
     */
    public function getName();

    /**
     * The name of the platform the account is used on.
     * A list of valid platform names is issued by Nosto.
     *
     * @return string the platform names.
     */
    public function getPlatform();

    /**
     * Absolute url to the front page of the shop for which the account is created for.
     *
     * @return string the url.
     */
    public function getFrontPageUrl();

    /**
     * The 3-letter ISO code (ISO 4217) for the currency used by the shop for which the account is created for.
     *
     * @return NostoCurrencyCode the currency code.
     */
    public function getCurrency();

    /**
     * The 2-letter ISO code (ISO 639-1) for the language used by the shop for which the account is created for.
     *
     * @return NostoLanguageCode the language code.
     */
    public function getLanguage();

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the account owner who is creating the account.
     *
     * @return NostoLanguageCode the language code.
     */
    public function getOwnerLanguage();

    /**
     * Meta data model for the account owner who is creating the account.
     *
     * @return NostoAccountMetaOwnerInterface the meta data model.
     */
    public function getOwner();

    /**
     * Meta data model for the account billing details.
     *
     * @return NostoAccountMetaBillingInterface the meta data model.
     */
    public function getBillingDetails();

    /**
     * The API token used to identify an account creation.
     * This token is platform specific and issued by Nosto.
     *
     * @return string the API token.
     */
    public function getSignUpApiToken();

    /**
     * Optional partner code for Nosto partners.
     * The code is issued by Nosto to partners only.
     *
     * @return string|null the partner code or null if none exist.
     */
    public function getPartnerCode();

    /**
     * Returns a list of currency objects supported by the store the account is to be created for.
     *
     * @return NostoCurrency[] the currencies.
     */
    public function getCurrencies();

    /**
     * Returns the default price variation ID if store is using multiple currencies.
     * This ID identifies the price that products are specified in and can be set to the currency
     * ISO 639-1 code
     *
     * @return string|null the currency ID or null if not set.
     */
    public function getDefaultPriceVariationId();

    /**
     * Returns if exchange rates should be used for handling
     * multiple currencies. Please note that the method only tells if the
     * setting is active. Method does not take account whether multiple
     * currencies actually exist or are used.
     *
     * @return boolean if multi variants are used
     */
    public function getUseCurrencyExchangeRates();

    /**
     * Checks if this account has been connected to Nosto, i.e. all API tokens exist.
     *
     * @return bool true if it is connected, false otherwise.
     */
    public function isConnectedToNosto();

    /**
     * Returns a list of API token names that are present for the account.
     * The API tokens act as scopes when doing OAuth requests to Nosto.
     *
     * @return array the list of names.
     */
    public function getMissingScopes();

    /**
     * Gets an api token associated with this account by it's name , e.g. "sso".
     *
     * @param string $name the api token name.
     * @return NostoApiToken|null the token or null if not found.
     */
    public function getApiToken($name);

    /**
     * Returns the accounts API tokens.
     *
     * @return NostoApiToken[] the tokens.
     */
    public function getTokens();

    /**
     * Checks if this account is the same as the given account.
     * They are considered equal if their name property match. The tokens are not relevant in the comparison,
     * as they are not required by the account upon creation.
     *
     * @param NostoAccount $account the account to check.
     * @return bool true if equals.
     */
    public function equals(NostoAccount $account);
}
