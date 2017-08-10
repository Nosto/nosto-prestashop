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
     */
    public function initContent()
    {
        if (!$this->viewAccess()) {
            $this->errors[] = Tools::displayError('You do not have permission to view this.');
            return;
        }

        /** @var NostoTaggingHelperFlashMessage $flashHelper */
        $flashHelper = Nosto::helper('nosto_tagging/flash_message');

        //check language
        //TODO moved from nostotagging.php. no sure what is it all about
        $languages = Language::getLanguages(true, $this->context->shop->id);
        $currentLanguage = $this->ensureAdminLanguage($languages, $this->getLanguageId());
        if (Shop::getContext() !== Shop::CONTEXT_SHOP) {
            $this->redirectToAdmin();
            return;
        } elseif ($currentLanguage['id_lang'] != $this->getLanguageId()) {
            $flashHelper->add('error', $this->l('Language cannot be empty.'));
            $this->redirectToAdmin();
            return;
        }

        if ($this->execute() === true) {
            $this->redirectToAdmin();
        }
    }

    /**
     * Gets the current admin config language data.
     *
     * @param array $languages list of valid languages.
     * @param int $id_lang if a specific language is required.
     * @return array the language data array.
     */
    protected function ensureAdminLanguage(array $languages, $id_lang)
    {
        foreach ($languages as $language) {
            if ($language['id_lang'] == $id_lang) {
                return $language;
            }
        }

        if (isset($languages[0])) {
            return $languages[0];
        } else {
            return array('id_lang' => 0, 'name' => '', 'iso_code' => '');
        }
    }

    /**
     * @return int languageId
     */
    public function getLanguageId()
    {
        return (int)Tools::getValue(NostoTagging::MODULE_NAME . '_current_language');
    }

    protected function redirectToAdmin()
    {
        $tabId = (int)Tab::getIdFromClassName('AdminModules');
        $employeeId = (int)$this->context->cookie->id_employee;
        $token = Tools::getAdminToken('AdminModules' . $tabId . $employeeId);
        Tools::redirectAdmin('index.php?controller=AdminModules&configure=nostotagging&token=' . $token);
    }

    //TODO it is copied from nostotagging.php. move it to helper after refactoring

    /**
     * @return bool should it be redirect to admin after executing this method.Return true means redirect to admin url
     */
    abstract function execute();
}
