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
 * Created by PhpStorm.
 * User: hannupolonen
 * Date: 18/05/16
 * Time: 12:52
 */

/**
 * Meta data class for account related information needed when creating new accounts.
 */
class NostoTaggingCollectionExchangeRates extends NostoExchangeRateCollection
{
    public function __construct($input, $flags = 0, $iterator_class = "ArrayIterator")
    {
        parent::__construct($input, $flags, $iterator_class);
        
        $this->dispatchHook(
            'actionNostoExchangeRatesLoadAfter',
            array(
                'nosto_exchange_rates' => $this,
            )
        );
    }

    /**
     * Executes a PS hook by name.
     *
     * Abstracts the differences between PS versions.
     *
     * @param string $name the hook name.
     * @param array $params the hook params.
     */
    private function dispatchHook($name, array $params)
    {
        Hook::exec($name, $params);
    }
}
