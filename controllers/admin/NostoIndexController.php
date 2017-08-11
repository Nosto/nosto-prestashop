<?php
/**
 * 2013-2017 Nosto Solutions Ltd
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
 * @copyright 2013-2017 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

class NostoIndexController
{
    /**
     * Get the associative array for setting to smarty with all the controller's url
     *
     * @param $employeeId int
     * @return array return a associative array. Keys are the controller name + "Url"
     */
    public function getControllerUrls($employeeId)
    {
        $urlMap = array();
        foreach (NostoTaggingHelperAdminTab::NOSTO_CONTROLLER_CLASSES as $controllerName) {
            $controllerUrl = NostoTaggingHelperUrl::getControllerUrl(
                $controllerName,
                $employeeId
            );

            $urlMap[$controllerName . "Url"] = $controllerUrl;
        }

        return $urlMap;
    }

    /**
     * Get Iframe url
     * @Context $context
     * @param $account NostoAccount|null
     * @param $languageId int
     * @param $employeeId int
     * @return string|null
     */
    public function getIframeUrl(Context $context, $account, $languageId, $employeeId)
    {
        $url = null;
        if (
            $account
            && $account->isConnectedToNosto()
            && Shop::getContext() === Shop::CONTEXT_SHOP
        ) {
            try {
                $meta = new NostoTaggingMetaAccountIframe();
                $meta->setUniqueId($this->getUniqueInstallationId());
                $meta->loadData($context, $languageId);
                $url = $account->getIframeUrl($meta);

            } catch (NostoException $e) {
                /* @var NostoTaggingHelperLogger $logger */
                $logger = Nosto::helper('nosto_tagging/logger');
                $logger->error(
                    __CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
                    $e->getCode(),
                    'Employee',
                    (int)$employeeId
                );
            }
        }

        return $url;
    }

    public function getSmartyMetaData(NostoTagging $nostoTagging)
    {
        $context = $nostoTagging->getContext();

        // Always update the url to the module admin page when we access it.
        // This can then later be used by the oauth2 controller to redirect the user back.
        $adminUrl = $this->getAdminUrl();

        /** @var NostoTaggingHelperConfig $configHelper */
        $configHelper = Nosto::helper('nosto_tagging/config');
        $configHelper->saveAdminUrl($adminUrl);
        $output = '';
        $languages = Language::getLanguages(true, $context->shop->id);
        /** @var EmployeeCore $employee */
        $employee = $context->employee;
        $accountEmail = $employee->email;
        /** @var NostoTaggingHelperUrl $urlHelper */
        $urlHelper = Nosto::helper('nosto_tagging/url');
        $shopId = null;
        $shopGroupId = null;
        if ($context->shop instanceof Shop) {
            $shopId = $context->shop->id;
            $shopGroupId = $context->shop->id_shop_group;
        }

        $languageId = (int)Tools::getValue('language_id', 0);

        // Choose current language if it has not been set.
        if (!isset($currentLanguage)) {
            $currentLanguage = NostoTagging::ensureAdminLanguage($languages, $languageId);
            $languageId = (int)$currentLanguage['id_lang'];
        }
        /** @var NostoAccount $account */
        $account = NostoTaggingHelperAccount::find($languageId, $shopGroupId, $shopId);
        $missingTokens = true;
        if (
            $account instanceof NostoAccountInterface
            && $account->getApiToken(NostoApiToken::API_EXCHANGE_RATES)
            && $account->getApiToken(NostoApiToken::API_SETTINGS)
        ) {
            $missingTokens = false;
        }
        // When no account is found we will show the installation URL
        if (
            $account instanceof NostoAccountInterface === false
            && Shop::getContext() === Shop::CONTEXT_SHOP
        ) {
            $accountIframe = new NostoTaggingMetaAccountIframe();
            $accountIframe->loadData($context, $languageId);
            /* @var NostoHelperIframe $iframeHelper */
            $iframeHelper = Nosto::helper('iframe');
            $iframeInstallationUrl = $iframeHelper->getUrl($accountIframe, null, array('v' => 1));
        } else {
            $iframeInstallationUrl = null;
        }

        /** @var NostoTaggingHelperImage $helper_images */
        $helper_images = Nosto::helper('nosto_tagging/image');

        $smartyMetaData = array(
            'nostotagging_form_action' => $this->getAdminUrl(),
            'nostotagging_has_account' => ($account !== null),
            'nostotagging_account_name' => ($account !== null) ? $account->getName() : null,
            'nostotagging_account_email' => $accountEmail,
            'nostotagging_account_authorized' => ($account !== null) ? $account->isConnectedToNosto() : false,
            'nostotagging_languages' => $languages,
            'nostotagging_current_language' => $currentLanguage,
            'nostotagging_translations' => array(
                'installed_heading' => sprintf(
                    $nostoTagging->l('You have installed Nosto to your %s shop'),
                    $currentLanguage['name']
                ),
                'installed_subheading' => sprintf(
                    $nostoTagging->l('Your account ID is %s'),
                    ($account !== null) ? $account->getName() : ''
                ),
                'not_installed_subheading' => sprintf(
                    $nostoTagging->l('Install Nosto to your %s shop'),
                    $currentLanguage['name']
                ),
                'exchange_rate_crontab_example' => sprintf(
                    '0 0 * * * curl --silent %s > /dev/null 2>&1',
                    $urlHelper->getModuleUrl(
                        'nostotagging',
                        null,
                        'cronRates',
                        $currentLanguage['id_lang'],
                        $shopId,
                        array('token' => NostoTagging::getCronAccessToken())
                    )
                ),
            ),
            'multi_currency_method' => $configHelper->getMultiCurrencyMethod(
                $currentLanguage['id_lang'],
                $shopGroupId,
                $shopId
            ),
            'nostotagging_position' => $configHelper->getNostotaggingRenderPosition(
                $currentLanguage['id_lang'],
                $shopGroupId,
                $shopId
            ),
            'nostotagging_ps_version_class' => 'ps-' . str_replace('.', '', Tools::substr(_PS_VERSION_, 0, 3)),
            'missing_tokens' => $missingTokens,
            'iframe_installation_url' => $iframeInstallationUrl,
            'iframe_origin' => $urlHelper->getIframeOrigin(),
            'image_types' => $helper_images->getProductImageTypes(),
            'current_image_type' => $configHelper->getImageType(
                $currentLanguage['id_lang'],
                $shopGroupId,
                $shopId
            )
        );

        // Try to login employee to Nosto in order to get a url to the internal setting pages,
        // which are then shown in an iframe on the module config page.
        $url = $this->getIframeUrl($context, $account, $languageId, $employee->id);
        if (!empty($url)) {
            $smartyMetaData['iframe_url'] = $url;
        }

        $controllerUrls = $this->getControllerUrls($employee->id);

        return array_merge($controllerUrls, $smartyMetaData);
    }

    /**
     * Returns a unique ID that identifies this PS installation.
     *
     * @return string the unique ID.
     */
    public function getUniqueInstallationId()
    {
        return sha1(NostoTagging::MODULE_NAME . _COOKIE_KEY_);
    }

    /**
     * Returns the admin url.
     * Note the url is parsed from the current url, so this can only work if called when on the admin page.
     *
     * @return string the url.
     */
    protected function getAdminUrl()
    {
        $currentUrl = Tools::getHttpHost(true) . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
        $parsedUrl = NostoHttpRequest::parseUrl($currentUrl);
        $parsedQueryString = NostoHttpRequest::parseQueryString($parsedUrl['query']);
        $valid_params = array(
            'controller',
            'token',
            'configure',
            'tab_module',
            'module_name',
            'tab',
        );
        $queryParams = array();
        foreach ($valid_params as $valid_param) {
            if (isset($parsedQueryString[$valid_param])) {
                $queryParams[$valid_param] = $parsedQueryString[$valid_param];
            }
        }
        $parsedUrl['query'] = http_build_query($queryParams);

        return NostoHttpRequest::buildUrl($parsedUrl);
    }
}
