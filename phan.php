<?php
/**
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
 * @copyright 2013-2019 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

ini_set('xdebug.max_nesting_level', 5120);

return [
    'analyze_signature_compatibility' => false,
    'backward_compatibility_checks' => false,
    'exclude_file_regex' => '@^libs/.*/(tests|test|Tests|Test)/@',
    'directory_list' => [
        'classes',
        'controllers',
        'upgrade',
        'views',
         '.phan',
         'libs'
    ],
    "exclude_analysis_directory_list" => [
        '.phan',
        'libs'
    ],
    "file_list" => [
        'nostotagging.php'
    ],
    "color_issue_messages_if_supported" => true
];
