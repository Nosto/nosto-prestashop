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

<!--suppress JSUnresolvedFunction -->
<script type="text/javascript">
    import * as prestashop from "../../../libs/prestashop/ps/admin-dev/filemanager/js/imagesloaded.pkgd.min";

    nostojs(function(){
        window.Nosto = window.Nosto || {};
        Nosto.reloadCartTagging = function () {
            if (window.jQuery) {
                jQuery.ajax({
                    url: decodeURIComponent("{$reload_cart_url|escape:'url'}")
                }).done(function (data) {
                    // noinspection JSJQueryEfficiency
                  if (jQuery('.nosto_cart').length > 0) {
                        jQuery('.nosto_cart').replaceWith(data);
                    } else {
                        jQuery('body').append(data);
                    }

                    //resend cart tagging and reload recommendations
                    if (typeof nostojs === 'function') {
                        nostojs(function (api) {
                            api.resendCartTagging();
                            api.loadRecommendations();
                        });
                    }
                });
            }
        };

      let maxTry = 60;
      const waitForJQuery = function () {
        if (window.jQuery) {
          //On prestashop 1.7+, use prestashop built-in js object
          if (window.prestashop && prestashop._events && prestashop._events.updateCart) {
            prestashop.on(
              'updateCart',
              function () {
                Nosto.reloadCartTagging();
              }
            );
          } else {
            jQuery(document).ajaxComplete(function (event, xhr, settings) {
              if (!settings || settings.crossDomain) {
                return;
              }
              //check controller
              if ((!settings.data || settings.data.indexOf('controller=cart') < 0)
                && (settings.url.indexOf('controller=cart') < 0)) {
                return;
              }

              //reload cart tagging
              Nosto.reloadCartTagging();
            });
          }
        } else if (maxTry > 0) {
          //jQuery is loaded to the page after nosto scripts on prestashop 1.7
          //wait for it
          maxTry--;
          setTimeout(waitForJQuery, 500);
        }
      };
      waitForJQuery();
    });

</script>
