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

use Nosto\Model\Signup\Account as NostoSDKAccount;
use Nosto\Model\Event\Cart\Update as NostoSDKCartUpdate;
use Nosto\Model\Cart\LineItem as NostoSDKCartItem;
use Nosto\Operation\CartOperation as NostoSDKCartOperation;
use Nosto\Helper\SerializationHelper as NostoSDKSerializationHelper;

/**
 * Helper class for sending cart data to Nosto.
 */
class NostoCartService extends AbstractNostoService
{
    const COOKIE_NAME = 'nosto.itemsAddedToCart';

    public static $addedItems = array();

    const PRODUCT = 'product';

    const QUANTITY = 'quantity';

    const OPERATOR = 'operator';

    const OPERATOR_UP = 'up';

    const ID_PRODUCT_ATTRIBUTE = 'id_product_attribute';

    /**
     * Cart updated event handler. It is called after cart updated.
     * @param array $params event parameters
     */
    public function cartUpdated($params)
    {
        try {
            if (!NostoTagging::isEnabled(NostoTagging::MODULE_NAME)) {
                return;
            }

            $account = NostoHelperAccount::getAccount();
            if (!$account instanceof NostoSDKAccount || !$account->isConnectedToNosto()) {
                return;
            }

            //Send the event only if there was item added to the cart
            if (!self::$addedItems) {
                return;
            }

            $cartUpdate = new NostoSDKCartUpdate();
            $cartUpdate->setAddedItems(self::$addedItems);

            if (!headers_sent()) {
                setcookie(
                    self::COOKIE_NAME,
                    NostoSDKSerializationHelper::serialize($cartUpdate),
                    time() + 60,
                    '/',
                    '',
                    false,
                    false
                );
            } else {
                NostoHelperLogger::info('Headers sent already. Cannot set the cookie.');
            }

            if (NostoHelperConfig::isCartUpdateEnabled()) {
                $nostoCustomerId = NostoHelperCookie::readNostoCookie();
                if (!$nostoCustomerId) {
                    NostoHelperLogger::info('Cannot find customer id from cookie');

                    return;
                }

                if (!$params || !isset($params['cart']) || !$params['cart'] instanceof Cart) {
                    NostoHelperLogger::info('Cannot find cart from event');

                    return;
                }

                $cart = $params['cart'];

                //restore cart hash must be generated before sending the cart to nosto
                NostoCustomerManager::updateNostoId();

                $nostoCart = NostoCart::loadData($cart);
                $cartUpdate->setCart($nostoCart);

                if ($nostoCart instanceof NostoCart) {
                    $hcid = hash(NostoTagging::VISITOR_HASH_ALGO, $nostoCustomerId);
                    $nostoCart->setHcid($hcid);
                }
                $service = new NostoSDKCartOperation($account);
                $service->updateCart($cartUpdate, $nostoCustomerId, $account->getName());
            }
        } catch (\Exception $e) {
            NostoHelperLogger::error($e);
        }
    }

    /**
     * Cart item quantity change event handler. It is called before the cart has been saved
     * @param array $params event parameters
     */
    public function cartItemQuantityChanged($params)
    {
        try {
            if (!NostoTagging::isEnabled(NostoTagging::MODULE_NAME)) {
                return;
            }

            $account = NostoHelperAccount::getAccount();
            if (!$account instanceof NostoSDKAccount || !$account->isConnectedToNosto()) {
                return;
            }

            if (!$params || !isset($params[self::PRODUCT]) || !$params[self::PRODUCT] instanceof Product) {
                return;
            }
            /** @var Product $product */
            $product = $params[self::PRODUCT];

            if ($params[self::QUANTITY] && $params[self::OPERATOR] == self::OPERATOR_UP) {
                $nostoLineItem = new NostoSDKCartItem();
                $nostoLineItem->setProductId(strval($product->id));
                $nostoLineItem->setSkuId($params[self::ID_PRODUCT_ATTRIBUTE]);
                $nostoLineItem->setQuantity((int)$params[self::QUANTITY]);
                $nostoLineItem->setName($product->name);

                self::$addedItems[] = $nostoLineItem;
            }
        } catch (\Exception $e) {
            NostoHelperLogger::error($e);
        }
    }
}
