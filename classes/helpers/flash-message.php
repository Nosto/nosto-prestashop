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
 * Helper class for setting and retrieving persistent user flash messages.
 * Uses the Prestashop core cookie class as storage.
 */
class NostoTaggingHelperFlashMessage
{
	const TYPE_SUCCESS = 'success';
	const TYPE_ERROR = 'error';

	/**
	 * Adds a new flash message to the users cookie.
	 *
	 * @param string $type the type of message (use class constants).
	 * @param string $message the message.
	 */
	public function add($type, $message)
	{
		$cookie = Context::getContext()->cookie;
		$cookie_data = isset($cookie->nostotagging) ? Tools::jsonDecode($cookie->nostotagging, true) : array();
		if (!isset($cookie_data['flash_messages']))
			$cookie_data['flash_messages'] = array();
		if (!isset($cookie_data['flash_messages'][$type]))
			$cookie_data['flash_messages'][$type] = array();
		$cookie_data['flash_messages'][$type][] = $message;
		$cookie->nostotagging = Tools::jsonEncode($cookie_data);
	}

	/**
	 * Gets a list of all flash messages from the users cookie by type.
	 *
	 * @param string $type the type of messages (use class constants).
	 * @return array the message array.
	 */
	public function getList($type)
	{
		$flash_messages = array();
		$cookie = Context::getContext()->cookie;
		$cookie_data = isset($cookie->nostotagging) ? Tools::jsonDecode($cookie->nostotagging, true) : array();
		if (isset($cookie_data['flash_messages'][$type]))
		{
			$flash_messages = $cookie_data['flash_messages'][$type];
			unset($cookie_data['flash_messages'][$type]);
			$cookie->nostotagging = Tools::jsonEncode($cookie_data);
		}
		return $flash_messages;
	}
}
