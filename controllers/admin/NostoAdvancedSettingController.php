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

require_once 'NostoBaseController.php';

class NostoAdvancedSettingController extends NostoBaseController
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $languageId = $this->getLanguageId();

        /** @var EmployeeCore $employee */
        $employee = $this->context->employee;

        $shopId = null;
        $shopGroupId = null;
        if ($this->context->shop instanceof Shop) {
            $shopId = $this->context->shop->id;
            $shopGroupId = $this->context->shop->id_shop_group;
        }


        /** @var NostoTaggingHelperConfig $configHelper */
        $configHelper = Nosto::helper('nosto_tagging/config');
        /** @var NostoTaggingHelperFlashMessage $flashHelper */
        $flashHelper = Nosto::helper('nosto_tagging/flash_message');
        $configHelper->saveMultiCurrencyMethod(
            Tools::getValue('multi_currency_method'),
            $languageId,
            $shopGroupId,
            $shopId
        );
        $configHelper->saveNostoTaggingRenderPosition(
            Tools::getValue('nostotagging_position'),
            $languageId,
            $shopGroupId,
            $shopId
        );
        $configHelper->saveImageType(
            Tools::getValue('image_type'),
            $languageId,
            $shopGroupId,
            $shopId
        );
        $account = NostoTaggingHelperAccount::find($languageId, $shopGroupId, $shopId);
        $accountMeta = new NostoTaggingMetaAccount();
        $accountMeta->loadData($this->context, $languageId);

        // Make sure we Nosto is installed for the current store
        if (!empty($account) && $account->isConnectedToNosto()) {
            try {
                NostoTaggingHelperAccount::updateSettings($account, $accountMeta);
                $flashHelper->add('success', $this->l('The settings have been saved.'));
            } catch (NostoException $e) {
                /* @var NostoTaggingHelperLogger $logger */
                $logger = Nosto::helper('nosto_tagging/logger');
                $logger->error(
                    __CLASS__ . '::' . __FUNCTION__ . ' - ' . $e->getMessage(),
                    $e->getCode(),
                    'Employee',
                    (int)$employee->id
                );

                $flashHelper->add(
                    'error',
                    $this->l('There was an error saving the settings. Please, see log for details.')
                );
            }
            // Also update the exchange rates if multi currency is used
            if ($accountMeta->getUseCurrencyExchangeRates()) {
                NostoTaggingHelperAccount::updateCurrencyExchangeRates($account, $this->context);
            }
        }

        return true;
    }
}
