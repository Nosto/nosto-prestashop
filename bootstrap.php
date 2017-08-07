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

class NostoBootstrap {

    public static function init($moduleDir) {
        require_once($moduleDir . '/libs/autoload.php');
        require_once($moduleDir . '/classes/nosto.php');
        require_once($moduleDir . '/classes/admin-notification.php');
        require_once($moduleDir . '/classes/models/rates.php');
        require_once($moduleDir . '/classes/helpers/account.php');
        require_once($moduleDir . '/classes/helpers/admin-tab.php');
        require_once($moduleDir . '/classes/helpers/config.php');
        require_once($moduleDir . '/classes/helpers/customer.php');
        require_once($moduleDir . '/classes/helpers/flash-message.php');
        require_once($moduleDir . '/classes/helpers/image.php');
        require_once($moduleDir . '/classes/helpers/NostoHelperLogger.php');
        require_once($moduleDir . '/classes/helpers/notification.php');
        require_once($moduleDir . '/classes/helpers/nosto-operation.php');
        require_once($moduleDir . '/classes/helpers/product-operation.php');
        require_once($moduleDir . '/classes/helpers/order-operation.php');
        require_once($moduleDir . '/classes/helpers/updater.php');
        require_once($moduleDir . '/classes/helpers/url.php');
        require_once($moduleDir . '/classes/helpers/currency.php');
        require_once($moduleDir . '/classes/helpers/context-factory.php');
        require_once($moduleDir . '/classes/helpers/price.php');
        require_once($moduleDir . '/classes/models/current-user.php');
        require_once($moduleDir . '/classes/models/meta/account.php');
        require_once($moduleDir . '/classes/models/meta/account-billing.php');
        require_once($moduleDir . '/classes/models/meta/account-iframe.php');
        require_once($moduleDir . '/classes/models/meta/account-owner.php');
        require_once($moduleDir . '/classes/models/meta/oauth.php');
        require_once($moduleDir . '/classes/models/base.php');
        require_once($moduleDir . '/classes/models/cart.php');
        require_once($moduleDir . '/classes/models/category.php');
        require_once($moduleDir . '/classes/models/customer.php');
        require_once($moduleDir . '/classes/models/NostoOrderTagging.php');
        require_once($moduleDir . '/classes/models/order/order-buyer.php');
        require_once($moduleDir . '/classes/models/price-variation.php');
        require_once($moduleDir . '/classes/models/order/order-purchased-item.php');
        require_once($moduleDir . '/classes/models/order/order-status.php');
        require_once($moduleDir . '/classes/models/product.php');
        require_once($moduleDir . '/classes/models/brand.php');
        require_once($moduleDir . '/classes/models/search.php');

        if (file_exists($moduleDir . DIRECTORY_SEPARATOR . '.env')) {
            $dotenv = new Dotenv\Dotenv($moduleDir); // @codingStandardsIgnoreLine
            $dotenv->overload();
        }

        \Nosto\Request\Http\HttpRequest::buildUserAgent('Prestashop', _PS_VERSION_, (string)$this->version);
    }
}

NostoBootstrap::init(NOSTO_DIR);