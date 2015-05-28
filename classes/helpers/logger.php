<?php
/**
 * 2013-2015 Nosto Solutions Ltd
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
 * @copyright 2013-2015 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class for logging events to the Prestashop log.
 */
class NostoTaggingHelperLogger
{
	const SEVERITY_INFO = 1;
	const SEVERITY_WARNING = 2;
	const SEVERITY_ERROR = 3;
	const SEVERITY_FATAL = 4;

	/**
	 * Logs a message to the PS log.
	 *
	 * @param string $message the message.
	 * @param int $severity what kind of log to create (use class constants).
	 * @param null|int $error_code the error code.
	 * @param null|string $object_type the object type affected.
	 * @param null|int $object_id the object id affected.
	 */
	public function log($message, $severity = self::SEVERITY_INFO, $error_code = null, $object_type = null, $object_id = null)
	{
		$logger = (class_exists('PrestaShopLogger') ? 'PrestaShopLogger' : (class_exists('Logger') ? 'Logger' : null));
		if (!empty($logger))
		{
			// The log message is not allowed to contain certain characters, so we url encode them before saving.
			$message = str_replace(array('{', '}', '<', '>'), array('%7B', '%7D', '%3C', '%3E'), $message);
			call_user_func(array($logger, 'addLog'), $message, $severity, $error_code, $object_type, $object_id, true);
		}
	}

	/**
	 * Logs an error to the PS log.
	 *
	 * @param string $message the message.
	 * @param null|int $error_code the error code.
	 * @param null|string $object_type the object type affected.
	 * @param null|int $object_id the object id affected.
	 */
	public function error($message, $error_code = null, $object_type = null, $object_id = null)
	{
		$this->log($message, self::SEVERITY_ERROR, $error_code, $object_type, $object_id);
	}
}
