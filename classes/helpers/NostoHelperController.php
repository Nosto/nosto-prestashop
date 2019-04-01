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

class NostoHelperController
{

    public static function getControllerName()
    {
        $result = false;

        // For prestashop 1.5 and 1.6 we can in most cases access the current controllers php_self property.
        if (isset(Context::getContext()->controller->php_self)
            && Context::getContext()->controller->php_self
        ) {
            $result = Context::getContext()->controller->php_self;
        } elseif (($controller = Tools::getValue('controller')) !== false) {
            $result = $controller;
        }

        return $result;
    }

    /**
     * Checks if the given controller is the current one.
     *
     * @param string $name the controller name
     * @return bool true if the given name is the same as the controllers php_self variable, false
     *     otherwise.
     */
    public static function isController($name)
    {
        return self::getControllerName() === $name;
    }

    /**
     * Tries to resolve current object by inspecting the underlying controller or checking for an
     * identifier. Assuming that a field called object needs to be accessed, this method will
     * first look if the controller has a method called getObject, if not, it will check if the
     * current request has a parameter called id_object and then use that identifier to instantiate
     * the object
     *
     * @param string $idName the name of the query parameter containing the id
     * @param string $klass the classname of the object to instantiate
     * @param string $method the accessor method in the base controller
     * @param bool $multiLanguageObject
     * @return mixed the resolved object or null
     *
     * @suppress PhanTypeMismatchArgument
     * @throws ReflectionException
     */
    public static function resolveObject($idName, $klass, $method, $multiLanguageObject = true)
    {
        $object = null;
        if (method_exists(Context::getContext()->controller, $method)) {
            $object = Context::getContext()->controller->$method();
        }
        $id = Tools::getValue($idName) ?: null;
        if ($object instanceof $klass === false && $id !== null) {
            try {
                $reflectionMethod = new ReflectionMethod($klass, '__construct');
            } catch (\ReflectionException $e) {
                NostoHelperLogger::error($e);
                return null;
            }
            $argsArray = array();
            $argsArray['full'] = false;
            $argsArray['idlang'] = $multiLanguageObject ? NostoHelperContext::getLanguageId() : null;
            $argsArray['idshop'] = NostoHelperContext::getShopId();
            $argsForObject = array();
            $argsForObject[] = $id; // First argument is always the id
            $constructorArgs = $reflectionMethod->getParameters();
            foreach ($constructorArgs as $constructorArg) {
                // Remove snake case
                $argName = Tools::strtolower(str_replace('_', '', $constructorArg->getName()));
                if (array_key_exists($argName, $argsArray)) {
                    $argsForObject[$argName] = $argsArray[$argName];
                }
            }
            $reflectionClass = new ReflectionClass($klass);
            $object = $reflectionClass->newInstanceArgs($argsForObject);
        }
        if ($object instanceof $klass === false || !Validate::isLoadedObject($object)) {
            $object = null;
        }
        return $object;
    }
}
