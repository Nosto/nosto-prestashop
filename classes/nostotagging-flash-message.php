<?php

/**
 * Helper class for setting and retrieving persistent user flash messages.
 * Uses the Prestashop core cookie class as storage.
 */
class NostoTaggingFlashMessage
{
	const TYPE_SUCCESS = 'success';
	const TYPE_ERROR = 'error';

	/**
	 * Adds a new flash message to the users cookie.
	 *
	 * @param string $type the type of message (use class constants).
	 * @param string $message the message.
	 */
	public static function add($type, $message)
	{
		$cookie = Context::getContext()->cookie;
		$cookie_data = isset($cookie->nostotagging) ? json_decode($cookie->nostotagging, true) : array();
		if (!isset($cookie_data['flash_messages']))
			$cookie_data['flash_messages'] = array();
		if (!isset($cookie_data['flash_messages'][$type]))
			$cookie_data['flash_messages'][$type] = array();
		$cookie_data['flash_messages'][$type][] = $message;
		$cookie->nostotagging = json_encode($cookie_data);
	}

	/**
	 * Gets a list of all flash messages from the users cookie by type.
	 *
	 * @param string $type the type of messages (use class constants).
	 * @return array the message array.
	 */
	public static function get($type)
	{
		$flash_messages = array();
		$cookie = Context::getContext()->cookie;
		$cookie_data = isset($cookie->nostotagging) ? json_decode($cookie->nostotagging, true) : array();
		if (isset($cookie_data['flash_messages'][$type]))
		{
			$flash_messages = $cookie_data['flash_messages'][$type];
			unset($cookie_data['flash_messages'][$type]);
			$cookie->nostotagging = json_encode($cookie_data);
		}
		return $flash_messages;
	}
} 