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

require_once(dirname(__FILE__).'/../libs/phpseclib/crypt/base.php');
require_once(dirname(__FILE__).'/../libs/phpseclib/crypt/rijndael.php');
require_once(dirname(__FILE__).'/../libs/phpseclib/crypt/aes.php');
require_once(dirname(__FILE__).'/nostotagging-security.php');

/**
 * Helper class for encrypting/decrypting strings.
 */
class NostoTaggingCipher
{
	/**
	 * @var CryptBase
	 */
	private $crypt;

	/**
	 * Constructor.
	 *
	 * @param string $secret the secret key to encrypt with.
	 */
	public function __construct($secret)
	{
		$this->crypt = new CryptAES(CRYPT_AES_MODE_CBC);
		$this->crypt->setKey($secret);
		// AES has a fixed block size of 128 bytes
		$this->crypt->setIV(NostoTaggingSecurity::rand(16));
	}

	/**
	 * Encrypts the string an returns iv.encrypted.
	 *
	 * @param string $plain_text the string to encrypt.
	 * @return string the encrypted string.
	 */
	public function encrypt($plain_text)
	{
		$iv = $this->crypt->getIV();
		$cipher_text = $this->crypt->encrypt($plain_text);
		return $iv.$cipher_text;
	}

	/**
	 * Decrypts the string and returns the plain text.
	 *
	 * @param string $cipher_text the encrypted cipher.
	 * @return string the decrypted plain text string.
	 */
	public function decrypt($cipher_text)
	{
		// Assume the first 16 chars is the IV.
		$iv = Tools::substr($cipher_text, 0, 16);
		$this->crypt->setIV($iv);
		$plain_text = $this->crypt->decrypt(Tools::substr($cipher_text, 16));
		return $plain_text;
	}
}
