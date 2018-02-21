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

use Nosto\Helper\IframeHelper as NostoSDKIframeHelper;
use Nosto\Helper\SerializationHelper as NostoSDKSerializationHelper;
use Nosto\Nosto as NostoSDK;
use Nosto\Object\Signup\Account as NostoSDKAccount;
use Nosto\Request\Api\Token as NostoSDKAPIToken;
use Nosto\Request\Http\HttpRequest as NostoSDKHttpRequest;
use Nosto\Types\Signup\AccountInterface as NostoSDKAccountInterface;
use Nosto\NostoException as NostoSDKException;

class NostoIndexController
{
    const DEFAULT_SERVER_ADDRESS = 'connect.nosto.com';
    const DEFAULT_IFRAME_ORIGIN_REGEXP = '(https:\/\/(.*)\.hub\.nosto\.com)|(https:\/\/my\.nosto\.com)';

    /**
     * Get the associative array for setting to smarty with all the controller's url
     *
     * @param $employeeId int
     * @return array return a associative array. Keys are the controller name + "Url"
     */
    public function getControllerUrls($employeeId)
    {
        $urlMap = array();
        foreach (NostoAdminTabManager::$controllers as $controllerName) {
            $controllerUrl = NostoHelperUrl::getControllerUrl(
                $controllerName,
                $employeeId
            );

            $urlMap[$controllerName . "Url"] = $controllerUrl;
        }

        return $urlMap;
    }

    /**
     * Get Iframe url
     *
     * @param NostoSDKAccount $account NostoAccount|null
     * @return string|null
     */
    public function getIframeUrl(NostoSDKAccount $account)
    {
        if ($account
            && $account->isConnectedToNosto()
            && Shop::getContext() === Shop::CONTEXT_SHOP
        ) {
            try {
                $currentUser = NostoCurrentUser::loadData();
                $meta = NostoIframe::loadData();
                return NostoSDKIframeHelper::getUrl($meta, $account, $currentUser);
            } catch (NostoSDKException $e) {
                NostoHelperLogger::error($e, 'Unable to load the Nosto IFrame');
            }
        }

        return null;
    }

    public function getSmartyMetaData(NostoTagging $nostoTagging)
    {
        // Always update the url to the module admin page when we access it.
        // This can then later be used by the oauth2 controller to redirect the user back.
        $adminUrl = $this->getAdminUrl();

        NostoHelperConfig::saveAdminUrl($adminUrl);
        $languages = Language::getLanguages(true, NostoHelperContext::getShopId());

        $shopGroupId = null;
        $shopId = (int)NostoHelperContext::getShopId();

        $languageId = (int)Tools::getValue('nostotagging_current_language', 0);
        $currentLanguage = NostoHelperLanguage::ensureAdminLanguage($languages, $languageId);

        return NostoHelperContext::runInContext(
            function () use ($nostoTagging, $languages, $currentLanguage) {
                return self::generateSmartyData($nostoTagging, $languages, $currentLanguage);
            },
            $languageId,
            $shopId
        );
    }

    private function generateSmartyData(NostoTagging $nostoTagging, $languages, $currentLanguage)
    {
        $account = NostoHelperAccount::getAccount();
        $missingTokens = true;
        if ($account instanceof NostoSDKAccountInterface
            && $account->getApiToken(NostoSDKAPIToken::API_EXCHANGE_RATES)
            && $account->getApiToken(NostoSDKAPIToken::API_SETTINGS)
        ) {
            $missingTokens = false;
        }
        // When no account is found we will show the installation URL
        if ($account instanceof NostoSDKAccountInterface === false
            && Shop::getContext() === Shop::CONTEXT_SHOP
        ) {
            $currentUser = NostoCurrentUser::loadData();
            $accountIframe = NostoIframe::loadData();
            $iframeInstallationUrl = NostoSDKIframeHelper::getUrl(
                $accountIframe,
                $account,
                $currentUser,
                array('v' => 1)
            );
        } else {
            $iframeInstallationUrl = null;
        }

        $accountEmail = NostoHelperContext::getEmployee()->email;
        $variationKeys = new NostoVariationKeyCollection();
        $variationKeys->loadData();

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
                    NostoHelperUrl::getModuleUrl(
                        'nostotagging',
                        'cronRates',
                        array('token' => NostoHelperCron::getCronAccessToken())
                    )
                ),
            ),
            'multi_currency_method' => NostoHelperConfig::getMultiCurrencyMethod(),
            'nostotagging_position' => NostoHelperConfig::getNostotaggingRenderPosition(),
            'nostotagging_variation_switch' => NostoHelperConfig::getVariationEnabled(),
            'nostotagging_variation_tax_rule_switch' => NostoHelperConfig::getVariationTaxRuleEnabled(),
            'nostotagging_ps_version_class' => 'ps-' . str_replace(
                '.',
                '',
                Tools::substr(_PS_VERSION_, 0, 3)
            ),
            'missing_tokens' => $missingTokens,
            'iframe_installation_url' => $iframeInstallationUrl,
            'iframe_origin' => NostoSDK::getIframeOriginRegex(),
            'sku_enabled' => NostoHelperConfig::getSkuEnabled(),
            'cart_update_enabled' => NostoHelperConfig::isCartUpdateEnabled(),
            'variation_keys' => NostoSDKSerializationHelper::serialize($variationKeys),
            'variation_countries_from_tax_rule' => implode(
                ', ',
                NostoHelperVariation::getCountriesBeingUsedInTaxRules()
            ),
            'variation_countries_from_price_rule' => implode(
                ', ',
                NostoHelperVariation::getCountriesBeingUsedInSpecificPrices()
            ),
            'variation_groups' => implode(
                ', ',
                NostoHelperVariation::getGroupsBeingUsedInSpecificPrices()
            )
        );

        if ($account) {
            // Try to login employee to Nosto in order to get a url to the internal setting pages,
            // which are then shown in an iframe on the module config page.
            $url = $this->getIframeUrl($account);
            if (!empty($url)) {
                $smartyMetaData['iframe_url'] = $url;
            }
        }

        $controllerUrls = $this->getControllerUrls(NostoHelperContext::getEmployee()->id);

        return array_merge($controllerUrls, $smartyMetaData);
    }

    /**
     * Returns the admin url.
     * Note the url is parsed from the current url, so this can only work if called when on the
     * admin page.
     *
     * @return string the url.
     */
    protected function getAdminUrl()
    {
        $currentUrl = Tools::getHttpHost(true) . (isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '');
        $parsedUrl = NostoSDKHttpRequest::parseUrl($currentUrl);
        $parsedQueryString = NostoSDKHttpRequest::parseQueryString($parsedUrl['query']);
        $validParams = array(
            'controller',
            'token',
            'configure',
            'tab_module',
            'module_name',
            'tab',
        );
        $queryParams = array();
        foreach ($validParams as $validParam) {
            if (isset($parsedQueryString[$validParam])) {
                $queryParams[$validParam] = $parsedQueryString[$validParam];
            }
        }
        $parsedUrl['query'] = http_build_query($queryParams);

        return NostoSDKHttpRequest::buildUrl($parsedUrl);
    }
}
