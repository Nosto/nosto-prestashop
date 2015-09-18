<?php
/**
 * 2013-2015 Nosto Solutions Ltd
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
 * @copyright 2013-2015 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

require_once(dirname(__FILE__).'/cron.php');

/**
 * Cron controller to update currency exchange rates in Nosto.
 *
 * This controller can be called directly from the servers cron tab or, using the PS "cronjob" module.
 * The authentication is
 */
class NostoTaggingCronRatesModuleFrontController extends NostoTaggingCronModuleFrontController
{
	/**
	 * @inheritdoc
	 */
	public function initContent()
	{
		/** @var NostoTaggingHelperAccount $helper_account */
		$helper_account = Nosto::helper('nosto_tagging/account');
		/** @var NostoTaggingHelperContextFactory $factory */
		$factory = Nosto::helper('nosto_tagging/context_factory');
		/** @var NostoTaggingHelperLogger $logger */
		$logger = Nosto::helper('nosto_tagging/logger');

		$logger->info('NOSTO CRON - exchange rate sync started');

		foreach (Shop::getShops() as $shop)
		{
			$id_shop = isset($shop['id_shop']) ? (int)$shop['id_shop'] : null;
			$id_shop_group = isset($shop['id_shop_group']) ? (int)$shop['id_shop_group'] : null;
			foreach (Language::getLanguages(true, $id_shop) as $language)
			{
				$id_lang = (int)$language['id_lang'];
				$nosto_account = $helper_account->find($id_lang, $id_shop_group, $id_shop);
				if (!is_null($nosto_account))
					if ($helper_account->updateCurrencyExchangeRates($nosto_account, $factory->forgeContext($id_lang, $id_shop)))
						$logger->info(sprintf('NOSTO CRON - synced exchange rates for account %s', $nosto_account->getName()));
			}
		}

		$logger->info('NOSTO CRON - exchange rate sync finished');

		exit;
	}
}
