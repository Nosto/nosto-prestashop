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

/**
 * Helper class for managing the tab added to the admin section.
 */
class NostoAdminTabManager
{

    const MAIN_MENU_ITEM_CLASS = 'AdminNosto';
    const SUB_MENU_ITEM_CLASS = 'AdminNostoPersonalization';

    public static $controllers = array(
        "NostoCreateAccount",
        "NostoConnectAccount",
        "NostoDeleteAccount",
        "NostoUpdateExchangeRate",
        "NostoAdvancedSetting"
    );

    /**
     * @var array translations for the Nosto `Personalization` menu item in PS 1.5.
     */
    protected static $itemTranslations = array(
        'de' => 'Personalisierung',
        'fr' => 'Personnalisation',
        'es' => 'Personalización',
    );

    /**
     * @param $class
     * @suppress PhanDeprecatedFunction
     * @return int
     */
    public static function getAdminTabId($class)
    {
        $class = preg_replace('/Controller$/', '', $class);
        /** @noinspection PhpDeprecationInspection */
        return (int)Tab::getIdFromClassName($class);
    }

    /**
     * Installs the Admin Tab in PS backend.
     *
     * @return bool true on success, false otherwise.
     * @suppress PhanTypeMismatchProperty
     */
    public static function install()
    {
        $languages = Language::getLanguages(true);
        if (empty($languages)) {
            return false;
        }

        $idTab = self::getAdminTabId(AdminNostoController::getClassName());
        if ($idTab) {
            $mainTabAdded = new Tab($idTab);
        } else {
            /** @var TabCore $tab */
            $tab = new Tab();
            $tab->active = true;
            $tab->class_name = self::MAIN_MENU_ITEM_CLASS;
            $tab->name = array();
            foreach ($languages as $lang) {
                $tab->name[$lang['id_lang']] = 'Nosto';
            }

            $tab->id_parent = 0;
            $tab->module = NostoTagging::MODULE_NAME;
            $mainTabAdded = $tab->add();
        }

        // For PS 1.6 it is enough to have the main menu, for PS 1.5 and 1.7 we need a sub-menu.
        if ($mainTabAdded && (_PS_VERSION_ < '1.6' || _PS_VERSION_ >= '1.7')) {
            $idTab = self::getAdminTabId(AdminNostoPersonalizationController::getClassName());
            if ($idTab) {
                $subTabAdded = new Tab($idTab);
            } else {
                $tab = new Tab();
                $tab->active = true;
                $tab->class_name = self::SUB_MENU_ITEM_CLASS;
                $tab->name = array();
                foreach ($languages as $lang) {
                    if (isset(self::$itemTranslations[$lang['iso_code']])) {
                        $tab->name[$lang['id_lang']] = self::$itemTranslations[$lang['iso_code']];
                    } else {
                        $tab->name[$lang['id_lang']] = 'Personalization';
                    }
                }
                $tab->id_parent = self::getAdminTabId(AdminNostoController::getClassName());
                $tab->module = NostoTagging::MODULE_NAME;
                $subTabAdded = $tab->add();
            }
        } else {
            $subTabAdded = true;
        }

        self::registerNostoControllers();

        return (bool)($mainTabAdded && $subTabAdded);
    }


    public static function registerNostoControllers()
    {
        foreach (self::$controllers as $controllerName) {
            self::registerController($controllerName);
        }
    }

    /**
     * Register a controller
     * @param string $className the controller class name, without the "Controller part"
     * @return bool|int tab id
     *
     * @suppress PhanDeprecatedFunction
     * @suppress PhanTypeMismatchProperty
     */
    public static function registerController($className)
    {
        $tab = new Tab();
        /** @noinspection PhpDeprecationInspection */
        $tab->id = (int)Tab::getIdFromClassName($className);
        $tab->active = true;
        $languages = Language::getLanguages(true, false, true);
        if ($languages) {
            $tab->name = array();
            foreach ($languages as $lang) {
                $tab->name[(int)$lang['id_lang']] = $className;
            }
        } else {
            //In prestashop 1.5, the tab name length is limited to max 32
            $tab->name = $className;
        }
        $tab->class_name = $className;

        $tab->id_parent = -1;
        $tab->module = NostoTagging::MODULE_NAME;
        return $tab->add();
    }

    /**
     * Uninstalls the Admin Tab from PS backend.
     *
     * @return bool true on success false otherwise.
     */
    public static function uninstall()
    {
        $tabs = array(AdminNostoController::getClassName(), AdminNostoPersonalizationController::getClassName());
        foreach ($tabs as $tabName) {
            $tabId = self::getAdminTabId($tabName);
            if ($tabId) {
                /** @var TabCore $tab */
                $tab = new Tab($tabId);
                $tab->delete();
            }
        }

        return true;
    }
}
