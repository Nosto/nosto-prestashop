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
 * Helper class for doing OAuth2 authorization with Nosto.
 * The client implements the 'Authorization Code' grant type.
 */
class NostoOAuthClient
{
    const PATH_AUTH = '?client_id={cid}&redirect_uri={uri}&response_type=code&scope={sco}&lang={iso}';
    const PATH_TOKEN = '/token?code={cod}&client_id={cid}&client_secret={sec}&redirect_uri={uri}&grant_type=authorization_code';

    /**
     * @var string the nosto oauth endpoint base url.
     */
    public static $baseUrl = 'https://my.nosto.com/oauth';

    /**
     * @var string the client id the identify this application to the oauth2 server.
     */
    protected $clientId = 'nosto';

    /**
     * @var string the client secret the identify this application to the oauth2 server.
     */
    protected $clientSecret = 'nosto';

    /**
     * @var string the redirect url that will be used by the oauth2 server when authenticating the client.
     */
    protected $redirectUrl;

    /**
     * @var NostoLanguageCode the language code used for localization on the oauth2 server.
     */
    protected $language;

    /**
     * @var array list of scopes to request access for during "PATH_AUTH" request.
     */
    protected $scopes = array();

    /**
     * @var NostoAccount the Nosto account if OAuth is done for a specific account to sync details.
     */
    protected $account;

    /**
     * @param NostoOauthClientMetaInterface $meta
     */
    public function __construct(NostoOauthClientMetaInterface $meta)
    {
        $this->scopes = $meta->getScopes();
        $this->clientId = $meta->getClientId();
        $this->clientSecret = $meta->getClientSecret();
        $this->redirectUrl = $meta->getRedirectUrl();
        $this->language = $meta->getLanguage();
        $this->account = $meta->getAccount();
    }

    /**
     * Returns the authorize url to the oauth2 server.
     *
     * @return string the url.
     */
    public function getAuthorizationUrl()
    {
        $url = NostoHttpRequest::buildUri(
            self::$baseUrl.self::PATH_AUTH,
            array(
                '{cid}' => $this->clientId,
                '{uri}' => urlencode($this->redirectUrl),
                '{sco}' => implode(' ', $this->scopes),
                '{iso}' => $this->language->getCode(),
            )
        );
        if ($this->account) {
            $url = NostoHttpRequest::replaceQueryParamInUrl(
                'merchant',
                $this->account->getName(),
                $url
            );
        }
        return $url;
    }

    /**
     * Authenticates the application with the given code to receive an access token.
     *
     * @param string $code code sent by the authorization server to exchange for an access token.
     * @return NostoOAuthToken
     *
     * @throws NostoException
     */
    public function authenticate($code)
    {
        if (empty($code)) {
            throw new NostoException('Invalid authentication token');
        }

        $request = new NostoHttpRequest();
        $request->setUrl(self::$baseUrl.self::PATH_TOKEN);
        $request->setReplaceParams(
            array(
                '{cid}' => $this->clientId,
                '{sec}' => $this->clientSecret,
                '{uri}' => $this->redirectUrl,
                '{cod}' => $code
            )
        );
        $response = $request->get();
        $result = $response->getJsonResult(true);
        if ($response->getCode() !== 200) {
            throw Nosto::createHttpException('Failed to authenticate with code.', $request, $response);
        }
        $merchantName = isset($result['merchant_name']) ? $result['merchant_name'] : null;
        $accessToken = isset($result['access_token']) ? $result['access_token'] : null;

        return new NostoOAuthToken($merchantName, $accessToken);
    }
}
