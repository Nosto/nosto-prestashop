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

class NostoHelperHook
{
    /**
     * Dispatches the hook `action{MODEL}LoadAfter`. This method can be called last in the tagging
     * model loadData() methods, to allow overriding of model data.
     *
     * @param string $klass the name of the class
     * @param array $params the hook params.
     * @throws PrestaShopException
     */
    public static function dispatchHookActionLoadAfter($klass, array $params)
    {
        // We replace the "NostoTagging" part of the class
        // name with "Nosto", e.g. "NostoProduct" => "NostoProduct".
        Hook::exec('action' . str_replace('NostoTagging', 'Nosto', $klass) . 'LoadAfter', $params);
    }
}
