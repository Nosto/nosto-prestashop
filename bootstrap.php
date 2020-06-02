<?php
/**
 * 2013-2019 Nosto Solutions Ltd
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

class NostoBootstrap
{
    /** @noinspection PhpIncludeInspection */
    public static function init($moduleDir)
    {
        require_once($moduleDir . '/libs/autoload.php');
        require_once($moduleDir . '/controllers/admin/NostoBaseController.php');
        require_once($moduleDir . '/controllers/admin/AdminNostoController.php');
        require_once($moduleDir . '/controllers/admin/AdminNostoPersonalizationController.php');
        require_once($moduleDir . '/controllers/admin/NostoAdvancedSettingController.php');
        require_once($moduleDir . '/controllers/admin/NostoConnectAccountController.php');
        require_once($moduleDir . '/controllers/admin/NostoCreateAccountController.php');
        require_once($moduleDir . '/controllers/admin/NostoUpdateExchangeRateController.php');
        require_once($moduleDir . '/controllers/admin/NostoIndexController.php');
        require_once($moduleDir . '/controllers/front/OauthTraitAdapter.php');
        require_once($moduleDir . '/classes/models/NostoNotification.php');
        require_once($moduleDir . '/classes/models/NostoExchangeRates.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperAccount.php');
        require_once($moduleDir . '/classes/managers/NostoCustomerManager.php');
        require_once($moduleDir . '/classes/managers/NostoAdminTabManager.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperConfig.php');
        require_once($moduleDir . '/classes/models/NostoCustomer.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperFlash.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperVariation.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperHook.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperLanguage.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperLogger.php');
        require_once($moduleDir . '/classes/managers/NostoNotificationManager.php');
        require_once($moduleDir . '/classes/managers/NostoHookManager.php');
        require_once($moduleDir . '/classes/services/AbstractNostoService.php');
        require_once($moduleDir . '/classes/services/NostoProductService.php');
        require_once($moduleDir . '/classes/services/NostoOrderService.php');
        require_once($moduleDir . '/classes/services/NostoRatesService.php');
        require_once($moduleDir . '/classes/services/NostoSettingsService.php');
        require_once($moduleDir . '/classes/services/NostoSignupService.php');
        require_once($moduleDir . '/classes/services/NostoCartService.php');
        require_once($moduleDir . '/classes/services/NostoCustomerService.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperUrl.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperCurrency.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperPrice.php');
        require_once($moduleDir . '/classes/models/NostoCurrentUser.php');
        require_once($moduleDir . '/classes/models/meta/NostoAccountSignup.php');
        require_once($moduleDir . '/classes/models/meta/NostoAccountBilling.php');
        require_once($moduleDir . '/classes/models/NostoIframe.php');
        require_once($moduleDir . '/classes/models/meta/NostoAccountOwner.php');
        require_once($moduleDir . '/classes/models/NostoOAuth.php');
        require_once($moduleDir . '/classes/models/NostoCart.php');
        require_once($moduleDir . '/classes/models/NostoCategory.php');
        require_once($moduleDir . '/classes/models/NostoCustomer.php');
        require_once($moduleDir . '/classes/models/NostoOrder.php');
        require_once($moduleDir . '/classes/models/order/NostoOrderBuyer.php');
        require_once($moduleDir . '/classes/models/NostoCurrentVariation.php');
        require_once($moduleDir . '/classes/models/order/NostoOrderPurchasedItem.php');
        require_once($moduleDir . '/classes/models/order/NostoOrderStatus.php');
        require_once($moduleDir . '/classes/models/NostoProduct.php');
        require_once($moduleDir . '/classes/models/NostoSku.php');
        require_once($moduleDir . '/classes/models/NostoBrand.php');
        require_once($moduleDir . '/classes/models/NostoSearch.php');
        require_once($moduleDir . '/classes/models/variation/NostoVariationCollection.php');
        require_once($moduleDir . '/classes/models/variation/NostoVariationKeyCollection.php');
        require_once($moduleDir . '/classes/models/variation/NostoVariationKey.php');
        require_once($moduleDir . '/classes/models/NostoVariation.php');
        require_once($moduleDir . '/classes/blocks/NostoBrandTagging.php');
        require_once($moduleDir . '/classes/blocks/NostoCartTagging.php');
        require_once($moduleDir . '/classes/blocks/NostoCategoryTagging.php');
        require_once($moduleDir . '/classes/blocks/NostoCustomerTagging.php');
        require_once($moduleDir . '/classes/blocks/NostoDefaultTagging.php');
        require_once($moduleDir . '/classes/blocks/NostoHeaderContent.php');
        require_once($moduleDir . '/classes/blocks/NostoOrderTagging.php');
        require_once($moduleDir . '/classes/blocks/NostoPageTypeTagging.php');
        require_once($moduleDir . '/classes/blocks/NostoProductTagging.php');
        require_once($moduleDir . '/classes/blocks/NostoRecommendationElement.php');
        require_once($moduleDir . '/classes/blocks/NostoSearchTagging.php');
        require_once($moduleDir . '/classes/blocks/NostoVariationTagging.php');
        require_once($moduleDir . '/classes/blocks/NostoHiddenElement.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperController.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperCookie.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperCron.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperFlash.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperLink.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperLogger.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperContext.php');
        require_once($moduleDir . '/classes/managers/notifications/NostoCheckAccountNotification.php');
        require_once($moduleDir . '/classes/managers/notifications/NostoCheckMulticurrencyNotification.php');
        require_once($moduleDir . '/classes/managers/notifications/NostoCheckTokenNotification.php');

        if (file_exists($moduleDir . DIRECTORY_SEPARATOR . '.env')) {
            $dotenv = new Dotenv\Dotenv($moduleDir); // @codingStandardsIgnoreLine
            $dotenv->overload();
        }
    }
}
NostoBootstrap::init(NOSTO_DIR);
