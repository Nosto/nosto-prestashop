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
 * Data Transfer Object representing a Nosto account.
 */
class NostoAccount
{
    /**
     * @var string the name of the Nosto account.
     */
    private $name;

    /**
     * @var NostoApiToken[] the Nosto API tokens associated with this account.
     */
    protected $tokens = array();

    /**
     * Constructor.
     * Create a new account object with given name.
     *
     * @param $name
     *
     * @throws NostoInvalidArgumentException
     */
    public function __construct($name)
    {
        if (!is_string($name) || empty($name)) {
            throw new NostoInvalidArgumentException(sprintf(
                '%s.name (%s) must be a non-empty string value.',
                __CLASS__,
                $name
            ));
        }

        $this->name = (string)$name;
    }

    /**
     * Gets the account name.
     *
     * @return string the account name.
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the accounts API tokens.
     *
     * @return NostoApiToken[] the tokens.
     */
    public function getTokens()
    {
        return $this->tokens;
    }

    /**
     * Checks if this account is the same as the given account.
     * They are considered equal if their name property match. The tokens are not relevant in the comparison,
     * as they are not required by the account upon creation.
     *
     * @param NostoAccount $account the account to check.
     * @return bool true if equals.
     */
    public function equals(NostoAccount $account)
    {
        return $account->getName() === $this->getName();
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
     * Returns a list of API token names that are present for the account.
     * The API tokens act as scopes when doing OAuth requests to Nosto.
     *
     * @return array the list of names.
     */
    public function getMissingScopes()
    {
        $allTokens = NostoApiToken::getApiTokenNames();
        $foundTokens = array();
        foreach ($allTokens as $tokenName) {
            foreach ($this->tokens as $token) {
                if ($token->getName() === $tokenName) {
                    $foundTokens[] = $tokenName;
                    break;
                }
            }
        }
        return array_diff($allTokens, $foundTokens);
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
     * Gets an api token associated with this account by it's name , e.g. "sso".
     *
     * @param string $name the api token name.
     * @return NostoApiToken|null the token or null if not found.
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
}
