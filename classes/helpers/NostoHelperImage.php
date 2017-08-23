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
 * Helper class to choose the optimal image version from the list of defined image versions. This
 * class helps by choosing the image closest to a 800px width.
 */
class NostoHelperImage
{
    const OPTIMAL_PRODUCT_IMAGE_WIDTH = 800;

    /**
     * Returns all image types configured to be available for product images
     *
     * @return array
     */
    public static function getProductImageTypes()
    {
        $productImages = array();
        $imagesTypes = ImageType::getImagesTypes();
        foreach ($imagesTypes as $image_type) {
            if (!empty($image_type['products']) && $image_type['products'] == 1) {
                $productImages[] = $image_type;
            }
        }

        return $productImages;
    }

    /**
     * Chooses the "optimal" image type to use for product image urls.
     *
     * The type is chosen based on which image type has a width closest to
     * `self::OPTIMAL_PRODUCT_IMAGE_WIDTH`.
     *
     * @return false|string the image type name or false if not found.
     */
    public static function chooseOptimalImageType()
    {
        $definition = ObjectModel::getDefinition('ImageType');
        $tableName = isset($definition['table']) ? $definition['table'] : 'image_type';
        $availableImageTypes = Db::getInstance()->executeS('
			SELECT * FROM `' . pSQL(_DB_PREFIX_ . $tableName) . '`
			WHERE `products` = 1
			ORDER BY `width` ASC
		');
        $optimal = self::OPTIMAL_PRODUCT_IMAGE_WIDTH;
        $found = array();
        foreach ($availableImageTypes as $available) {
            if (empty($found) || abs($optimal - (int)$found['width']) > abs((int)$available['width'] - $optimal)) {
                $found = $available;
            }
        }
        return isset($found['name']) ? $found['name'] : false;
    }

    /**
     * Returns the image type to be used in product image tagging
     *
     * @return string
     */
    public static function getTaggingImageTypeName()
    {
        $savedImageTypeId = NostoHelperConfig::getImageType();
        if ($savedImageTypeId) {
            $image_type = new ImageType($savedImageTypeId);
            $imageTypeName = $image_type->name;
        } else {
            $imageTypeName = NostoHelperImage::chooseOptimalImageType();
        }

        return $imageTypeName;
    }
}
