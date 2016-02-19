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
 * Oauth DTO (Data Transfer Object).
 */
class NostoOauth implements \NostoOauthClientMetaInterface
{
    /**
     * @var array The scopes for the OAuth2 request.
     */
    protected $_scopes = array();

    /**
     * @var string the url where the oauth2 server should redirect after
     * authorization is done.
     */
    protected $_redirectUrl;

    /**
     * @var \NostoLanguageCode the 2-letter ISO code (ISO 639-1) for localization
     * on oauth2 server.
     */
    protected $_language;

    /**
     * @var \NostoAccount|null account if OAuth is to sync details.
     */
    protected $_account;

    /**
     * @inheritdoc
     */
    public function getClientId()
    {
        return 'magento';
    }

    /**
     * @inheritdoc
     */
    public function getClientSecret()
    {
        return 'magento';
    }

    /**
     * @inheritdoc
     */
    public function getScopes()
    {
        return $this->_scopes;
    }

    /**
     * @inheritdoc
     */
    public function setScopes(array $scopes)
    {
        $this->_scopes = $scopes;
    }

    /**
     * @inheritdoc
     */
    public function getRedirectUrl()
    {
        return $this->_redirectUrl;
    }

    /**
     * @inheritdoc
     */
    public function setRedirectUrl($redirectUrl)
    {
        $this->_redirectUrl = $redirectUrl;
    }

    /**
     * @inheritdoc
     */
    public function getLanguage()
    {
        return $this->_language;
    }

    /**
     * @inheritdoc
     */
    public function setLanguage(\NostoLanguageCode $language)
    {
        $this->_language = $language;
    }

    /**
     * @inheritdoc
     */
    public function getAccount()
    {
        return $this->_account;
    }

    /**
     * @inheritdoc
     */
    public function setAccount(\NostoAccountMetaInterface $account)
    {
        $this->_account = $account;
    }
}
