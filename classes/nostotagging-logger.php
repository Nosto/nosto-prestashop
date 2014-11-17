<?php
/**
 * 2013-2014 Nosto Solutions Ltd
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
 *  @author Nosto Solutions Ltd <contact@nosto.com>
 *  @copyright  2013-2014 Nosto Solutions Ltd
 *  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for logging events to the Prestashop log.
 */
class NostoTaggingLogger
{
	const LOG_SEVERITY_INFO = 1;
	const LOG_SEVERITY_WARNING = 2;
	const LOG_SEVERITY_ERROR = 3;
	const LOG_SEVERITY_FATAL = 4;

	/**
	 * Logs an event to the Prestashop log.
	 *
	 * @param string $message the message to log.
	 * @param int $severity the log severity (use class constants).
	 * @param null|int $error_code the error code if any.
	 * @param null|string $object_type the object type if any.
	 * @param null|int $object_id the object id if any.
	 */
	public static function log($message, $severity = self::LOG_SEVERITY_INFO, $error_code = null, $object_type = null, $object_id = null)
	{
		$logger = (class_exists('PrestaShopLogger') ? 'PrestaShopLogger' : (class_exists('Logger') ? 'Logger' : null));
		if (!empty($logger))
			call_user_func(array($logger, 'addLog'), $message, $severity, $error_code, $object_type, $object_id, true);
	}
}
