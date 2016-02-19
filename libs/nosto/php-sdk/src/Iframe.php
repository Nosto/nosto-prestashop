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
 * IFrame DTO (Data Transfer Object).
 */
class NostoIframe implements \NostoAccountMetaIframeInterface
{
    /**
     * @var string unique ID that identifies the Magento installation.
     */
    protected $_uniqueId;

    /**
     * @var \NostoLanguageCode the language code for oauth server locale.
     */
    protected $_language;

    /**
     * @var \NostoLanguageCode the language code for the store view scope.
     */
    protected $_shopLanguage;

    /**
     * @var string the name of the store Nosto is installed in or about to be installed.
     */
    protected $_shopName;

    /**
     * @var string the Magento version number.
     */
    protected $_versionPlatform;

    /**
     * @var string the Nosto_Tagging version number.
     */
    protected $_versionModule;

    /**
     * @var string preview url for the product page in the active store scope.
     */
    protected $_previewUrlProduct;

    /**
     * @var string preview url for the category page in the active store scope.
     */
    protected $_previewUrlCategory;

    /**
     * @var string preview url for the search page in the active store scope.
     */
    protected $_previewUrlSearch;

    /**
     * @var string preview url for the cart page in the active store scope.
     */
    protected $_previewUrlCart;

    /**
     * @var string preview url for the front page in the active store scope.
     */
    protected $_previewUrlFront;

    /**
     * @inheritdoc
     */
    public function getUniqueId()
    {
        return $this->_uniqueId;
    }

    /**
     * @inheritdoc
     */
    public function setUniqueId($uniqueId)
    {
        $this->_uniqueId = $uniqueId;
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
    public function getShopLanguage()
    {
        return $this->_shopLanguage;
    }

    /**
     * @inheritdoc
     */
    public function setShopLanguage(\NostoLanguageCode $shopLanguage)
    {
        $this->_shopLanguage = $shopLanguage;
    }

    /**
     * @inheritdoc
     */
    public function getShopName()
    {
        return $this->_shopName;
    }

    /**
     * @inheritdoc
     */
    public function setShopName($shopName)
    {
        $this->_shopName = $shopName;
    }

    /**
     * @inheritdoc
     */
    public function getVersionPlatform()
    {
        return $this->_versionPlatform;
    }

    /**
     * @inheritdoc
     */
    public function setVersionPlatform($platformVersion)
    {
        $this->_versionPlatform = $platformVersion;
    }

    /**
     * @inheritdoc
     */
    public function getVersionModule()
    {
        return $this->_versionModule;
    }

    /**
     * @inheritdoc
     */
    public function setVersionModule($moduleVersion)
    {
        $this->_versionModule = $moduleVersion;
    }

    /**
     * @inheritdoc
     */
    public function getPreviewUrlProduct()
    {
        return $this->_previewUrlProduct;
    }

    /**
     * @inheritdoc
     */
    public function setPreviewUrlProduct($productPreviewUrl)
    {
        $this->_previewUrlProduct = $productPreviewUrl;
    }

    /**
     * @inheritdoc
     */
    public function getPreviewUrlCategory()
    {
        return $this->_previewUrlCategory;
    }

    /**
     * @inheritdoc
     */
    public function setPreviewUrlCategory($categoryPreviewUrl)
    {
        $this->_previewUrlCategory = $categoryPreviewUrl;
    }

    /**
     * @inheritdoc
     */
    public function getPreviewUrlSearch()
    {
        return $this->_previewUrlSearch;
    }

    /**
     * @inheritdoc
     */
    public function setPreviewUrlSearch($searchPreviewUrl)
    {
        $this->_previewUrlSearch = $searchPreviewUrl;
    }

    /**
     * @inheritdoc
     */
    public function getPreviewUrlCart()
    {
        return $this->_previewUrlCart;
    }

    /**
     * @inheritdoc
     */
    public function setPreviewUrlCart($cartPreviewUrl)
    {
        $this->_previewUrlCart = $cartPreviewUrl;
    }

    /**
     * @inheritdoc
     */
    public function getPreviewUrlFront()
    {
        return $this->_previewUrlFront;
    }

    /**
     * @inheritdoc
     */
    public function setPreviewUrlFront($frontPreviewUrl)
    {
        $this->_previewUrlFront = $frontPreviewUrl;
    }
}
