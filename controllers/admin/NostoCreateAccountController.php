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

require_once 'NostoBaseController.php';

/**
 * Class CreateAccountController
 *
 * @property Context $context
 * @noinspection PhpUnused
 */
class NostoCreateAccountController extends NostoBaseController
{
    /**
     * @inheritdoc
     *
     * @suppress PhanDeprecatedFunction
     * @noinspection PhpUnused
     */
    public function execute()
    {
        try {
            $accountDetails = json_decode(Tools::getValue('details'));
            $accountEmail = $accountDetails->email;
            if (empty($accountEmail)) {
                /** @noinspection PhpDeprecationInspection */
                NostoHelperFlash::add('error', $this->l('Email cannot be empty.'));
            } elseif (!Validate::isEmail($accountEmail)) {
                /** @noinspection PhpDeprecationInspection */
                NostoHelperFlash::add('error', $this->l('Email is not a valid email address.'));
            } else {
                $service = new NostoSignupService();
                $service->createAccount($accountEmail, $accountDetails);

                NostoHelperConfig::clearCache();
                /** @noinspection PhpDeprecationInspection */
                NostoHelperFlash::add(
                    'success',
                    $this->l(
                        'Account created. Please check your email and follow the instructions to set a'
                        . ' password for your new account within three days.'
                    )
                );

                Tools::redirectAdmin(NostoHelperUrl::getFullAdminControllerUrl('NostoOpenAccount', $this->getLanguageId()));
            }
        } catch (Exception $e) {
            /** @noinspection PhpDeprecationInspection */
            NostoHelperFlash::add(
                'error',
                $this->l('Account could not be automatically created. Please see logs for details.')
            );
            NostoHelperLogger::error($e, 'Creating Nosto account failed');
        }

        return true;
    }
}
