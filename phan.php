<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Nosto
 * @package   Nosto_Tagging
 * @author    Nosto Solutions Ltd <magento@nosto.com>
 * @copyright Copyright (c) 2013-2020 Nosto Solutions Ltd (http://www.nosto.com)
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

ini_set('xdebug.max_nesting_level', 5120);

return [
    'analyze_signature_compatibility' => false,
    'backward_compatibility_checks' => false,
    'exclude_file_regex' => '@^libs/.*/(tests|test|Tests|Test)/@',
	'exclude_file_list' => [
		'libs/prestashop/ps/tests-legacy/resources/ModulesOverrideInstallUninstallTest/Cart.php'
	],
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
