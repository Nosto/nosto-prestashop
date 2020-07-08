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

/**
 * PS 1.6 admin controller for the Nosto admin tab.
 */
class AdminNostoController extends ModuleAdminController
{
    /**
     * A way to get class name for php 5.3 and lower
     * @return string class Name
     */
    public static function getClassName()
    {
        return get_called_class();
    }

    /**
     * @inheritdoc
     * @noinspection PhpUnused
     */
    public function initContent()
    {
        if (!$this->viewAccess()) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $this->errors[] = Tools::displayError('You do not have permission to view this.');
            return;
        }

        $idTab = NostoAdminTabManager::getAdminTabId('AdminModulesController');
        /** @noinspection PhpUndefinedFieldInspection */
        $idEmployee = (int)$this->context->cookie->id_employee;
        $token = Tools::getAdminToken('AdminModules' . $idTab . $idEmployee);
        Tools::redirectAdmin('index.php?controller=AdminModules&configure=nostotagging&token=' . $token);
    }
}
