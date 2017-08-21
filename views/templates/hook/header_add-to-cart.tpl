{*
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
*}

<script type="text/javascript">
    {literal}
    if (typeof Nosto === "undefined") {
        var Nosto = {};
    }
    {/literal}
    Nosto.addProductToCart = function (productId, element) {
        var productData = {
            "productId": productId
        };
        Nosto.addSkuToCart(productData, element);
    };

    //Product object must have fields productId and skuId productId: 123, skuId: 321
    Nosto.addSkuToCart = function (product, element) {
        if (typeof nostojs !== 'undefined' && typeof element === 'object') {
            var slotId = Nosto.resolveContextSlotId(element);
            if (slotId) {
                nostojs(function (api) {
                    api.recommendedProductAddedToCart(productId, slotId);
                });
            }
        }

        //ajaxCart is prestashop object
        if (ajaxCart && ajaxCart.add && $('.cart_block').length) {
            try {
                ajaxCart.add(product.productId, product.skuId, true, null, 1, null);

                return;//done with ajax way
            } catch (e) {
                console.log(e);
            }
        }

        //if ajax way failed, submit a form to add it to cart
        var hiddenFields = {
            "qty": 1,
            "controller": "cart",
            "id_product": product.productId,
            "ipa": product.skuId,
            "add": 1,
            "token": "{$static_token|escape:"javascript":"UTF-8"}"
        };
        Nosto.postAddToCartForm(hiddenFields, "{$add_to_cart_url|escape:"javascript":"UTF-8"}");
    };

    Nosto.postAddToCartForm = function (data, url) {

        var form = document.createElement("form");
        form.setAttribute("method", "post");
        form.setAttribute("action", url);

        for (var key in data) {
            if (data.hasOwnProperty(key)) {
                var hiddenField = document.createElement("input");
                hiddenField.setAttribute("type", "hidden");
                hiddenField.setAttribute("name", key);
                hiddenField.setAttribute("value", data[key]);
                form.appendChild(hiddenField);
            }
        }

        document.body.appendChild(form);
        form.submit();
    };

    Nosto.resolveContextSlotId = function (element) {
        if (!element) {
            return false;
        }
        var m = 20;
        var n = 0;
        var e = element;
        while (typeof e.parentElement !== "undefined" && e.parentElement) {
            ++n;
            e = e.parentElement;
            if (e.getAttribute('class') === 'nosto_element' && e.getAttribute('id')) {
                return e.getAttribute('id');
            }
            if (n >= m) {
                return false;
            }
        }
        return false;
    }

</script>
