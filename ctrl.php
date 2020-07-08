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
 * This is a backwards compatibility script for running module front controllers in all supported Prestashop versions.
 * The script is meant to run outside of Prestashop, so if _PS_VERSION_ is already defined, we do nothing.
 */
if (!defined('_PS_VERSION_')) {
    /**
     * White-list of valid controllers that this script is allowed to run.
     */
    $controllerWhiteList = array('oauth2', 'product', 'order', 'cronrates');

    /**
     * If this file is symlinked, then we need to parse the `SCRIPT_FILENAME` to get the path of the PS root dir.
     * This is because __FILE__ resolves the symlink source path.
     * We cannot use `/../..` on dirname() of `SCRIPT_FILENAME` as that will also resolve the symlink source path.
     * So combining as many dirname() calls as needed to step up the tree to the second parent, which should be the
     * PS root dir, is a solution.
     */
    if (!empty($_SERVER['SCRIPT_FILENAME'])) {
        $psDir = dirname(dirname(dirname($_SERVER['SCRIPT_FILENAME'])));
    }

    /**
     * If the PS dir is not set, or we cannot resolve the config file, try to to get the path relative to this file.
     * This only works if this file is NOT symlinked.
     */
    if (!isset($psDir) || !file_exists($psDir.'/config/config.inc.php')) {
        $psDir = dirname(__FILE__).'/../..';
    }

    $controllerDir = $psDir.'/modules/nostotagging/controllers/front';

    /** @noinspection PhpIncludeInspection */
    require_once($psDir.'/config/config.inc.php');
    $controller = Tools::strtolower((string)Tools::getValue('controller'));
    if (!empty($controller)
        && in_array(Tools::strtolower($controller), $controllerWhiteList)
    ) {
        $classFile = $controllerDir.'/'.$controller.'.php';
        /** @noinspection PhpIncludeInspection */
        require_once($classFile);
        switch ($controller) {
            case 'product':
                $nostoController = new NostoTaggingProductModuleFrontController();
                break;
            case 'order':
                $nostoController = new NostoTaggingOrderModuleFrontController();
                break;
            default:
                die();
        }

        try {
            $nostoController->initContent();
        } catch (Exception $e) {
            die(sprintf(
                'Unknown error happened when initializing content. Message was: %s',
                $e->getMessage()
                )
            );
        }
    } else {
        die(
            sprintf(
                'Controller %s empty or not whitelisted',
                htmlentities($controller)
            )
        );
    }
}
