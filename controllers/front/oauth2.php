<?php
/**
 * 2013-2020 Nosto Solutions Ltd
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to contact@nosto.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2019 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * @property NostoTagging $module
 * @noinspection PhpUnused
 */
class NostoTaggingOauth2ModuleFrontController extends ModuleFrontController
{
    /**
     * Handles the redirect from Nosto oauth2 authorization server when an existing account is
     * connected to a store. This is handled in the front end as the oauth2 server validates the
     * "return_url" sent in the first step of the authorization cycle, and requires it to be from
     * the same domain that the account is configured for and only redirects to that domain.
     *
     * @return void
     */
    public function initContent()
    {
        $oauthAdaptor = new OauthTraitAdapter();
        $oauthAdaptor->initContent($this->module);
    }
}
