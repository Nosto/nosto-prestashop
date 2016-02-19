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
 * Interface for the meta data model used when creating new Nosto accounts.
 */
interface NostoAccountMetaDataInterface
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
     * @return string the currency ISO code.
     */
    public function getCurrencyCode();

    /**
     * The 2-letter ISO code (ISO 639-1) for the language used by the shop for which the account is created for.
     *
     * @return string the language ISO code.
     */
    public function getLanguageCode();

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the account owner who is creating the account.
     *
     * @return string the language ISO code.
     */
    public function getOwnerLanguageCode();

    /**
     * Meta data model for the account owner who is creating the account.
     *
     * @return NostoAccountMetaDataOwnerInterface the meta data model.
     */
    public function getOwner();

    /**
     * Meta data model for the account billing details.
     *
     * @return NostoAccountMetaDataBillingDetailsInterface the meta data model.
     */
    public function getBillingDetails();

    /**
     * The API token used to identify an account creation.
     * This token is platform specific and issued by Nosto.
     *
     * @return string the API token.
     */
    public function getSignUpApiToken();
}
