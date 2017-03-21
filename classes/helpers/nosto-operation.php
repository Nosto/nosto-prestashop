<?php
/**
 * 2013-2016 Nosto Solutions Ltd
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
 * @copyright 2013-2016 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for sending product create/update/delete events to Nosto.
 */
abstract class NostoTaggingHelperOperation
{
    /**
     * @var array stores a snapshot of the context object and shop context so it can be restored between processing all
     * accounts. This is important as the accounts belong to different shops and languages and the context, that
     * contains this information, is used internally in PrestaShop when generating urls.
     */
    protected $contextSnapshot;

    /**
     * Returns Nosto accounts based on active shops.
     *
     * The result is formatted as follows:
     *
     * array(
     *   array(object(NostoAccount), int(id_shop), int(id_lang))
     * )
     *
     * @return NostoAccount[] the account data.
     */
    protected function getAccountData()
    {
        $data = array();
        foreach ($this->getContextShops() as $shop) {
            $id_shop = (int)$shop['id_shop'];
            $id_shop_group = (int)$shop['id_shop_group'];
            foreach (LanguageCore::getLanguages(true, $id_shop) as $language) {
                $id_lang = (int)$language['id_lang'];
                $account = NostoTaggingHelperAccount::find($id_lang, $id_shop_group, $id_shop);
                if ($account === null || !$account->isConnectedToNosto()) {
                    continue;
                }

                $data[] = array($account, $id_shop, $id_lang);
            }
        }

        return $data;
    }

    /**
     * Returns the shops that are affected by the current context.
     *
     * @return array list of shop data.
     */
    protected function getContextShops()
    {
        if (Shop::isFeatureActive() && Shop::getContext() !== Shop::CONTEXT_SHOP) {
            if (Shop::getContext() === Shop::CONTEXT_GROUP) {
                return Shop::getShops(true, Shop::getContextShopGroupID());
            } else {
                return Shop::getShops(true);
            }
        } else {
            $ctx = Context::getContext();
            return array(
                (int)$ctx->shop->id => array(
                    'id_shop' => (int)$ctx->shop->id,
                    'id_shop_group' => (int)$ctx->shop->id_shop_group,
                ),
            );
        }
    }
}
