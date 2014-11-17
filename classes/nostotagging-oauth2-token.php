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
 * @author    Nosto Solutions Ltd <contact@nosto.com>
 * @copyright 2013-2014 Nosto Solutions Ltd
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Helper class that represents a oauth2 access token.
 */
class NostoTaggingOAuth2Token
{
	/**
	 * @var string the access token string.
	 */
	public $access_token;

	/**
	 * @var string the merchant name string.
	 */
	public $merchant_name;

	/**
	 * @var string the type of token, e.g. "bearer".
	 */
	public $token_type;

	/**
	 * @var int the amount of time this token is valid for.
	 */
	public $expires_in;

	/**
	 * Creates a new token instance and populates it with the given data.
	 *
	 * @param array $data the data to put in the token.
	 * @return NostoTaggingOAuth2Token
	 */
	public static function create(array $data)
	{
		$token = new NostoTaggingOAuth2Token();
		foreach ($data as $key => $value)
			if (property_exists($token, $key))
				$token->{$key} = $value;
		return $token;
	}
}