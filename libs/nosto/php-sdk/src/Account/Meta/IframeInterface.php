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
 * Interface for the meta data needed for the account configuration iframe.
 */
interface NostoAccountMetaIframeInterface
{
    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the user who is loading the config iframe.
     *
     * @return NostoLanguageCode the language code.
     */
    public function getLanguage();

    /**
     * The 2-letter ISO code (ISO 639-1) for the language of the shop the account belongs to.
     *
     * @return NostoLanguageCode the language code.
     */
    public function getShopLanguage();

    /**
     * Unique identifier for the e-commerce installation.
     * This identifier is used to link accounts together that are created on the same installation.
     *
     * @return string the identifier.
     */
    public function getUniqueId();

    /**
     * The version number of the platform the e-commerce installation is running on.
     *
     * @return string the platform version.
     */
    public function getVersionPlatform();

    /**
     * The version number of the Nosto module/extension running on the e-commerce installation.
     *
     * @return string the module version.
     */
    public function getVersionModule();

    /**
     * An absolute URL for any product page in the shop the account is linked to, with the nostodebug GET parameter
     * enabled. e.g. http://myshop.com/products/product123?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlProduct();

    /**
     * An absolute URL for any category page in the shop the account is linked to, with the nostodebug GET parameter
     * enabled. e.g. http://myshop.com/products/category123?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlCategory();

    /**
     * An absolute URL for the search page in the shop the account is linked to, with the nostodebug GET parameter
     * enabled. e.g. http://myshop.com/search?query=red?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlSearch();

    /**
     * An absolute URL for the cart page in the shop the account is linked to, with the nostodebug GET parameter
     * enabled. e.g. http://myshop.com/cart?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlCart();

    /**
     * An absolute URL for the front page in the shop the account is linked to, with the nostodebug GET parameter
     * enabled. e.g. http://myshop.com?nostodebug=true
     * This is used in the config iframe to allow the user to quickly preview the recommendations on the given page.
     *
     * @return string the url.
     */
    public function getPreviewUrlFront();

    /**
     * Returns the name of the shop context where Nosto is installed or about to be installed in.
     *
     * @return string the name.
     */
    public function getShopName();
}
