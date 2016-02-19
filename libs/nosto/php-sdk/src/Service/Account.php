<?php
/**
 * Copyright (c) 2015, Nosto Solutions Ltd
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
 * @copyright 2015 Nosto Solutions Ltd
 * @license http://opensource.org/licenses/BSD-3-Clause BSD 3-Clause
 */

/**
 * Handles sending account related requests though the Nosto API.
 */
class NostoServiceAccount
{
    /**
     * Sends an account create API call to Nosto.
     *
     * @param NostoAccountMetaInterface $meta the account meta data.
     * @return NostoAccount the newly created account.
     *
     * @throws NostoException on failure.
     */
    public function create(NostoAccountMetaInterface $meta)
    {
        $request = $this->initApiRequest(NostoApiRequest::PATH_SIGN_UP, $meta->getSignUpApiToken());
        $request->setReplaceParams(array('{lang}' => $meta->getLanguage()->getCode()));
        $response = $request->post($this->getCreateAccountMetaAsJson($meta));
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to send account create to Nosto.', $request, $response);
        }
        $result = $response->getJsonResult(true);
        $account = new NostoAccount($meta->getPlatform().'-'.$meta->getName());
        $tokens = NostoApiToken::parseTokens($result, '', '_token');
        foreach ($tokens as $token) {
            $account->addApiToken($token);
        }
        return $account;
    }

    /**
     * Sends an account update API call to Nosto.
     *
     * Account updates are needed when making changes in the platform settings that need to be transferred to Nosto.
     * An example of this would be when a new currency is added and the price formatting details need to be made
     * available in Nosto for the recommendations.
     *
     * @param NostoAccount $account the account to update.
     * @param NostoAccountMetaInterface $meta the account meta data.
     * @return bool true on success.
     *
     * @throws NostoException on failure.
     */
    public function update(NostoAccount $account, NostoAccountMetaInterface $meta)
    {
        $token = $account->getApiToken(NostoApiToken::API_SETTINGS);
        if (is_null($token)) {
            throw new NostoException(sprintf(
                'No `%s` API token found for account "%s".',
                NostoApiToken::API_SETTINGS,
                $account->getName()
            ));
        }
        $request = $this->initApiRequest(NostoApiRequest::PATH_SETTINGS, $token->getValue());
        $response = $request->put($this->getUpdateAccountMetaAsJson($meta));
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to send account update to Nosto.', $request, $response);
        }
        return true;
    }

    /**
     * Sends an account delete API call to Nosto.
     *
     * This notifies Nosto about accounts that are no longer in use.
     *
     * @param NostoAccount $account the account to delete.
     * @return bool true on success.
     *
     * @throws NostoException on failure.
     */
    public function delete(NostoAccount $account)
    {
        $token = $account->getApiToken(NostoApiToken::API_SSO);
        if (is_null($token)) {
            throw new NostoException(sprintf(
                'No `%s` API token found for account "%s".',
                NostoApiToken::API_SSO,
                $account->getName()
            ));
        }
        $request = new NostoHttpRequest();
        $request->setUrl(NostoHttpRequest::$baseUrl.NostoHttpRequest::PATH_ACCOUNT_DELETED);
        $request->setAuthBasic('', $token->getValue());
        $response = $request->post('');
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to send account delete to Nosto.', $request, $response);
        }
        return true;
    }

    /**
     * Syncs an existing Nosto account via OAuth.
     *
     * Requires that the OAuth cycle has already completed the first step in getting the authorization code.
     *
     * @param NostoOauthClientMetaInterface $meta the OAuth client meta data to use for connection to Nosto.
     * @param string $authCode the authorization code that grants access to transfer data from Nosto.
     * @return NostoAccount the synced account.
     *
     * @throws NostoException on failure.
     */
    public function sync(NostoOauthClientMetaInterface $meta, $authCode)
    {
        $oauthClient = new NostoOAuthClient($meta);
        $token = $oauthClient->authenticate($authCode);
        $request = new NostoHttpRequest();
        // The request is currently not made according the the OAuth2 spec with the access token in the
        // Authorization header. This is due to the authentication server not implementing the full OAuth2 spec yet.
        $request->setUrl(NostoOAuthClient::$baseUrl.'/exchange');
        $request->setQueryParams(array('access_token' => $token->getAccessToken()));
        $response = $request->get();
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to send account sync to Nosto.', $request, $response);
        }
        $result = $response->getJsonResult(true);
        $account = new NostoAccount($token->getMerchantName());
        $tokens = NostoApiToken::parseTokens($result, 'api_');
        foreach ($tokens as $token) {
            $account->addApiToken($token);
        }
        if (!$account->isConnectedToNosto()) {
            throw new NostoException('Failed to sync all account details from Nosto. Unknown error.');
        }
        return $account;
    }

    /**
     * Signs the user in to Nosto via SSO.
     *
     * Requires that the account has a valid sso token associated with it.
     *
     * @param NostoAccount $account the account to sign into.
     * @param NostoAccountMetaSingleSignOnInterface $meta the SSO meta-data.
     * @return string a secure login url.
     *
     * @throws NostoException on failure.
     */
    public function sso(NostoAccount $account, NostoAccountMetaSingleSignOnInterface $meta)
    {
        $token = $account->getApiToken(NostoApiToken::API_SSO);
        if (is_null($token)) {
            throw new NostoException(sprintf(
                'No `%s` API token found for account "%s".',
                NostoApiToken::API_SSO,
                $account->getName()
            ));
        }
        $request = new NostoHttpRequest();
        $request->setUrl(NostoHttpRequest::$baseUrl.NostoHttpRequest::PATH_SSO_AUTH);
        $request->setReplaceParams(
            array(
                '{platform}' => $meta->getPlatform(),
                '{email}' => $meta->getEmail(),
            )
        );
        $request->setContentType('application/x-www-form-urlencoded');
        $request->setAuthBasic('', $token->getValue());
        $response = $request->post(
            http_build_query(
                array(
                    'fname' => $meta->getFirstName(),
                    'lname' => $meta->getLastName(),
                )
            )
        );
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to sign into Nosto using Single Sign On.', $request, $response);
        }
        $result = $response->getJsonResult();
        if (empty($result->login_url)) {
            throw new NostoException('No "login_url" returned when logging in employee to Nosto');
        }

        return $result->login_url;
    }

    /**
     * Builds the API request and returns it.
     *
     * @param string $path the request API path.
     * @param string $auth the basic auth token.
     * @return NostoApiRequest the request object.
     */
    protected function initApiRequest($path, $auth)
    {
        $request = new NostoApiRequest();
        $request->setContentType('application/json');
        $request->setAuthBasic('', $auth);
        $request->setPath($path);
        return $request;
    }

    /**
     * Turns the account meta data into valid JSON that can be sent to Nosto when creating an account.
     *
     * @param NostoAccountMetaInterface $meta the account meta data.
     * @return string the JSON.
     */
    protected function getCreateAccountMetaAsJson(NostoAccountMetaInterface $meta)
    {
        $data = array(
            'title' => $meta->getTitle(),
            'name' => $meta->getName(),
            'platform' => $meta->getPlatform(),
            'front_page_url' => $meta->getFrontPageUrl(),
            'currency_code' => $meta->getCurrency()->getCode(),
            'language_code' => $meta->getOwnerLanguage()->getCode(),
            'owner' => array(
                'first_name' => $meta->getOwner()->getFirstName(),
                'last_name' => $meta->getOwner()->getLastName(),
                'email' => $meta->getOwner()->getEmail(),
            ),
        );

        // Add optional billing details if the required data is set.
        if ($meta->getBillingDetails()->getCountry()) {
            $data['billing_details'] = array(
                'country' => $meta->getBillingDetails()->getCountry()->getCode()
            );
        }

        // Add optional partner code if one is set.
        $partnerCode = $meta->getPartnerCode();
        if (!empty($partnerCode)) {
            $data['partner_code'] = $partnerCode;
        }

        // Request all available API tokens for the account.
        $tokens = NostoApiToken::getApiTokenNames();
        if (count($tokens) > 0) {
            $data['api_tokens'] = array();
            foreach ($tokens as $name) {
                $data['api_tokens'][] = 'api_'.$name;
            }
        }

        // Add all configured currency formats.
        $currencies = $meta->getCurrencies();
        if (count($currencies) > 0) {
            $data['currencies'] = array();
            foreach ($currencies as $currency) {
                $data['currencies'][$currency->getCode()->getCode()] = array(
                    'currency_before_amount' => ($currency->getSymbol()->getPosition() === NostoCurrencySymbol::SYMBOL_POS_LEFT),
                    'currency_token' => $currency->getSymbol()->getSymbol(),
                    'decimal_character' => $currency->getFormat()->getDecimalSymbol(),
                    'grouping_separator' => $currency->getFormat()->getGroupSymbol(),
                    'decimal_places' => $currency->getFormat()->getPrecision(),
                );
            }
            // Add multi-currency settings.
            if (count($currencies) > 1) {
                $data['default_variant_id'] = $meta->getDefaultPriceVariationId();
                $data['use_exchange_rates'] = (bool)$meta->getUseCurrencyExchangeRates();
            }
        }

        return json_encode($data);
    }

    /**
     * Turns the account meta data into valid JSON that can be sent to Nosto when updating an account.
     *
     * @param NostoAccountMetaInterface $meta the account meta data.
     * @return string the JSON.
     */
    protected function getUpdateAccountMetaAsJson(NostoAccountMetaInterface $meta)
    {
        $data = array(
            'title' => $meta->getTitle(),
            'language_code' => $meta->getLanguage()->getCode(),
            'front_page_url' => $meta->getFrontPageUrl(),
            'currency_code' => $meta->getCurrency()->getCode(),
        );

        // Add all configured currency formats.
        $currencies = $meta->getCurrencies();
        if (count($currencies) > 0) {
            $data['currencies'] = array();
            foreach ($currencies as $currency) {
                $data['currencies'][$currency->getCode()->getCode()] = array(
                    'currency_before_amount' => ($currency->getSymbol()->getPosition() === NostoCurrencySymbol::SYMBOL_POS_LEFT),
                    'currency_token' => $currency->getSymbol()->getSymbol(),
                    'decimal_character' => $currency->getFormat()->getDecimalSymbol(),
                    'grouping_separator' => $currency->getFormat()->getGroupSymbol(),
                    'decimal_places' => $currency->getFormat()->getPrecision(),
                );
            }
            // Add multi-currency settings.
            if (count($currencies) > 1) {
                $data['default_variant_id'] = $meta->getDefaultPriceVariationId();
                $data['use_exchange_rates'] = (bool)$meta->getUseCurrencyExchangeRates();
            }
        }

        return json_encode($data);
    }
}
