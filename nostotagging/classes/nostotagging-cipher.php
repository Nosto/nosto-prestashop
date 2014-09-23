<?php

require_once(dirname(__FILE__).'/../vendor/phpseclib/crypt/base.php');
require_once(dirname(__FILE__).'/../vendor/phpseclib/crypt/rijndael.php');
require_once(dirname(__FILE__).'/../vendor/phpseclib/crypt/aes.php');

/**
 * Helper class for encrypting/decrypting strings.
 */
class NostoTaggingCipher
{
	/**
	 * @var Crypt_Base
	 */
	private $crypt;

	/**
	 * Constructor.
	 */
	public function __construct($secret, $iv = null)
	{
		$this->crypt = new Crypt_AES();
		$this->crypt->setKey($secret);
		if (!empty($iv))
			$this->crypt->setIV($iv);
	}

	/**
	 * Encrypts the string an returns iv.encrypted.
	 *
	 * @param string $plain_text the string to encrypt.
	 * @return string the encrypted string.
	 */
	public function encrypt($plain_text)
	{
		return $this->crypt->encrypt($plain_text);
	}
}
