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

/**
 * Base model for all tagging model classes.
 */
abstract class NostoTaggingModel
{

    /**
     * Dispatches the hook `action{MODEL}LoadAfter`.
     *
     * This method can be called last in the tagging model loadData() methods, to allow overriding of model data.
     *
     * @param array $params the hook params.
     */
    protected function dispatchHookActionLoadAfter(array $params)
    {
        // We replace the "NostoTagging" part of the class
        // name with "Nosto", e.g. "NostoTaggingProduct" => "NostoProduct".
        $this->dispatchHook(
            'action'.str_replace('NostoTagging', 'Nosto', get_class($this)).'LoadAfter',
            $params
        );
    }

    /**
     * Executes a PS hook by name.
     *
     * Abstracts the differences between PS versions.
     *
     * @param string $name the hook name.
     * @param array $params the hook params.
     */
    private function dispatchHook($name, array $params)
    {
        Hook::exec($name, $params);
    }

    /**
     * Returns a protected/private property value by invoking it's public getter.
     *
     * The getter names are assumed to be the property name in camel case with preceding word "get".
     *
     * @param string $name the property name.
     * @return mixed the property value.
     * @throws Exception if public getter does not exist.
     */
    public function __get($name)
    {
        $getter = 'get'.str_replace('_', '', $name);
        if (method_exists($this, $getter)) {
            return $this->{$getter}();
        }
        throw new Exception(sprintf('Property `%s.%s` is not defined.', get_class($this), $name));
    }
}
