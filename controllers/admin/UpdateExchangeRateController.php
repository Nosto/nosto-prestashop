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

class UpdateExchangeRateController extends NostoBaseController
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        $language_id = $this->getLanguageId();

        /** @var NostoTaggingHelperFlashMessage $flashHelper */
        $flashHelper = Nosto::helper('nosto_tagging/flash_message');

        $shopId = null;
        $shopGroupId = null;
        if ($this->context->shop instanceof Shop) {
            $shopId = $this->context->shop->id;
            $shopGroupId = $this->context->shop->id_shop_group;
        }

        $nosto_account = NostoTaggingHelperAccount::find($language_id, $shopGroupId, $shopId);
        if ($nosto_account &&
            NostoTaggingHelperAccount::updateCurrencyExchangeRates(
                $nosto_account,
                $this->context
            )
        ) {
            $flashHelper->add(
                'success',
                $this->l(
                    'Exchange rates successfully updated to Nosto'
                )
            );
        } else {
            if (!$nosto_account->getApiToken(NostoApiToken::API_EXCHANGE_RATES)) {
                $message = 'Failed to update exchange rates to Nosto due to a missing API token. 
                            Please, reconnect your account with Nosto';
            } else {
                $message = 'There was an error updating the exchange rates. 
                            See Prestashop logs for more information.';
            }
            $flashHelper->add(
                'error',
                $this->l($message)
            );
        }

        return true;
    }
}
