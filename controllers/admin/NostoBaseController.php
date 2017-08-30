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
abstract class NostoBaseController extends ModuleAdminController
{
    /**
     * @inheritdoc
     *
     * @suppress PhanDeprecatedFunction
     */
    public function initContent()
    {
        if (!$this->viewAccess()) {
            $this->errors[] = Tools::displayError('You do not have permission to view this.');
            return;
        }

        $languages = Language::getLanguages(true, $this->context->shop->id);
        $handlingLanguage = NostoHelperLanguage::ensureAdminLanguage($languages, $this->getLanguageId());
        if (Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->redirectToAdmin();
            return;
        } elseif ($handlingLanguage['id_lang'] != $this->getLanguageId()) {
            NostoHelperFlash::add('error', $this->l('Language cannot be empty.'));
            $this->redirectToAdmin();
            return;
        }

        //run the code in a context with language id set to the language admin chose
        $redirectToAdminPage = NostoHelperContext::runInContext(
            function () {
                return $this->execute();
            },
            $handlingLanguage['id_lang'],
            NostoHelperContext::getShopId()
        );

        if ($redirectToAdminPage) {
            $this->redirectToAdmin();
        }
    }

    /**
     * @return int languageId
     */
    public function getLanguageId()
    {
        return (int)Tools::getValue(NostoTagging::MODULE_NAME . '_current_language');
    }

    /**
     * @suppress PhanDeprecatedFunction
     */
    protected function redirectToAdmin()
    {
        $tabId = (int)Tab::getIdFromClassName('AdminModules');
        $employeeId = (int)$this->context->cookie->id_employee;
        $token = Tools::getAdminToken('AdminModules' . $tabId . $employeeId);
        Tools::redirectAdmin('index.php?controller=AdminModules&configure=nostotagging&token=' . $token);
    }

    /**
     * @return bool should it be redirect to admin after executing this method.Return true means
     *     redirect to admin url
     */
    public abstract function execute();
}
