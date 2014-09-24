<?php

require_once(dirname(__FILE__).'/../vendor/phpseclib/crypt/base.php');
require_once(dirname(__FILE__).'/../vendor/phpseclib/crypt/rijndael.php');
require_once(dirname(__FILE__).'/../vendor/phpseclib/crypt/aes.php');
require_once(dirname(__FILE__).'/nostotagging-security.php');

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
	 *
	 * @param string $secret the secret key to encrypt with.
	 */
	public function __construct($secret)
	{
		$this->crypt = new Crypt_AES(CRYPT_AES_MODE_CBC);
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
		$iv = $this->crypt->iv;
		$cipher_text = $this->crypt->encrypt($plain_text);
		return $iv.$cipher_text;
	}
}
