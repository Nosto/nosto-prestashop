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

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrades the module to version 2.7.0.
 *
 * Creates a table for Nosto customer reference and registers backoffice hook.
 *
 * @param NostoTagging $object
 * @return bool
 * @suppress PhanUnreferencedMethod
 * @noinspection PhpUnused
 */
function upgrade_module_2_7_0($object)
{
    NostoCustomerManager::createCustomerReferenceTable();
    $success = $object->registerHook('displayBackOfficeTop');
    NostoHelperConfig::clearCache();

    return $success;
}
