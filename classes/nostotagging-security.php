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
 * Helper class for generating cryptographically secure random strings.
 */
class NostoTaggingSecurity
{
	/**
	 * Generates a random string of given length.
	 * If either 'OpenSSL', 'Mcrypt' or '/dev/urandom' is not present, the result is not cryptographically secure.
	 *
	 * @param int $length the length of the generated string.
	 * @return string the generated random string.
	 */
	public static function rand($length)
	{
		// Primary choice for a cryptographic strong randomness function is openssl_random_pseudo_bytes.
		// The openssl_random_pseudo_bytes function comes bundled with php >= 5.3.0.
		if (function_exists('openssl_random_pseudo_bytes'))
		{
			$rnd = openssl_random_pseudo_bytes($length, $strong);
			if ($strong)
				return $rnd;
		}

		// Secondary choice is the mcrypt extension.
		if (function_exists('mcrypt_create_iv'))
		{
			$rnd = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
			if ($rnd !== false)
				return $rnd;
		}

		// Third choice is /dev/urandom
		if (file_exists('/dev/urandom') && is_readable('/dev/urandom'))
		{
			if (($fp = fopen('/dev/urandom', 'rb')) !== false)
			{
				if (function_exists('stream_set_read_buffer'))
					stream_set_read_buffer($fp, 0);

				$rnd = fread($fp, $length);
				fclose($fp);
				if ($rnd !== false)
					return $rnd;
			}
		}

		// If all else fails, fall back on some week entropy pseudo randomness.
		$rnd = '';
		do
		{
			$entropy = rand().uniqid(mt_rand(), true);
			$rnd .= hash('sha256', $entropy, true);
			$len = Tools::strlen($rnd);
		} while ($length > $len);
		return Tools::substr($rnd, 0, $length);
	}
} 