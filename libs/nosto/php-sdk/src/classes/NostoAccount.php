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
 * Nosto account class for handling account related actions like, creation, OAuth2 syncing and SSO to Nosto.
 */
class NostoAccount extends NostoObject implements NostoAccountInterface, NostoValidatableInterface
{
    /**
     * @var string the name of the Nosto account.
     */
    protected $name;

    /**
     * @var NostoApiToken[] the Nosto API tokens associated with this account.
     */
    protected $tokens = array();

    /**
     * Constructor.
     * Create a new account object with given name.
     *
     * @param $name
     */
    public function __construct($name)
    {
        $this->name = $name;
        $this->validate();
    }

    /**
     * @inheritdoc
     */
    public function getValidationRules()
    {
        return array(
            array(array('name'), 'required')
        );
    }

    /**
     * @inheritdoc
     */
    public static function create(NostoAccountMetaDataInterface $meta)
    {
        $params = array(
            'title' => $meta->getTitle(),
            'name' => $meta->getName(),
            'platform' => $meta->getPlatform(),
            'front_page_url' => $meta->getFrontPageUrl(),
            'currency_code' => strtoupper($meta->getCurrencyCode()),
            'language_code' => strtolower($meta->getOwnerLanguageCode()),
            'owner' => array(
                'first_name' => $meta->getOwner()->getFirstName(),
                'last_name' => $meta->getOwner()->getLastName(),
                'email' => $meta->getOwner()->getEmail(),
            ),
            'billing_details' => array(
                'country' => strtoupper($meta->getBillingDetails()->getCountry()),
            ),
            'api_tokens' => array(),
        );

        foreach (NostoApiToken::$tokenNames as $name) {
            $params['api_tokens'][] = 'api_'.$name;
        }

        $request = new NostoApiRequest();
        $request->setPath(NostoApiRequest::PATH_SIGN_UP);
        $request->setReplaceParams(array('{lang}' => $meta->getLanguageCode()));
        $request->setContentType('application/json');
        $request->setAuthBasic('', $meta->getSignUpApiToken());
        $response = $request->post(json_encode($params));

        if ($response->getCode() !== 200) {
            Nosto::throwHttpException('Nosto account could not be created.', $request, $response);
        }

        $account = new self($meta->getPlatform().'-'.$meta->getName());
        $account->tokens = NostoApiToken::parseTokens($response->getJsonResult(true), '', '_token');
        return $account;
    }

    /**
     * @inheritdoc
     */
    public static function syncFromNosto(NostoOAuthClientMetaDataInterface $meta, $code)
    {
        $oauthClient = new NostoOAuthClient($meta);
        $token = $oauthClient->authenticate($code);

        if (empty($token->accessToken)) {
            throw new NostoException('No access token found when trying to sync account from Nosto');
        }
        if (empty($token->merchantName)) {
            throw new NostoException('No merchant name found when trying to sync account from Nosto');
        }

        $request = new NostoHttpRequest();
        // The request is currently not made according the the OAuth2 spec with the access token in the
        // Authorization header. This is due to the authentication server not implementing the full OAuth2 spec yet.
        $request->setUrl(NostoOAuthClient::$baseUrl.'/exchange');
        $request->setQueryParams(array('access_token' => $token->accessToken));
        $response = $request->get();
        $result = $response->getJsonResult(true);

        if ($response->getCode() !== 200) {
            Nosto::throwHttpException('Failed to sync account from Nosto.', $request, $response);
        }
        if (empty($result)) {
            throw new NostoException('Received invalid data from Nosto when trying to sync account');
        }

        $account = new self($token->merchantName);
        $account->tokens = NostoApiToken::parseTokens($result, 'api_');
        if (!$account->isConnectedToNosto()) {
            throw new NostoException('Failed to sync all account details from Nosto');
        }
        return $account;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $token = $this->getApiToken('sso');
        if ($token === null) {
            throw new NostoException('Failed to notify Nosto about deleted account, no "sso" token');
        }

        $request = new NostoHttpRequest();
        $request->setUrl(NostoHttpRequest::$baseUrl.NostoHttpRequest::PATH_ACCOUNT_DELETED);
        $request->setAuthBasic('', $token->getValue());
        $response = $request->post('');

        if ($response->getCode() !== 200) {
            Nosto::throwHttpException('Failed to notify Nosto about deleted account.', $request, $response);
        }
    }

    /**
     * Returns the account name.
     *
     * @return string the name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the account tokens.
     *
     * @return NostoApiToken[] the tokens.
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * @inheritdoc
     */
    public function isConnectedToNosto()
    {
        if (empty($this->tokens)) {
            return false;
        }
        $countTokens = count($this->tokens);
        $foundTokens = 0;
        foreach (NostoApiToken::getApiTokenNames() as $name) {
            foreach ($this->tokens as $token) {
                if ($token->name === $name) {
                    $foundTokens++;
                    break;
                }
            }
        }
        return ($countTokens === $foundTokens);
    }

    /**
     * Adds an API token to the account.
     *
     * @param NostoApiToken $token the token.
     */
    public function addApiToken(NostoApiToken $token)
    {
        $this->tokens[] = $token;
    }

    /**
     * @inheritdoc
     */
    public function getApiToken($name)
    {
        foreach ($this->tokens as $token) {
            if ($token->getName() === $name) {
                return $token;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function getIframeUrl(NostoAccountMetaDataIframeInterface $meta, array $params = array())
    {
        return Nosto::helper('iframe')->getUrl($meta, $this, $params);
    }

    /**
     * @inheritdoc
     */
    public function ssoLogin(NostoAccountMetaDataIframeInterface $meta)
    {
        $token = $this->getApiToken('sso');
        if ($token === null) {
            return false;
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
        $result = $response->getJsonResult();

        if ($response->getCode() !== 200) {
            Nosto::throwHttpException('Unable to login employee to Nosto with SSO token.', $request, $response);
        }
        if (empty($result->login_url)) {
            throw new NostoException('No "login_url" returned when logging in employee to Nosto');
        }

        return $result->login_url;
    }

    /**
     * Validates the account attributes.
     *
     * @throws NostoException if any attribute is invalid.
     */
    protected function validate()
    {
        $validator = new NostoValidator($this);
        if (!$validator->validate()) {
            foreach ($validator->getErrors() as $errors) {
                throw new NostoException(sprintf('Invalid Nosto account. %s', $errors[0]));
            }
        }
    }
}
