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

class ConnectAccountController extends ModuleAdminController
{
    /**
     * @inheritdoc
     */
    public function initContent()
    {
        if (!$this->viewAccess()) {
            $this->errors[] = Tools::displayError('You do not have permission to viewDeleteAccountController.php this.');
            return;
        }

        $language_id = (int)Tools::getValue(NostoTagging::MODULE_NAME.'_current_language');


        $meta = new NostoTaggingMetaOauth();
        $meta->setModuleName(NostoTagging::MODULE_NAME);
        //todo prestashop 1.5.0.0 needs this module path
//        $meta->setModulePath($this->_path);
        $meta->loadData($this->context, $language_id);
        $client = new NostoOAuthClient($meta);
        Tools::redirect($client->getAuthorizationUrl(), '');

        //todo it will redirect back to save the account data.

//        $tabId = (int)Tab::getIdFromClassName('AdminModules');
//        $employeeId = (int)$this->context->cookie->id_employee;
//        $token = Tools::getAdminToken('AdminModules'.$tabId.$employeeId);
//        Tools::redirectAdmin('index.php?controller=AdminModules&configure=nostotagging&token='.$token);
    }
}
