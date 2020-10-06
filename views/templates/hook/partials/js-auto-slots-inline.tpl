{*
* 2013-2019 Nosto Solutions Ltd
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
* @copyright 2013-2019 Nosto Solutions Ltd
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*}
<!--suppress JSUnresolvedFunction, ES6ConvertVarToLetConst -->
<script type="text/javascript">
    var nostoRecosLoaded = false;
    // noinspection JSUnresolvedFunction
    nostojs(function (api) {
        api.listen('postrender', function () {
            nostoRecosLoaded = true;
        });
        var maxTriesForJquery = 60;
        var waitForJQuery = function () {
            if (window.jQuery) {
                var placeSlots = function () {
                    var $center_column = jQuery('#center_column, #content-wrapper');
                    var $hidden_elements = jQuery('.hidden_nosto_element');
                    var slotsMoved = false;
                    if ($center_column.length && $hidden_elements.length) {
                        $hidden_elements.each(function () {
                            var $slot = jQuery(this), nostoId = $slot.data('nosto-id');
                            if (nostoId && !jQuery('#' + nostoId).length) {
                                $slot.attr('id', nostoId);
                                $slot.attr('class', 'nosto_element');
                                if ($slot.attr('nosto_insert_position') === 'prepend') {
                                    $slot.prependTo($center_column);
                                } else {
                                    $slot.appendTo($center_column);
                                }
                                slotsMoved = true;
                            }
                        });
                        if (slotsMoved && nostoRecosLoaded) {
                            api.loadRecommendations();
                        }
                    }
                };
                jQuery(document).ready(placeSlots);
            } else if (maxTriesForJquery > 0) {
                //jQuery is loaded to the page after nosto scripts on prestashop 1.7
                //wait for it
                maxTriesForJquery--;
                setTimeout(waitForJQuery, 500);
            }
        };
        waitForJQuery();
    });
</script>
