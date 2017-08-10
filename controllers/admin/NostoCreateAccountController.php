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

/**
 * Class CreateAccountController
 * @property Context $context
 */
class NostoCreateAccountController extends NostoBaseController
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        /** @var EmployeeCore $employee */
        $employee = $this->context->employee;

        /** @var NostoTaggingHelperFlashMessage $flashHelper */
        $flashHelper = Nosto::helper('nosto_tagging/flash_message');

        /** @var NostoTaggingHelperConfig $configHelper */
        $configHelper = Nosto::helper('nosto_tagging/config');

        $accountEmail = (string)Tools::getValue(NostoTagging::MODULE_NAME . '_account_email');
        if (empty($accountEmail)) {
            $flashHelper->add('error', $this->l('Email cannot be empty.'));
        } elseif (!Validate::isEmail($accountEmail)) {
            $flashHelper->add('error', $this->l('Email is not a valid email address.'));
        } else {
            try {
                if (Tools::isSubmit('nostotagging_account_details')) {
                    $accountDetails = Tools::jsonDecode(Tools::getValue('nostotagging_account_details'));
                } else {
                    $accountDetails = false;
                }
                $this->createAccount($this->getLanguageId(), $accountEmail, $accountDetails);
                $configHelper->clearCache();
                $flashHelper->add(
                    'success',
                    $this->l(
                        'Account created. Please check your email and follow the instructions to set a'
                        . ' password for your new account within three days.'
                    )
                );
            } catch (NostoApiResponseException $e) {
                $flashHelper->add(
                    'error',
                    $this->l(
                        'Account could not be automatically created due to missing or invalid parameters.'
                        . ' Please see your Prestashop logs for details'
                    )
                );
                /* @var NostoTaggingHelperLogger $logger */
                $logger = Nosto::helper('nosto_tagging/logger');
                $logger->error(
                    'Creating Nosto account failed: ' . $e->getMessage() . ':' . $e->getCode(),
                    $e->getCode(),
                    'Employee',
                    (int)$employee->id
                );
            } catch (Exception $e) {
                $flashHelper->add(
                    'error',
                    $this->l('Account could not be automatically created. Please see logs for details.')
                );
                /* @var NostoTaggingHelperLogger $logger */
                $logger = Nosto::helper('nosto_tagging/logger');
                $logger->error(
                    'Creating Nosto account failed: ' . $e->getMessage() . ':' . $e->getCode(),
                    $e->getCode(),
                    'Employee',
                    (int)$employee->id
                );
            }
        }

        return true;
    }

    /**
     * Creates a new Nosto account for given shop language.
     *
     * @param int $id_lang the language ID for which to create the account.
     * @param string $email the account owner email address.
     * @param string $account_details the details for the account.
     * @return bool true if account was created, false otherwise.
     */
    protected function createAccount($id_lang, $email, $account_details = "")
    {
        $meta = new NostoTaggingMetaAccount();
        $meta->loadData($this->context, $id_lang);
        $meta->getOwner()->setEmail($email);
        $meta->setDetails($account_details);
        /** @var NostoAccount $account */
        $account = NostoAccount::create($meta);
        $id_shop = null;
        $id_shop_group = null;
        if ($this->context->shop instanceof Shop) {
            $id_shop = $this->context->shop->id;
            $id_shop_group = $this->context->shop->id_shop_group;
        }

        return NostoTaggingHelperAccount::save($account, $id_lang, $id_shop_group, $id_shop);
    }
}
