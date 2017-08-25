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
class NostoContextIterator implements IteratorAggregate
{
    private $scopes = array();

    public function __construct($connected = true)
    {
        $this->scopes = array();
        foreach (Shop::getShops() as $shop) {
            $id_shop = isset($shop['id_shop']) ? $shop['id_shop'] : null;
            foreach (Language::getLanguages(true, $id_shop) as $language) {
                $id_shop_group = isset($shop['id_shop_group']) ? $shop['id_shop_group'] : null;
                NostoHelperContext::runWithEachNostoAccount(function()
                {
                    $account = NostoHelperAccount::find();
                    if ($account !== null) {
                        $this->scopes[] = $account;
                    }
                });
            }
        }

        return true;
    }

    public function getIterator()
    {
        return new ArrayIterator($this->scopes);
    }
}