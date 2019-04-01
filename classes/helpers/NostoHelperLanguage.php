<?php
/**
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
 */

class NostoHelperLanguage
{
    /**
     * Gets the current admin config language data.
     *
     * @param array $languages list of valid languages.
     * @param int $languageId if a specific language is required.
     * @return array the language data array.
     */
    public static function ensureAdminLanguage(array $languages, $languageId)
    {
        foreach ($languages as $language) {
            if ($language['id_lang'] == $languageId) {
                return $language;
            }
        }

        if (isset($languages[0])) {
            return $languages[0];
        } else {
            return array('id_lang' => 0, 'name' => '', 'iso_code' => '');
        }
    }
}
