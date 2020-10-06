/*
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

// noinspection JSUnresolvedFunction
$(document).ready(function () {
    // Change event handler for "Manage Accounts:".
    // noinspection JSUnresolvedFunction
    $("#nostotagging_language").change(function () {
      // noinspection JSUnresolvedFunction,ES6ConvertVarToLetConst
        var langId = parseInt($(this).val()),
            $currentLanguage = $('#nostotagging_current_language'),
            $form = $('form.nostotagging');
        // noinspection JSUnresolvedFunction
        $currentLanguage.val(langId);
        $form.submit();
    });

    // Toggle the Nosto settings
    // noinspection JSCheckFunctionSignatures,JSUnresolvedFunction
    $("#nostotagging_account_setup").click(function (event) {
        event.preventDefault();
        // noinspection JSCheckFunctionSignatures,JSUnresolvedFunction
        $('div.nostotagging_settings').toggle();
        // noinspection JSCheckFunctionSignatures,JSUnresolvedFunction
        $('#nostotagging_iframe').toggle();
    });

    // Init the iframe re-sizer.
    // noinspection JSUnresolvedFunction
    $('#nostotagging_iframe').iFrameResize({heightCalculationMethod: 'bodyScroll'});
});
