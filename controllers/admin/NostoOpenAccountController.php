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
 * @copyright 2013-2020 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once 'NostoBaseController.php';

use Nosto\Helper\ConnectionHelper as NostoSDKConnectionHelper;
use Nosto\Types\Signup\AccountInterface as NostoSDKAccountInterface;

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
     * @noinspection PhpUnused
     */
    public function execute()
    {
        try {
            $account = NostoHelperAccount::getAccount();
            $currentUser = NostoCurrentUser::loadData();
            $accountConnection = NostoConnection::loadData();
            $connectionUrl = NostoSDKConnectionHelper::getUrl(
                $accountConnection,
                $account,
                $currentUser,
                array('v' => 1)
            );

            // When no account is found we will redirect to the installation URL
            if ($account instanceof NostoSDKAccountInterface === false
                && Shop::getContext() === Shop::CONTEXT_SHOP) {

                $langIdLabel = NostoTagging::MODULE_NAME . '_current_language';
                $langIdValue = $this->getLanguageId();
                $langIdParam = [$langIdLabel => $langIdValue];
                $baseUrl = NostoHelperUrl::getBaseUrl();

                $params = [
                    'createUrl'  => $baseUrl . basename(_PS_ADMIN_DIR_) . '/' . $this->context->link->getAdminLink('NostoCreateAccount', true)  . '&' . http_build_query($langIdParam) ,
                    'connectUrl' => $baseUrl . basename(_PS_ADMIN_DIR_) . '/' . $this->context->link->getAdminLink('NostoConnectAccount', true) . '&' . http_build_query($langIdParam) ,
                    'deleteUrl'  => $baseUrl . basename(_PS_ADMIN_DIR_) . '/' . $this->context->link->getAdminLink('NostoDeleteAccount', true)  . '&' . http_build_query($langIdParam) ,
                ];

                $connectionUrl .= '&' . http_build_query($params);
            } else {
                $params = [
                    'dashboard_rd' => 'true'
                ];

                $connectionUrl .= '&' . http_build_query($params);
            }

            Tools::redirect($connectionUrl, '');

        } catch (Exception $e) {
            NostoHelperFlash::add(
                'error',
                Context::getContext()->getTranslator()->trans('Connection controls could not be opened. Please see logs for details.')
            );
            NostoHelperLogger::error($e, 'Opening Nosto view failed');
        }
        return true;
    }
}
