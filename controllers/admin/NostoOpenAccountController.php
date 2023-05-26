<?php
/**
 * 2013-2022 Nosto Solutions Ltd
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
 * @copyright 2013-2022 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once 'NostoBaseController.php';

use Nosto\Helper\ConnectionHelper as NostoSDKConnectionHelper;

/**
 * Class NostoOpenAccountController
 *
 * @property Context $context
 * @noinspection PhpUnused
 */
class NostoOpenAccountController extends NostoBaseController
{
    /**
     * @inheritdoc
     *
     * @suppress PhanDeprecatedFunction
     * @noinspection PhpUnused, PhpDeprecationInspection
     */
    public function execute()
    {
        try {
            $langId = $this->getLanguageId();

            $params = [
                'v' => 1,
                'createUrl'  => NostoHelperUrl::getFullAdminControllerUrl('NostoCreateAccount', $langId),
                'connectUrl' => NostoHelperUrl::getFullAdminControllerUrl('NostoConnectAccount', $langId),
                'deleteUrl'  => NostoHelperUrl::getFullAdminControllerUrl('NostoDeleteAccount', $langId),
                'dashboard_rd' => 'true' // Redirect to Dashboard
            ];

            $account = NostoHelperAccount::getAccount();
            $currentUser = NostoCurrentUser::loadData();
            $accountConnection = NostoConnection::loadData();
            $connectionUrl = NostoSDKConnectionHelper::getUrl(
                $accountConnection,
                $account,
                $currentUser,
                $params
            );
            $connectionUrl .= '&' . http_build_query($params);
            Tools::redirect($connectionUrl, '');
        } catch (Exception $e) {
            NostoHelperFlash::add(
                'error',
                $this->l('Connection controls could not be opened. Please see logs for details.')
            );
            NostoHelperLogger::error($e, 'Opening Nosto view failed');
        }
        return true;
    }
}
