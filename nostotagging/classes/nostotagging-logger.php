<?php

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
