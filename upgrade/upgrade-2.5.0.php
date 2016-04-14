<?php
/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * Upgrades the module to version 2.5.0.
 *
 * Creates "action{MODEL}LoadAfter" hooks dispatched by the tagging model.
 *
 * @return bool
 */
function upgrade_module_2_5_0()
{
    $hooks = array(
        array(
            'name' => 'actionNostoOrderLoadAfter',
            'title' => 'After load nosto order',
            'description' => 'Action hook fired after a Nosto order object has been loaded.',
        ),
        array(
            'name' => 'actionNostoProductLoadAfter',
            'title' => 'After load nosto product',
            'description' => 'Action hook fired after a Nosto product object has been loaded.',
        ),
        array(
            'name' => 'actionNostoSearchLoadAfter',
            'title' => 'After load nosto search',
            'description' => 'Action hook fired after a Nosto search object has been loaded.',
        ),
    );

    $success = true;
    foreach ($hooks as $hook) {
        $callback = array('Hook', (method_exists('Hook', 'getIdByName')) ? 'getIdByName' : 'get');
        $id_hook = call_user_func($callback, $hook['name']);
        if (empty($id_hook)) {
            $new_hook = new Hook();
            $new_hook->name = $hook['name'];
            $new_hook->title = $hook['title'];
            $new_hook->description = $hook['description'];
            $new_hook->add();
            $id_hook = $new_hook->id;
            if (!$id_hook) {
                $success = false;
            }
        }
    }

    return $success;
}
