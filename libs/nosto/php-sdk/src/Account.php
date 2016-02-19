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
 * Account DTO (Data Transfer Object).
 */
class NostoAccount implements \NostoAccountMetaInterface
{
    /**
     * @var string the store name.
     */
    protected $_title;

    /**
     * @var string the account name.
     */
    protected $_name;

    /**
     * @var string the store front end url.
     */
    protected $_frontPageUrl;

    /**
     * @var \NostoCurrencyCode the store currency ISO (ISO 4217) code.
     */
    protected $_currency;

    /**
     * @var \NostoLanguageCode the store language ISO (ISO 639-1) code.
     */
    protected $_language;

    /**
     * @var \NostoLanguageCode the owner language ISO (ISO 639-1) code.
     */
    protected $_ownerLanguage;

    /**
     * @var \NostoOwner the account owner meta model.
     */
    protected $_owner;

    /**
     * @var \NostoAccountMetaBillingInterface the billing meta model.
     */
    protected $_billing;

    /**
     * @var \NostoCurrency[] list of supported currencies by the store.
     */
    protected $_currencies = array();

    /**
     * @var string the default price variation ID if using multiple currencies.
     */
    protected $_defaultPriceVariationId;

    /**
     * @var bool if the store uses exchange rates to manage multiple currencies.
     */
    protected $_useCurrencyExchangeRates = false;

    /**
     * @var string the API token used to identify an account creation.
     */
    protected $_signUpApiToken = 'YBDKYwSqTCzSsU8Bwbg4im2pkHMcgTy9cCX7vevjJwON1UISJIwXOLMM0a8nZY7h';

    /**
     * @var NostoApiToken[] the Nosto API tokens associated with this account.
     */
    protected $_tokens = array();

    /**
     * NostoAccount constructor.
     * @param $name
     */
    public function __construct($name = '')
    {
        $this->_name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->_title;
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->_title = $title;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->_name = $name;
    }

    /**
     * @inheritdoc
     */
    public function getPlatform()
    {
        return 'magento';
    }

    /**
     * @inheritdoc
     */
    public function getFrontPageUrl()
    {
        return $this->_frontPageUrl;
    }

    /**
     * @inheritdoc
     */
    public function setFrontPageUrl($frontPageUrl)
    {
        $this->_frontPageUrl = $frontPageUrl;
    }

    /**
     * @inheritdoc
     */
    public function getCurrency()
    {
        return $this->_currency;
    }

    /**
     * @inheritdoc
     */
    public function setCurrency(\NostoCurrencyCode $currency)
    {
        $this->_currency = $currency;
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
    public function getOwnerLanguage()
    {
        return $this->_ownerLanguage;
    }

    /**
     * @inheritdoc
     */
    public function setOwnerLanguage(\NostoLanguageCode $ownerLanguage)
    {
        $this->_ownerLanguage = $ownerLanguage;
    }

    /**
     * @inheritdoc
     */
    public function getOwner()
    {
        return $this->_owner;
    }

    /**
     * @inheritdoc
     */
    public function setOwner(\NostoAccountMetaOwnerInterface $owner)
    {
        $this->_owner = $owner;
    }

    /**
     * @inheritdoc
     */
    public function getBillingDetails()
    {
        return $this->_billing;
    }

    /**
     * @inheritdoc
     */
    public function getCurrencies()
    {
        return $this->_currencies;
    }

    /**
     * @inheritdoc
     */
    public function setCurrencies(array $currencies)
    {
        $this->_currencies = $currencies;
    }

    /**
     * @inheritdoc
     */
    public function getDefaultPriceVariationId()
    {
        return $this->_defaultPriceVariationId;
    }

    /**
     * @inheritdoc
     */
    public function getUseCurrencyExchangeRates()
    {
        return $this->_useCurrencyExchangeRates;
    }

    /**
     * @inheritdoc
     */
    public function setUseCurrencyExchangeRates($useCurrencyExchangeRates)
    {
        $this->_useCurrencyExchangeRates = $useCurrencyExchangeRates;
    }

    /**
     * @inheritdoc
     */
    public function getSignUpApiToken()
    {
        return $this->_signUpApiToken;
    }

    /**
     * @inheritdoc
     */
    public function getPartnerCode()
    {
        return null;
    }

    /**
     * @inheritdoc
     */
    public function setBilling(\NostoAccountMetaBillingInterface $billing)
    {
        $this->_billing = $billing;
    }

    /**
     * Checks if this account has been connected to Nosto, i.e. all API tokens exist.
     *
     * @return bool true if it is connected, false otherwise.
     */
    public function isConnectedToNosto()
    {
        $missingTokens = $this->getMissingScopes();
        return empty($missingTokens);
    }

    /**
     * @inheritdoc
     */
    public function getMissingScopes()
    {
        $allTokens = NostoApiToken::getApiTokenNames();
        $foundTokens = array();
        foreach ($allTokens as $tokenName) {
            foreach ($this->_tokens as $token) {
                if ($token->getName() === $tokenName) {
                    $foundTokens[] = $tokenName;
                    break;
                }
            }
        }
        return array_diff($allTokens, $foundTokens);
    }

    /**
     * @inheritdoc
     */
    public function addApiToken(NostoApiToken $token)
    {
        $this->_tokens[] = $token;
    }

    /**
     * @inheritdoc
     */
    public function getApiToken($name)
    {
        foreach ($this->_tokens as $token) {
            if ($token->getName() === $name) {
                return $token;
            }
        }
        return null;
    }

    /**
     * @inheritdoc
     */
    public function equals(NostoAccount $account)
    {
        return $account->getName() === $this->getName();
    }

    /**
     * @inheritdoc
     */
    public function getTokens()
    {
        return $this->_tokens;
    }
}