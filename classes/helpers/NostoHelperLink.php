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

/**
 * Helper class to build secure or unsecure link objects used for building all back office, front
 * and preview URLs. This class helps by taking care of figuring out the secure mode of the store.
 */
class NostoHelperLink
{
    const PS_SSL_ENABLED_EVERYWHERE = 'PS_SSL_ENABLED_EVERYWHERE';

    /**
     * Returns a secure link object if SSL is enabled else falls back to the unsecured
     * link. The link object from the context is never used as that would cause the
     * URLs to flip-flop between secured and unsecured depending upon whether the
     * site is being viewed in a secured or unsecured mode.
     *
     * @return Link the secured or unsecured link object for all site URLs
     */
    public static function getLink()
    {
        if (Configuration::get(self::PS_SSL_ENABLED_EVERYWHERE)) {
            $link = new Link('https://', 'https://');
        } else {
            $link = new Link('http://', 'http://');
        }

        return $link;
    }

    /**
     * Returns a link to a product image for display
     * Note: the new image filesystem stores product images in subdirectories of img/p/.
     *
     * @param string $rewrite rewrite link of the image
     * @param string $ids  id part of the image filename - can be
     * "id_product-id_image" (legacy support, recommended) or "id_image" (new)
     * @param string|null $type null means original image
     *
     * @return string
     * @suppress PhanTypeMismatchArgument
     */
    public static function getImageLink($rewrite, $ids, $type = null)
    {
        $link = NostoHelperLink::getLink();

        return $link->getImageLink($rewrite, $ids, $type);
    }
}
