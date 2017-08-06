<?php
/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * @property NostoTagging $module
 */
class NostoTaggingOauth2ModuleFrontController extends ModuleFrontController
{
    use Nosto\Mixins\OauthTrait {
        Nosto\Mixins\OauthTrait::redirect as redirectTo;
    }

    private $id_shop;
    private $id_shop_group;
    private $id_lang;

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
        $this->id_lang = (int)Tools::getValue('language_id', Context::getContext()->language->id);
        if (Context::getContext()->shop instanceof Shop) {
            $this->id_shop = $this->context->shop->id;
            $this->id_shop_group = $this->context->shop->id_shop_group;
        }
        self::connect();
    }

    /**
     * Implemented trait method that is responsible for fetching the OAuth parameters used for all
     * OAuth operations
     *
     * @return Nosto\Oauth the OAuth parameters for the operations
     * @suppress PhanUndeclaredMethod
     */
    public function getMeta()
    {
        return NostoTaggingMetaOauth::loadData(
            Context::getContext(),
            $this->id_lang,
            $this->module->name,
            $this->module->getPath()
        );
    }

    /**
     * Implemented trait method that is responsible for saving an account with the all tokens for
     * the current store view (as defined by the parameter.)
     *
     * @param Nosto\Types\Signup\AccountInterface $account the account to save
     */
    public function save(Nosto\Types\Signup\AccountInterface $account)
    {
        NostoTaggingHelperAccount::save($account, $this->id_lang, $this->id_shop_group,
            $this->id_shop);
    }

    /**
     * Implemented trait method that redirects the user with the authentication params to the
     * admin controller.
     *
     * @param array $params the parameters to be used when building the redirect
     */
    public function redirectTo(array $params)
    {
        /** @var NostoTaggingHelperConfig $config_helper */
        $config_helper = Nosto::helper('nosto_tagging/config');
        $admin_url = $config_helper->getAdminUrl();
        if (!empty($admin_url)) {
            $admin_url = \Nosto\Request\Http\HttpRequest::replaceQueryParamsInUrl($params,
                $admin_url);
            Tools::redirect($admin_url, '');
            die;
        }
        $this->notFound();
    }

    /**
     * Implemented trait method that is a utility responsible for fetching a specified query
     * parameter from the GET request.
     *
     * @param string $name the name of the query parameter to fetch
     * @return string the value of the specified query parameter
     */
    public function getParam($name)
    {
        return Tools::getValue($name);
    }

    /**
     * Implemented trait method that is responsible for logging an exception to the Magento error
     * log when an error occurs.
     *
     * @param Exception $e the exception to be logged
     */
    public function logError(Exception $e)
    {
        NostoTaggingHelperLogger::error(
            __CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
            $e->getCode()
        );
    }

    /**
     * Implemented trait method that is responsible for redirecting the user to a 404 page when
     * the authorization code is invalid.
     */
    public function notFound()
    {
        Controller::getController('PageNotFoundController')->run();
    }
}
