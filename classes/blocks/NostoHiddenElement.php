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
class NostoHiddenElement
{
    const HIDDEN_ELEMENT = '<div class="hidden_nosto_element" data-nosto-id="%s" nosto_insert_position="%s"></div>';
    const INSERT_POSITION_PREPEND = 'prepend';
    const INSERT_POSITION_APPEND = 'append';

    /**
     * Renders a single hidden element using the identifier specifie.
     * The hidden elements will be appended to the center_column block by js
     *
     * @param string $nostoDataId the identifier of the hidden element
     * @return string the tagging
     */
    public static function append($nostoDataId)
    {
        if (!Nosto::isContextConnected()) {
            return '';
        }

        return sprintf(self::HIDDEN_ELEMENT, $nostoDataId, self::INSERT_POSITION_APPEND);
    }

    /**
     * Renders a single hidden element using the identifier specified
     * The hidden elements will be prepended to the center_column block by js
     *
     * @param string $nostoDataId the identifier of the hidden element
     * @return string the tagging
     */
    public static function prepend($nostoDataId)
    {
        if (!Nosto::isContextConnected()) {
            return '';
        }

        return sprintf(self::HIDDEN_ELEMENT, $nostoDataId, self::INSERT_POSITION_PREPEND);
    }
}
