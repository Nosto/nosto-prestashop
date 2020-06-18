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

class NostoHookManager
{
    /**
     * Adds custom hooks used by this module and is run upon module installation.
     *
     * @param array $hooks the array of custom hook names to register
     * @return bool if the custom hook registration was successful
     *
     * @suppress PhanTypeArraySuspicious
     */
    public static function initHooks($hooks)
    {
        $success = true;
        if (!empty($hooks)) {
            /** @var array $hook */
            foreach ($hooks as $hookInfo) {
                $callback = array(
                    'Hook',
                    (method_exists('Hook', 'getIdByName')) ? 'getIdByName' : 'get'
                );
                $idHook = call_user_func($callback, $hookInfo['name']);
                if (empty($idHook)) {
                    $hook = new Hook();
                    $hook->name = pSQL($hookInfo['name']);
                    $hook->title = pSQL($hookInfo['title']);
                    $hook->description = pSQL($hookInfo['description']);
                    $hook->add();
                    $idHook = $hook->id;
                    if (!$idHook) {
                        $success = false;
                    }
                }
            }
        }

        return $success;
    }
}
