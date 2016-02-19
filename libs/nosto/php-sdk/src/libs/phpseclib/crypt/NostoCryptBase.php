<?php
/**
 * Base Class for all NostoCrypt* cipher classes
 *
 * PHP versions 5
 *
 * Internally for phpseclib developers:
 *  If you plan to add a new cipher class, please note following rules:
 *
 *  - The new Crypt* cipher class should extend NostoCryptBase
 *
 *  - Following methods are then required to be overridden/overloaded:
 *
 *    - encryptBlock()
 *
 *    - decryptBlock()
 *
 *    - setupKey()
 *
 *  - All other methods are optional to be overridden/overloaded
 *
 *  - Look at the source code of the current ciphers how they extend NostoCryptBase
 *    and take one of them as a start up for the new cipher class.
 *
 *  - Please read all the other comments/notes/hints here also for each class var/method
 *
 * LICENSE: Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * @category   Crypt
 * @package    NostoCryptBase
 * @author     Jim Wigginton <terrafrost@php.net>
 * @author     Hans-Juergen Petrich <petrich@tronic-media.com>
 * @copyright  MMVII Jim Wigginton
 * @license    http://www.opensource.org/licenses/mit-license.html  MIT License
 * @version    1.0.1
 * @link       http://phpseclib.sourceforge.net
 */

/**
 * Encrypt / decrypt using the Counter mode.
 *
 * Set to -1 since that's what Crypt/Random.php uses to index the CTR mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Counter_.28CTR.29
 */
if (!defined('CRYPT_MODE_CTR')) {
    define('CRYPT_MODE_CTR', -1);
}
/**
 * Encrypt / decrypt using the Electronic Code Book mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Electronic_codebook_.28ECB.29
 */
if (!defined('CRYPT_MODE_ECB')) {
    define('CRYPT_MODE_ECB', 1);
}
/**
 * Encrypt / decrypt using the Code Book Chaining mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher-block_chaining_.28CBC.29
 */
if (!defined('CRYPT_MODE_CBC')) {
    define('CRYPT_MODE_CBC', 2);
}
/**
 * Encrypt / decrypt using the Cipher Feedback mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Cipher_feedback_.28CFB.29
 */
if (!defined('CRYPT_MODE_CFB')) {
    define('CRYPT_MODE_CFB', 3);
}
/**
 * Encrypt / decrypt using the Output Feedback mode.
 *
 * @link http://en.wikipedia.org/wiki/Block_cipher_modes_of_operation#Output_feedback_.28OFB.29
 */
if (!defined('CRYPT_MODE_OFB')) {
    define('CRYPT_MODE_OFB', 4);
}
/**
 * Encrypt / decrypt using streaming mode.
 *
 */
if (!defined('CRYPT_MODE_STREAM')) {
    define('CRYPT_MODE_STREAM', 5);
}

/**
 * Base value for the internal implementation $engine switch
 */
if (!defined('CRYPT_MODE_INTERNAL')) {
    define('CRYPT_MODE_INTERNAL', 1);
}
/**
 * Base value for the mcrypt implementation $engine switch
 */
if (!defined('CRYPT_MODE_MCRYPT')) {
    define('CRYPT_MODE_MCRYPT', 2);
}

/**
 * NostoUndefinedMethodException
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @version 0.3.5
 * @package NostoCryptBase
 */
class NostoUndefinedMethodException extends Exception
{
}

/**
 * NostoInvalidLengthException
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @version 0.3.5
 * @package NostoCryptBase
 */
class NostoInvalidLengthException extends Exception
{
}

/**
 * NostoInvalidPaddingException
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @version 0.3.5
 * @package NostoCryptBase
 */
class NostoInvalidPaddingException extends Exception
{
}

/**
 * Base Class for all Crypt* cipher classes
 *
 * @author  Jim Wigginton <terrafrost@php.net>
 * @author  Hans-Juergen Petrich <petrich@tronic-media.com>
 * @version 1.0.0
 * @package NostoCryptBase
 */
abstract class NostoCryptBase
{
    /**
     * The Encryption Mode
     *
     * @see NostoCryptBase::NostoCryptBase()
     * @var Integer
     */
    public $mode;

    /**
     * The Block Length of the block cipher
     *
     * @var Integer
     */
    public $blockSize = 16;

    /**
     * The Key
     *
     * @see NostoCryptBase::setKey()
     * @var String
     */
    public $key = "\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0";

    /**
     * The Initialization Vector
     *
     * @see NostoCryptBase::setIV()
     * @var String
     */
    public $iv;

    /**
     * A "sliding" Initialization Vector
     *
     * @see NostoCryptBase::enableContinuousBuffer()
     * @see NostoCryptBase::clearBuffers()
     * @var String
     */
    public $encryptIV;

    /**
     * A "sliding" Initialization Vector
     *
     * @see NostoCryptBase::enableContinuousBuffer()
     * @see NostoCryptBase::clearBuffers()
     * @var String
     */
    public $decryptIV;

    /**
     * Continuous Buffer status
     *
     * @see NostoCryptBase::enableContinuousBuffer()
     * @var Boolean
     */
    public $continuousBuffer = false;

    /**
     * Encryption buffer for CTR, OFB and CFB modes
     *
     * @see NostoCryptBase::encrypt()
     * @see NostoCryptBase::clearBuffers()
     * @var Array
     */
    public $enBuffer;

    /**
     * Decryption buffer for CTR, OFB and CFB modes
     *
     * @see NostoCryptBase::decrypt()
     * @see NostoCryptBase::clearBuffers()
     * @var Array
     */
    public $deBuffer;

    /**
     * mcrypt resource for encryption
     *
     * The mcrypt resource can be recreated every time something needs to be created or it can be created just once.
     * Since mcrypt operates in continuous mode, by default, it'll need to be recreated when in non-continuous mode.
     *
     * @see NostoCryptBase::encrypt()
     * @var Resource
     */
    public $enMcrypt;

    /**
     * mcrypt resource for decryption
     *
     * The mcrypt resource can be recreated every time something needs to be created or it can be created just once.
     * Since mcrypt operates in continuous mode, by default, it'll need to be recreated when in non-continuous mode.
     *
     * @see NostoCryptBase::decrypt()
     * @var Resource
     */
    public $deMcrypt;

    /**
     * Does the enMcrypt resource need to be (re)initialized?
     *
     * @see CryptTwofish::setKey()
     * @see CryptTwofish::setIV()
     * @var Boolean
     */
    public $enChanged = true;

    /**
     * Does the deMcrypt resource need to be (re)initialized?
     *
     * @see CryptTwofish::setKey()
     * @see CryptTwofish::setIV()
     * @var Boolean
     */
    public $deChanged = true;

    /**
     * mcrypt resource for CFB mode
     *
     * mcrypt's CFB mode, in (and only in) buffered context,
     * is broken, so phpseclib implements the CFB mode by it self,
     * even when the mcrypt php extension is available.
     *
     * In order to do the CFB-mode work (fast) phpseclib
     * use a separate ECB-mode mcrypt resource.
     *
     * @link http://phpseclib.sourceforge.net/cfb-demo.phps
     * @see NostoCryptBase::encrypt()
     * @see NostoCryptBase::decrypt()
     * @see NostoCryptBase::setupMcrypt()
     * @var Resource
     */
    public $ecb;

    /**
     * Optimizing value while CFB-encrypting
     *
     * Only relevant if $continuousBuffer enabled
     * and $engine == CRYPT_MODE_MCRYPT
     *
     * It's faster to re-init $enMcrypt if
     * $buffer bytes > $cfbInitLen than
     * using the $ecb resource furthermore.
     *
     * This value depends of the chosen cipher
     * and the time it would be needed for it's
     * initialization [by mcrypt_generic_init()]
     * which, typically, depends on the complexity
     * on its internally Key-expanding algorithm.
     *
     * @see NostoCryptBase::encrypt()
     * @var Integer
     */
    public $cfbInitLen = 600;

    /**
     * Does internal cipher state need to be (re)initialized?
     *
     * @see setKey()
     * @see setIV()
     * @see disableContinuousBuffer()
     * @var Boolean
     */
    public $changed = true;

    /**
     * Padding status
     *
     * @see NostoCryptBase::enablePadding()
     * @var Boolean
     */
    public $padding = true;

    /**
     * Is the mode one that is paddable?
     *
     * @see NostoCryptBase::NostoCryptBase()
     * @var Boolean
     */
    public $paddable = false;

    /**
     * Holds which crypt engine internaly should be use,
     * which will be determined automatically on __construct()
     *
     * Currently available $engines are:
     * - CRYPT_MODE_MCRYPT   (fast, php-extension: mcrypt, extension_loaded('mcrypt') required)
     * - CRYPT_MODE_INTERNAL (slower, pure php-engine, no php-extension required)
     *
     * In the pipeline... maybe. But currently not available:
     * - CRYPT_MODE_OPENSSL  (very fast, php-extension: openssl, extension_loaded('openssl') required)
     *
     * If possible, CRYPT_MODE_MCRYPT will be used for each cipher.
     * Otherwise CRYPT_MODE_INTERNAL
     *
     * @see NostoCryptBase::encrypt()
     * @see NostoCryptBase::decrypt()
     * @var Integer
     */
    public $engine;

    /**
     * The mcrypt specific name of the cipher
     *
     * Only used if $engine == CRYPT_MODE_MCRYPT
     *
     * @link http://www.php.net/mcrypt_module_open
     * @link http://www.php.net/mcrypt_list_algorithms
     * @see NostoCryptBase::setupMcrypt()
     * @var String
     */
    public $cipherNameMcrypt;

    /**
     * The default password keySize used by setPassword()
     *
     * @see NostoCryptBase::setPassword()
     * @var Integer
     */
    public $passwordKeySize = 32;

    /**
     * The default salt used by setPassword()
     *
     * @see NostoCryptBase::setPassword()
     * @var String
     */
    public $passwordDefaultSalt = 'phpseclib/salt';

    /**
     * The namespace used by the cipher for its constants.
     *
     * ie: AES.php is using CRYPT_AES_MODE_* for its constants
     *     so $constNamespace is AES
     *
     *     DES.php is using CRYPT_DES_MODE_* for its constants
     *     so $constNamespace is DES... and so on
     *
     * All CRYPT_<$constNamespace>_MODE_* are aliases of
     * the generic CRYPT_MODE_* constants, so both could be used
     * for each cipher.
     *
     * Example:
     * $aes = new CryptAES(CRYPT_AES_MODE_CFB); // $aes will operate in cfb mode
     * $aes = new CryptAES(CRYPT_MODE_CFB);     // identical
     *
     * @see NostoCryptBase::NostoCryptBase()
     * @var String
     */
    public $constNamespace;

    /**
     * The name of the performance-optimized callback function
     *
     * Used by encrypt() / decrypt()
     * only if $engine == CRYPT_MODE_INTERNAL
     *
     * @see NostoCryptBase::encrypt()
     * @see NostoCryptBase::decrypt()
     * @see NostoCryptBase::setupInlineCrypt()
     * @see useInlineCrypt::$use_inline_crypt
     * @var Callback
     */
    public $inlineCrypt;

    /**
     * Holds whether performance-optimized $inlineCrypt() can/should be used.
     *
     * @see NostoCryptBase::encrypt()
     * @see NostoCryptBase::decrypt()
     * @see NostoCryptBase::inlineCrypt
     * @var mixed
     */
    public $useInlineCrypt;

    /**
     * Default Constructor.
     *
     * Determines whether or not the mcrypt extension should be used.
     *
     * $mode could be:
     *
     * - CRYPT_MODE_ECB
     *
     * - CRYPT_MODE_CBC
     *
     * - CRYPT_MODE_CTR
     *
     * - CRYPT_MODE_CFB
     *
     * - CRYPT_MODE_OFB
     *
     * (or the alias constants of the chosen cipher, for example for AES: CRYPT_AES_MODE_ECB or CRYPT_AES_MODE_CBC ...)
     *
     * If not explicitly set, CRYPT_MODE_CBC will be used.
     *
     * @param optional Integer $mode
     */
    public function __construct($mode = CRYPT_MODE_CBC)
    {
        $constCryptMode = 'CRYPT_'.$this->constNamespace.'_MODE';

        // Determining the availability of mcrypt support for the cipher
        if (!defined($constCryptMode)) {
            switch (true) {
                case extension_loaded('mcrypt') && in_array($this->cipherNameMcrypt, mcrypt_list_algorithms()):
                    define($constCryptMode, CRYPT_MODE_MCRYPT);
                    break;
                default:
                    define($constCryptMode, CRYPT_MODE_INTERNAL);
            }
        }

        // Determining which internal $engine should be used.
        // The fastest possible first.
        switch (true) {
            // The cipher module has no mcrypt-engine support at all so we force CRYPT_MODE_INTERNAL
            case empty($this->cipherNameMcrypt):
                $this->engine = CRYPT_MODE_INTERNAL;
                break;
            case constant($constCryptMode) == CRYPT_MODE_MCRYPT:
                $this->engine = CRYPT_MODE_MCRYPT;
                break;
            default:
                $this->engine = CRYPT_MODE_INTERNAL;
        }

        // $mode dependent settings
        switch ($mode) {
            case CRYPT_MODE_ECB:
                $this->paddable = true;
                $this->mode = $mode;
                break;
            case CRYPT_MODE_CTR:
            case CRYPT_MODE_CFB:
            case CRYPT_MODE_OFB:
            case CRYPT_MODE_STREAM:
                $this->mode = $mode;
                break;
            case CRYPT_MODE_CBC:
            default:
                $this->paddable = true;
                $this->mode = CRYPT_MODE_CBC;
        }

        // Determining whether inline encrypting can be used by the cipher
        if ($this->useInlineCrypt !== false && function_exists('create_function')) {
            $this->useInlineCrypt = true;
        }
    }

    /**
     * Sets the initialization vector. (optional)
     *
     * SetIV is not required when CRYPT_MODE_ECB (or ie for AES: CRYPT_AES_MODE_ECB) is being used.
     * If not explicitly set, it'll be assumed to be all zero's.
     *
     * Note: Could, but not must, extend by the child Crypt* class
     *
     * @param String $iv
     */
    public function setIV($iv)
    {
        if ($this->mode == CRYPT_MODE_ECB) {
            return;
        }

        $this->iv = $iv;
        $this->changed = true;
    }

    /**
     * Gets the initialization vector.
     *
     * @return String
     */
    public function getIV()
    {
        return $this->iv;
    }

    /**
     * Sets the key.
     *
     * The min/max length(s) of the key depends on the cipher which is used.
     * If the key not fits the length(s) of the cipher it will padded with null bytes
     * up to the closest valid key length.  If the key is more than max length,
     * we trim the excess bits.
     *
     * If the key is not explicitly set, it'll be assumed to be all null bytes.
     *
     * Note: Could, but not must, extend by the child Crypt* class
     *
     * @param String $key
     */
    public function setKey($key)
    {
        $this->key = $key;
        $this->changed = true;
    }

    /**
     * Sets the password.
     *
     * Depending on what $method is set to, setPassword()'s (optional) parameters are as follows:
     *     {@link http://en.wikipedia.org/wiki/PBKDF2 pbkdf2}:
     *         $hash, $salt, $count, $dk_len
     *
     *         Where $hash (default = sha1) currently supports the following hashes: see: Crypt/Hash.php
     *
     * Note: Could, but not must, extend by the child Crypt* class
     *
     * @see Crypt/Hash.php
     * @param String $password
     * @param optional String $method
     */
    public function setPassword($password, $method = 'pbkdf2')
    {
// DISABLED DUE TO MISSING "CryptHash" METHOD (WE ARE NOT USING THIS ANYWAYS)
//		$key = '';
//
//		switch ($method)
//		{
//			default: // 'pbkdf2'
//				$func_args = func_get_args();
//
//				// Hash function
//				$hash = isset($func_args[2]) ? $func_args[2] : 'sha1';
//
//				// WPA and WPA2 use the SSID as the salt
//				$salt = isset($func_args[3]) ? $func_args[3] : $this->passwordDefaultSalt;
//
//				// RFC2898#section-4.2 uses 1,000 iterations by default
//				// WPA and WPA2 use 4,096.
//				$count = isset($func_args[4]) ? $func_args[4] : 1000;
//
//				// Key length
//				$dk_len = isset($func_args[5]) ? $func_args[5] : $this->passwordKeySize;
//
//				// Determining if php[>=5.5.0]'s hash_pbkdf2() function avail- and usable
//				switch (true)
//				{
//					case !function_exists('hash_pbkdf2'):
//					case !function_exists('hash_algos'):
//					case !in_array($hash, hash_algos()):
//						$i = 1;
//						$key_len = strlen($key);
//						while ($key_len < $dk_len)
//						{
//							$hmac = new CryptHash();
//							$hmac->setHash($hash);
//							$hmac->setKey($password);
//							$f = $u = $hmac->hash($salt.pack('N', $i++));
//							for ($j = 2; $j <= $count; ++$j)
//							{
//								$u = $hmac->hash($u);
//								$f ^= $u;
//							}
//							$key .= $f;
//							$key_len = strlen($key);
//						}
//						$key = substr($key, 0, $dk_len);
//						break;
//					default:
//						$key = hash_pbkdf2($hash, $password, $salt, $count, $dk_len, true);
//				}
//		}
//
//		$this->setKey($key);
    }

    /**
     * Encrypts a message.
     *
     * $plaintext will be padded with additional bytes such that it's length is a multiple of the block size. Other
     * cipher implementations may or may not pad in the same manner.  Other common approaches to padding and the reasons
     * why it's necessary are discussed in the following
     * URL:
     *
     * {@link http://www.di-mgt.com.au/cryptopad.html http://www.di-mgt.com.au/cryptopad.html}
     *
     * An alternative to padding is to, separately, send the length of the file.  This is what SSH, in fact, does.
     * strlen($plaintext) will still need to be a multiple of the block size, however, arbitrary values can be added to
     * make it that length.
     *
     * Note: Could, but not must, extend by the child Crypt* class
     *
     * @see NostoCryptBase::decrypt()
     * @param String $plaintext
     * @return String $ciphertext
     */
    public function encrypt($plaintext)
    {
        if ($this->engine == CRYPT_MODE_MCRYPT) {
            if ($this->changed) {
                $this->setupMcrypt();
                $this->changed = false;
            }
            if ($this->enChanged) {
                mcrypt_generic_init($this->enMcrypt, $this->key, $this->encryptIV);
                $this->enChanged = false;
            }

            // re: {@link http://phpseclib.sourceforge.net/cfb-demo.phps}
            // using mcrypt's default handing of CFB the above would output two different things.  using phpseclib's
            // rewritten CFB implementation the above outputs the same thing twice.
            if ($this->mode == CRYPT_MODE_CFB && $this->continuousBuffer) {
                $blockSize = $this->blockSize;
                $iv = & $this->encryptIV;
                $pos = & $this->enBuffer['pos'];
                $len = strlen($plaintext);
                $ciphertext = '';
                $i = 0;
                if ($pos) {
                    $origPos = $pos;
                    $max = $blockSize - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len -= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos += $len;
                        $len = 0;
                    }
                    $ciphertext = substr($iv, $origPos) ^ $plaintext;
                    $iv = substr_replace($iv, $ciphertext, $origPos, $i);
                    $this->enBuffer['enmcrypt_init'] = true;
                }
                if ($len >= $blockSize) {
                    if ($this->enBuffer['enmcrypt_init'] === false || $len > $this->cfbInitLen) {
                        if ($this->enBuffer['enmcrypt_init'] === true) {
                            mcrypt_generic_init($this->enMcrypt, $this->key, $iv);
                            $this->enBuffer['enmcrypt_init'] = false;
                        }
                        $ciphertext .= mcrypt_generic(
                            $this->enMcrypt,
                            substr($plaintext, $i, $len - $len % $blockSize)
                        );
                        $iv = substr($ciphertext, -$blockSize);
                        $len %= $blockSize;
                    } else {
                        while ($len >= $blockSize) {
                            $iv = mcrypt_generic($this->ecb, $iv) ^ substr($plaintext, $i, $blockSize);
                            $ciphertext .= $iv;
                            $len -= $blockSize;
                            $i += $blockSize;
                        }
                    }
                }

                if ($len) {
                    $iv = mcrypt_generic($this->ecb, $iv);
                    $block = $iv ^ substr($plaintext, -$len);
                    $iv = substr_replace($iv, $block, 0, $len);
                    $ciphertext .= $block;
                    $pos = $len;
                }
                return $ciphertext;
            }

            if ($this->paddable) {
                $plaintext = $this->pad($plaintext);
            }

            $ciphertext = mcrypt_generic($this->enMcrypt, $plaintext);

            if (!$this->continuousBuffer) {
                mcrypt_generic_init($this->enMcrypt, $this->key, $this->encryptIV);
            }

            return $ciphertext;
        }

        if ($this->changed) {
            $this->setup();
            $this->changed = false;
        }
        if ($this->useInlineCrypt) {
            $inline = $this->inlineCrypt;
            return $inline('encrypt', $this, $plaintext);
        }
        if ($this->paddable) {
            $plaintext = $this->pad($plaintext);
        }

        $buffer = & $this->enBuffer;
        $blockSize = $this->blockSize;
        $ciphertext = '';
        $plaintext_len = strlen($plaintext);
        switch ($this->mode) {
            case CRYPT_MODE_ECB:
                for ($i = 0; $i < $plaintext_len; $i += $blockSize) {
                    $ciphertext .= $this->encryptBlock(substr($plaintext, $i, $blockSize));
                }
                break;
            case CRYPT_MODE_CBC:
                $xor = $this->encryptIV;
                for ($i = 0; $i < $plaintext_len; $i += $blockSize) {
                    $block = substr($plaintext, $i, $blockSize);
                    $block = $this->encryptBlock($block ^ $xor);
                    $xor = $block;
                    $ciphertext .= $block;
                }
                if ($this->continuousBuffer) {
                    $this->encryptIV = $xor;
                }
                break;
            case CRYPT_MODE_CTR:
                $xor = $this->encryptIV;
                $key = '';
                if (strlen($buffer['encrypted'])) {
                    for ($i = 0; $i < $plaintext_len; $i += $blockSize) {
                        $block = substr($plaintext, $i, $blockSize);
                        if (strlen($block) > strlen($buffer['encrypted'])) {
                            $buffer['encrypted'] .= $this->encryptBlock($this->generateXor($xor, $blockSize));
                        }
                        $key = $this->stringShift($buffer['encrypted'], $blockSize);
                        $ciphertext .= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < $plaintext_len; $i += $blockSize) {
                        $block = substr($plaintext, $i, $blockSize);
                        $key = $this->encryptBlock($this->generateXor($xor, $blockSize));
                        $ciphertext .= $block ^ $key;
                    }
                }
                if ($this->continuousBuffer) {
                    $this->encryptIV = $xor;
                    if ($start = strlen($plaintext) % $blockSize) {
                        $buffer['encrypted'] = substr($key, $start).$buffer['encrypted'];
                    }
                }
                break;
            case CRYPT_MODE_CFB:
                // cfb loosely routines inspired by openssl's:
                // {@link http://cvs.openssl.org/fileview?f=openssl/crypto/modes/cfb128.c&v=1.3.2.2.2.1}
                if ($this->continuousBuffer) {
                    $iv = & $this->encryptIV;
                    $pos = & $buffer['pos'];
                } else {
                    $iv = $this->encryptIV;
                    $pos = 0;
                }
                $len = strlen($plaintext);
                $i = 0;
                if ($pos) {
                    $origPos = $pos;
                    $max = $blockSize - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len -= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos += $len;
                        $len = 0;
                    }
                    // ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $blocksize
                    $ciphertext = substr($iv, $origPos) ^ $plaintext;
                    $iv = substr_replace($iv, $ciphertext, $origPos, $i);
                }
                while ($len >= $blockSize) {
                    $iv = $this->encryptBlock($iv) ^ substr($plaintext, $i, $blockSize);
                    $ciphertext .= $iv;
                    $len -= $blockSize;
                    $i += $blockSize;
                }
                if ($len) {
                    $iv = $this->encryptBlock($iv);
                    $block = $iv ^ substr($plaintext, $i);
                    $iv = substr_replace($iv, $block, 0, $len);
                    $ciphertext .= $block;
                    $pos = $len;
                }
                break;
            case CRYPT_MODE_OFB:
                $xor = $this->encryptIV;
                $key = '';
                if (strlen($buffer['xor'])) {
                    for ($i = 0; $i < $plaintext_len; $i += $blockSize) {
                        $block = substr($plaintext, $i, $blockSize);
                        if (strlen($block) > strlen($buffer['xor'])) {
                            $xor = $this->encryptBlock($xor);
                            $buffer['xor'] .= $xor;
                        }
                        $key = $this->stringShift($buffer['xor'], $blockSize);
                        $ciphertext .= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < $plaintext_len; $i += $blockSize) {
                        $xor = $this->encryptBlock($xor);
                        $ciphertext .= substr($plaintext, $i, $blockSize) ^ $xor;
                    }
                    $key = $xor;
                }
                if ($this->continuousBuffer) {
                    $this->encryptIV = $xor;
                    if ($start = strlen($plaintext) % $blockSize) {
                        $buffer['xor'] = substr($key, $start).$buffer['xor'];
                    }
                }
                break;
            case CRYPT_MODE_STREAM:
                $ciphertext = $this->encryptBlock($plaintext);
                break;
        }

        return $ciphertext;
    }

    /**
     * Decrypts a message.
     *
     * If strlen($ciphertext) is not a multiple of the block size, null bytes will be added to the end of the string
     * until it is.
     *
     * Note: Could, but not must, extend by the child Crypt* class
     *
     * @see NostoCryptBase::encrypt()
     * @param String $ciphertext
     * @return String $plaintext
     */
    public function decrypt($ciphertext)
    {
        if ($this->engine == CRYPT_MODE_MCRYPT) {
            $block_size = $this->blockSize;
            if ($this->changed) {
                $this->setupMcrypt();
                $this->changed = false;
            }
            if ($this->deChanged) {
                mcrypt_generic_init($this->deMcrypt, $this->key, $this->decryptIV);
                $this->deChanged = false;
            }

            if ($this->mode == CRYPT_MODE_CFB && $this->continuousBuffer) {
                $iv = & $this->decryptIV;
                $pos = & $this->deBuffer['pos'];
                $len = strlen($ciphertext);
                $plaintext = '';
                $i = 0;
                if ($pos) {
                    $orig_pos = $pos;
                    $max = $block_size - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len -= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos += $len;
                        $len = 0;
                    }
                    // ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $blocksize
                    $plaintext = substr($iv, $orig_pos) ^ $ciphertext;
                    $iv = substr_replace($iv, substr($ciphertext, 0, $i), $orig_pos, $i);
                }
                if ($len >= $block_size) {
                    $cb = substr($ciphertext, $i, $len - $len % $block_size);
                    $plaintext .= mcrypt_generic($this->ecb, $iv.$cb) ^ $cb;
                    $iv = substr($cb, -$block_size);
                    $len %= $block_size;
                }
                if ($len) {
                    $iv = mcrypt_generic($this->ecb, $iv);
                    $plaintext .= $iv ^ substr($ciphertext, -$len);
                    $iv = substr_replace($iv, substr($ciphertext, -$len), 0, $len);
                    $pos = $len;
                }

                return $plaintext;
            }

            if ($this->paddable) {
                // we pad with chr(0) since that's what mcrypt_generic does. to quote from
                // {@link http://www.php.net/function.mcrypt-generic}:
                // "The data is padded with "\0" to make sure the length of the data is n * blocksize."
                $ciphertext = str_pad(
                    $ciphertext,
                    strlen($ciphertext) + ($block_size - strlen($ciphertext) % $block_size) % $block_size,
                    chr(0)
                );
            }

            $plaintext = mdecrypt_generic($this->deMcrypt, $ciphertext);

            if (!$this->continuousBuffer) {
                mcrypt_generic_init($this->deMcrypt, $this->key, $this->decryptIV);
            }

            return $this->paddable ? $this->unpad($plaintext) : $plaintext;
        }

        if ($this->changed) {
            $this->setup();
            $this->changed = false;
        }
        if ($this->useInlineCrypt) {
            $inline = $this->inlineCrypt;
            return $inline('decrypt', $this, $ciphertext);
        }

        $block_size = $this->blockSize;
        if ($this->paddable) {
            // we pad with chr(0) since that's what mcrypt_generic does [...]
            $ciphertext = str_pad(
                $ciphertext,
                strlen($ciphertext) + ($block_size - strlen($ciphertext) % $block_size) % $block_size,
                chr(0)
            );
        }

        $buffer = & $this->deBuffer;
        $plaintext = '';
        $cipher_text_len = strlen($ciphertext);
        switch ($this->mode) {
            case CRYPT_MODE_ECB:
                for ($i = 0; $i < $cipher_text_len; $i += $block_size) {
                    $plaintext .= $this->decryptBlock(substr($ciphertext, $i, $block_size));
                }
                break;
            case CRYPT_MODE_CBC:
                $xor = $this->decryptIV;
                for ($i = 0; $i < $cipher_text_len; $i += $block_size) {
                    $block = substr($ciphertext, $i, $block_size);
                    $plaintext .= $this->decryptBlock($block) ^ $xor;
                    $xor = $block;
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                }
                break;
            case CRYPT_MODE_CTR:
                $xor = $this->decryptIV;
                $key = '';
                if (strlen($buffer['ciphertext'])) {
                    for ($i = 0; $i < $cipher_text_len; $i += $block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        if (strlen($block) > strlen($buffer['ciphertext'])) {
                            $buffer['ciphertext'] .= $this->encryptBlock($this->generateXor($xor, $block_size));
                        }
                        $key = $this->stringShift($buffer['ciphertext'], $block_size);
                        $plaintext .= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < $cipher_text_len; $i += $block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        $key = $this->encryptBlock($this->generateXor($xor, $block_size));
                        $plaintext .= $block ^ $key;
                    }
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                    if ($start = strlen($ciphertext) % $block_size) {
                        $buffer['ciphertext'] = substr($key, $start).$buffer['ciphertext'];
                    }
                }
                break;
            case CRYPT_MODE_CFB:
                if ($this->continuousBuffer) {
                    $iv = & $this->decryptIV;
                    $pos = & $buffer['pos'];
                } else {
                    $iv = $this->decryptIV;
                    $pos = 0;
                }
                $len = strlen($ciphertext);
                $i = 0;
                if ($pos) {
                    $orig_pos = $pos;
                    $max = $block_size - $pos;
                    if ($len >= $max) {
                        $i = $max;
                        $len -= $max;
                        $pos = 0;
                    } else {
                        $i = $len;
                        $pos += $len;
                        $len = 0;
                    }
                    // ie. $i = min($max, $len), $len-= $i, $pos+= $i, $pos%= $blocksize
                    $plaintext = substr($iv, $orig_pos) ^ $ciphertext;
                    $iv = substr_replace($iv, substr($ciphertext, 0, $i), $orig_pos, $i);
                }
                while ($len >= $block_size) {
                    $iv = $this->encryptBlock($iv);
                    $cb = substr($ciphertext, $i, $block_size);
                    $plaintext .= $iv ^ $cb;
                    $iv = $cb;
                    $len -= $block_size;
                    $i += $block_size;
                }
                if ($len) {
                    $iv = $this->encryptBlock($iv);
                    $plaintext .= $iv ^ substr($ciphertext, $i);
                    $iv = substr_replace($iv, substr($ciphertext, $i), 0, $len);
                    $pos = $len;
                }
                break;
            case CRYPT_MODE_OFB:
                $xor = $this->decryptIV;
                $key = '';
                if (strlen($buffer['xor'])) {
                    for ($i = 0; $i < $cipher_text_len; $i += $block_size) {
                        $block = substr($ciphertext, $i, $block_size);
                        if (strlen($block) > strlen($buffer['xor'])) {
                            $xor = $this->encryptBlock($xor);
                            $buffer['xor'] .= $xor;
                        }
                        $key = $this->stringShift($buffer['xor'], $block_size);
                        $plaintext .= $block ^ $key;
                    }
                } else {
                    for ($i = 0; $i < $cipher_text_len; $i += $block_size) {
                        $xor = $this->encryptBlock($xor);
                        $plaintext .= substr($ciphertext, $i, $block_size) ^ $xor;
                    }
                    $key = $xor;
                }
                if ($this->continuousBuffer) {
                    $this->decryptIV = $xor;
                    if ($start = strlen($ciphertext) % $block_size) {
                        $buffer['xor'] = substr($key, $start).$buffer['xor'];
                    }
                }
                break;
            case CRYPT_MODE_STREAM:
                $plaintext = $this->decryptBlock($ciphertext);
                break;
        }
        return $this->paddable ? $this->unpad($plaintext) : $plaintext;
    }

    /**
     * Pad "packets".
     *
     * Block ciphers working by encrypting between their specified [$this->]blockSize at a time
     * If you ever need to encrypt or decrypt something that isn't of the proper length, it becomes necessary to
     * pad the input so that it is of the proper length.
     *
     * Padding is enabled by default.  Sometimes, however, it is undesirable to pad strings.  Such is the case in SSH,
     * where "packets" are padded with random bytes before being encrypted. Un-pad these packets and you risk stripping
     * away characters that should not be stripped away. (SSH knows how many bytes are added because the length is
     * transmitted separately)
     *
     * @see NostoCryptBase::disablePadding()
     */
    public function enablePadding()
    {
        $this->padding = true;
    }

    /**
     * Do not pad packets.
     *
     * @see NostoCryptBase::enablePadding()
     */
    public function disablePadding()
    {
        $this->padding = false;
    }

    /**
     * Treat consecutive "packets" as if they are a continuous buffer.
     *
     * Say you have a 32-byte plaintext $plaintext.  Using the default behavior, the two following code snippets
     * will yield different outputs:
     *
     * <code>
     *    echo $rijndael->encrypt(substr($plaintext,  0, 16));
     *    echo $rijndael->encrypt(substr($plaintext, 16, 16));
     * </code>
     * <code>
     *    echo $rijndael->encrypt($plaintext);
     * </code>
     *
     * The solution is to enable the continuous buffer.  Although this will resolve the above discrepancy, it creates
     * another, as demonstrated with the following:
     *
     * <code>
     *    $rijndael->encrypt(substr($plaintext, 0, 16));
     *    echo $rijndael->decrypt($rijndael->encrypt(substr($plaintext, 16, 16)));
     * </code>
     * <code>
     *    echo $rijndael->decrypt($rijndael->encrypt(substr($plaintext, 16, 16)));
     * </code>
     *
     * With the continuous buffer disabled, these would yield the same output.  With it enabled, they yield different
     * outputs.  The reason is due to the fact that the initialization vector's change after every encryption /
     * decryption round when the continuous buffer is enabled.  When it's disabled, they remain constant.
     *
     * Put another way, when the continuous buffer is enabled, the state of the Crypt*() object changes after each
     * encryption / decryption round, whereas otherwise, it'd remain constant.  For this reason, it's recommended that
     * continuous buffers not be used.  They do offer better security and are, in fact, sometimes required
     * (SSH uses them), however, they are also less intuitive and more likely to cause you problems.
     *
     * Note: Could, but not must, extend by the child Crypt* class
     *
     * @see NostoCryptBase::disableContinuousBuffer()
     */
    public function enableContinuousBuffer()
    {
        if ($this->mode == CRYPT_MODE_ECB) {
            return;
        }

        $this->continuousBuffer = true;
    }

    /**
     * Treat consecutive packets as if they are a discontinuous buffer.
     *
     * The default behavior.
     *
     * Note: Could, but not must, extend by the child Crypt* class
     *
     * @see NostoCryptBase::enableContinuousBuffer()
     */
    public function disableContinuousBuffer()
    {
        if ($this->mode == CRYPT_MODE_ECB) {
            return;
        }
        if (!$this->continuousBuffer) {
            return;
        }

        $this->continuousBuffer = false;
        $this->changed = true;
    }

    /**
     * Encrypts a block
     *
     * Note: Must extend by the child Crypt* class
     *
     * @param String $in
     * @return String
     * @throws NostoUndefinedMethodException
     */
    public function encryptBlock($in)
    {
        throw new NostoUndefinedMethodException(__METHOD__.'() must extend by class '.get_class($this));
    }

    /**
     * Decrypts a block
     *
     * Note: Must extend by the child Crypt* class
     *
     * @param String $in
     * @return String
     * @throws NostoUndefinedMethodException
     */
    public function decryptBlock($in)
    {
        throw new NostoUndefinedMethodException(__METHOD__.'() must extend by class '.get_class($this));
    }

    /**
     * Setup the key (expansion)
     *
     * Only used if $engine == CRYPT_MODE_INTERNAL
     *
     * Note: Must extend by the child Crypt* class
     *
     * @see NostoCryptBase::setup()
     * @throws NostoUndefinedMethodException
     */
    public function setupKey()
    {
        throw new NostoUndefinedMethodException(__METHOD__.'() must extend by class '.get_class($this));
    }

    /**
     * Setup the CRYPT_MODE_INTERNAL $engine
     *
     * (re)init, if necessary, the internal cipher $engine and flush all $buffers
     * Used (only) if $engine == CRYPT_MODE_INTERNAL
     *
     * _setup() will be called each time if $changed === true
     * typically this happens when using one or more of following public methods:
     *
     * - setKey()
     *
     * - setIV()
     *
     * - disableContinuousBuffer()
     *
     * - First run of encrypt() / decrypt() with no init-settings
     *
     * Internally: _setup() is called always before(!) en/decryption.
     *
     * Note: Could, but not must, extend by the child Crypt* class
     *
     * @see setKey()
     * @see setIV()
     * @see disableContinuousBuffer()
     */
    public function setup()
    {
        $this->clearBuffers();
        $this->setupKey();

        if ($this->useInlineCrypt) {
            $this->setupInlineCrypt();
        }
    }

    /**
     * Setup the CRYPT_MODE_MCRYPT $engine
     *
     * (re)init, if necessary, the (ext)mcrypt resources and flush all $buffers
     * Used (only) if $engine = CRYPT_MODE_MCRYPT
     *
     * _setupMcrypt() will be called each time if $changed === true
     * typically this happens when using one or more of following public methods:
     *
     * - setKey()
     *
     * - setIV()
     *
     * - disableContinuousBuffer()
     *
     * - First run of encrypt() / decrypt()
     *
     *
     * Note: Could, but not must, extend by the child Crypt* class
     *
     * @see setKey()
     * @see setIV()
     * @see disableContinuousBuffer()
     */
    public function setupMcrypt()
    {
        $this->clearBuffers();
        $this->enChanged = $this->deChanged = true;

        if (!isset($this->enMcrypt)) {
            static $mcrypt_modes = array(
                CRYPT_MODE_CTR => 'ctr',
                CRYPT_MODE_ECB => MCRYPT_MODE_ECB,
                CRYPT_MODE_CBC => MCRYPT_MODE_CBC,
                CRYPT_MODE_CFB => 'ncfb',
                CRYPT_MODE_OFB => MCRYPT_MODE_NOFB,
                CRYPT_MODE_STREAM => MCRYPT_MODE_STREAM,
            );

            $this->deMcrypt = mcrypt_module_open($this->cipherNameMcrypt, '', $mcrypt_modes[$this->mode], '');
            $this->enMcrypt = mcrypt_module_open($this->cipherNameMcrypt, '', $mcrypt_modes[$this->mode], '');

            // we need the $ecb mcrypt resource (only) in MODE_CFB with enableContinuousBuffer()
            // to workaround mcrypt's broken ncfb implementation in buffered mode
            // see: {@link http://phpseclib.sourceforge.net/cfb-demo.phps}
            if ($this->mode == CRYPT_MODE_CFB) {
                $this->ecb = mcrypt_module_open($this->cipherNameMcrypt, '', MCRYPT_MODE_ECB, '');
            }

        } // else should mcrypt_generic_deinit be called?

        if ($this->mode == CRYPT_MODE_CFB) {
            mcrypt_generic_init($this->ecb, $this->key, str_repeat("\0", $this->blockSize));
        }
    }

    /**
     * Pads a string
     *
     * Pads a string using the RSA PKCS padding standards so that its length is a multiple of the block size.
     * $this->blockSize - (strlen($text) % $this->blockSize) bytes are added, each of which is equal to
     * chr($this->blockSize - (strlen($text) % $this->blockSize)
     *
     * If padding is disabled and $text is not a multiple of the block size, the string will be padded regardless
     * and padding will, hence forth, be enabled.
     *
     * @see NostoCryptBase::unpad()
     * @param String $text
     * @return String
     * @throws NostoInvalidLengthException
     */
    public function pad($text)
    {
        $length = strlen($text);

        if (!$this->padding) {
            if ($length % $this->blockSize == 0) {
                return $text;
            } else {
                throw new NostoInvalidLengthException(
                    "The plaintext length ($length) is not a multiple of the block size ({$this->blockSize})"
                );
            }
        }

        $pad = $this->blockSize - ($length % $this->blockSize);

        return str_pad($text, $length + $pad, chr($pad));
    }

    /**
     * Un-pads a string.
     *
     * If padding is enabled and the reported padding length is invalid the encryption key will be assumed to be wrong
     * and false will be returned.
     *
     * @see NostoCryptBase::pad()
     * @param String $text
     * @return String
     * @throws NostoInvalidPaddingException
     */
    public function unpad($text)
    {
        if (!$this->padding) {
            return $text;
        }

        $length = ord($text[strlen($text) - 1]);

        if (!$length || $length > $this->blockSize) {
            throw new NostoInvalidPaddingException("An illegal padding character ($length) has been detected");
        }

        return substr($text, 0, -$length);
    }

    /**
     * Clears internal buffers
     *
     * Clearing/resetting the internal buffers is done everytime
     * after disableContinuousBuffer() or on cipher $engine (re)init
     * ie after setKey() or setIV()
     *
     * Note: Could, but not must, extend by the child Crypt* class
     */
    public function clearBuffers()
    {
        $this->enBuffer = array('encrypted' => '', 'xor' => '', 'pos' => 0, 'enmcrypt_init' => true);
        $this->deBuffer = array('ciphertext' => '', 'xor' => '', 'pos' => 0, 'demcrypt_init' => true);

        // mcrypt's handling of invalid's $iv:
        // $this->encryptIV = $this->decryptIV =
        // strlen($this->iv) == $this->blockSize ? $this->iv : str_repeat("\0", $this->blockSize);
        $this->encryptIV = $this->decryptIV = str_pad(substr($this->iv, 0, $this->blockSize), $this->blockSize, "\0");
    }

    /**
     * String Shift
     *
     * Inspired by array_shift
     *
     * @param String $string
     * @param optional Integer $index
     * @return String
     */
    public function stringShift(&$string, $index = 1)
    {
        $substr = substr($string, 0, $index);
        $string = substr($string, $index);
        return $substr;
    }

    /**
     * Generate CTR XOR encryption key
     *
     * Encrypt the output of this and XOR it against the ciphertext / plaintext to get the
     * plaintext / ciphertext in CTR mode.
     *
     * @see NostoCryptBase::decrypt()
     * @see NostoCryptBase::encrypt()
     * @param String $iv
     * @param Integer $length
     * @return String $xor
     */
    public function generateXor(&$iv, $length)
    {
        $xor = '';
        $count = 0;
        $block_size = $this->blockSize;
        $num_blocks = floor(($length + ($block_size - 1)) / $block_size);
        for ($i = 0; $i < $num_blocks; $i++) {
            $xor .= $iv;
            for ($j = 4; $j <= $block_size; $j += 4) {
                $temp = substr($iv, -$j, 4);
                switch ($temp) {
                    case "\xFF\xFF\xFF\xFF":
                        $iv = substr_replace($iv, "\x00\x00\x00\x00", -$j, 4);
                        break;
                    case "\x7F\xFF\xFF\xFF":
                        $iv = substr_replace($iv, "\x80\x00\x00\x00", -$j, 4);
                        break 2;
                    default:
                        extract(unpack('Ncount', $temp));
                        $iv = substr_replace($iv, pack('N', $count + 1), -$j, 4);
                        break 2;
                }
            }
        }

        return $xor;
    }

    /**
     * Setup the performance-optimized function for de/encrypt()
     *
     * Stores the created (or existing) callback function-name
     * in $this->inlineCrypt
     *
     * Internally for phpseclib developers:
     *
     *     _setupInlineCrypt() would be called only if:
     *
     *     - $engine == CRYPT_MODE_INTERNAL and
     *
     *     - $useInlineCrypt === true
     *
     *     - each time on _setup(), after(!) setupKey()
     *
     *
     *     This ensures that _setupInlineCrypt() has allways a
     *     full ready2go initializated internal cipher $engine state
     *     where, for example, the keys allready expanded,
     *     keys/blockSize calculated and such.
     *
     *     It is, each time if called, the responsibility of _setupInlineCrypt():
     *
     *     - to set $this->inlineCrypt to a valid and fully working callback function
     *       as a (faster) replacement for encrypt() / decrypt()
     *
     *     - NOT to create unlimited callback functions (for memory reasons!)
     *       no matter how often _setupInlineCrypt() would be called. At some
     *       point of amount they must be generic re-useable.
     *
     *     - the code of _setupInlineCrypt() it self,
     *       and the generated callback code,
     *       must be, in following order:
     *       - 100% safe
     *       - 100% compatible to encrypt()/decrypt()
     *       - using only php5+ features/lang-constructs/php-extensions if
     *         compatibility (down to php4) or fallback is provided
     *       - readable/maintainable/understandable/commented and... not-cryptic-styled-code :-)
     *       - >= 10% faster than encrypt()/decrypt() [which is, by the way,
     *         the reason for the existence of _setupInlineCrypt() :-)]
     *       - memory-nice
     *       - short (as good as possible)
     *
     * Note: - _setupInlineCrypt() is using _createInlineCryptFunction() to create the full callback function code.
     *       - In case of using inline crypting, _setupInlineCrypt() must extend by the child Crypt* class.
     *       - The following variable names are reserved:
     *         - $_*  (all variable names prefixed with an underscore)
     *         - $self (object reference to it self. Do not use $this, but $self instead)
     *         - $in (the content of $in has to en/decrypt by the generated code)
     *       - The callback function should not use the 'return' statement, but en/decrypt'ing the content of $in only
     *
     *
     * @see NostoCryptBase::setup()
     * @see NostoCryptBase::createInlineCryptFunction()
     * @see NostoCryptBase::encrypt()
     * @see NostoCryptBase::decrypt()
     */
    public function setupInlineCrypt()
    {
        // If a Crypt* class providing inline crypting it must extend _setupInlineCrypt()

        // If, for any reason, an extending NostoCryptBase() Crypt* class
        // not using inline crypting then it must be ensured that: $this->useInlineCrypt = false
        // ie in the class var declaration of $useInlineCrypt in general for the Crypt* class,
        // in the constructor at object instance-time
        // or, if it's runtime-specific, at runtime

        $this->useInlineCrypt = false;
    }

    /**
     * Creates the performance-optimized function for en/decrypt()
     *
     * Internally for phpseclib developers:
     *
     *    _createInlineCryptFunction():
     *
     *    - merge the $cipher_code [setup'ed by _setupInlineCrypt()]
     *      with the current [$this->]mode of operation code
     *
     *    - create the $inline function, which called by encrypt() / decrypt()
     *      as its replacement to speed up the en/decryption operations.
     *
     *    - return the name of the created $inline callback function
     *
     *    - used to speed up en/decryption
     *
     *
     *
     *    The main reason why can speed up things [up to 50%] this way are:
     *
     *    - using variables more effective then regular.
     *      (ie no use of expensive arrays but integers $k_0, $k_1 ...
     *      or even, for example, the pure $key[] values hardcoded)
     *
     *    - avoiding 1000's of function calls of ie encryptBlock()
     *      but inlining the crypt operations.
     *      in the mode of operation for() loop.
     *
     *    - full loop unroll the (sometimes key-dependent) rounds
     *      avoiding this way ++$i counters and runtime-if's etc...
     *
     *    The basic code architectur of the generated $inline en/decrypt()
     *    lambda function, in pseudo php, is:
     *
     *    <code>
     *    +----------------------------------------------------------------------------------------------+
     *    | callback $inline = create_function:                                                          |
     *    | lambda_function_0001_crypt_ECB($action, $text)                                               |
     *    | {                                                                                            |
     *    |     INSERT PHP CODE OF:                                                                      |
     *    |     $cipher_code['init_crypt'];                  // general init code.                       |
     *    |                                                  // ie: $sbox'es declarations used for       |
     *    |                                                  //     encrypt and decrypt'ing.             |
     *    |                                                                                              |
     *    |     switch ($action) {                                                                       |
     *    |         case 'encrypt':                                                                      |
     *    |             INSERT PHP CODE OF:                                                              |
     *    |             $cipher_code['init_encrypt'];       // encrypt sepcific init code.               |
     *    |                                                    ie: specified $key or $box                |
     *    |                                                        declarations for encrypt'ing.         |
     *    |                                                                                              |
     *    |             foreach ($ciphertext) {                                                          |
     *    |                 $in = $blockSize of $ciphertext;                                            |
     *    |                                                                                              |
     *    |                 INSERT PHP CODE OF:                                                          |
     *    |                 $cipher_code['encrypt_block'];  // encrypt's (string) $in, which is always:  |
     *    |                                                 // strlen($in) == $this->blockSize          |
     *    |                                                 // here comes the cipher algorithm in action |
     *    |                                                 // for encryption.                           |
     *    |                                                 // $cipher_code['encrypt_block'] has to      |
     *    |                                                 // encrypt the content of the $in variable   |
     *    |                                                                                              |
     *    |                 $plaintext .= $in;                                                           |
     *    |             }                                                                                |
     *    |             return $plaintext;                                                               |
     *    |                                                                                              |
     *    |         case 'decrypt':                                                                      |
     *    |             INSERT PHP CODE OF:                                                              |
     *    |             $cipher_code['init_decrypt'];       // decrypt sepcific init code                |
     *    |                                                    ie: specified $key or $box                |
     *    |                                                        declarations for decrypt'ing.         |
     *    |             foreach ($plaintext) {                                                           |
     *    |                 $in = $blockSize of $plaintext;                                             |
     *    |                                                                                              |
     *    |                 INSERT PHP CODE OF:                                                          |
     *    |                 $cipher_code['decrypt_block'];  // decrypt's (string) $in, which is always   |
     *    |                                                 // strlen($in) == $this->blockSize          |
     *    |                                                 // here comes the cipher algorithm in action |
     *    |                                                 // for decryption.                           |
     *    |                                                 // $cipher_code['decrypt_block'] has to      |
     *    |                                                 // decrypt the content of the $in variable   |
     *    |                 $ciphertext .= $in;                                                          |
     *    |             }                                                                                |
     *    |             return $ciphertext;                                                              |
     *    |     }                                                                                        |
     *    | }                                                                                            |
     *    +----------------------------------------------------------------------------------------------+
     *    </code>
     *
     *    See also the Crypt*::setupInlineCrypt()'s for
     *    productive inline $cipher_code's how they works.
     *
     *    Structure of:
     *    <code>
     *    $cipher_code = array(
     *        'init_crypt'    => (string) '', // optional
     *        'init_encrypt'  => (string) '', // optional
     *        'init_decrypt'  => (string) '', // optional
     *        'encrypt_block' => (string) '', // required
     *        'decrypt_block' => (string) ''  // required
     *    );
     *    </code>
     *
     * @see NostoCryptBase::setupInlineCrypt()
     * @see NostoCryptBase::encrypt()
     * @see NostoCryptBase::decrypt()
     * @param Array $cipher_code
     * @return String (the name of the created callback function)
     */
    public function createInlineCryptFunction($cipher_code)
    {
        $block_size = $this->blockSize;

        // optional
        $init_crypt = isset($cipher_code['init_crypt']) ? $cipher_code['init_crypt'] : '';
        $init_encrypt = isset($cipher_code['init_encrypt']) ? $cipher_code['init_encrypt'] : '';
        $init_decrypt = isset($cipher_code['init_decrypt']) ? $cipher_code['init_decrypt'] : '';
        // required
        $encrypt_block = $cipher_code['encrypt_block'];
        $decrypt_block = $cipher_code['decrypt_block'];

        // Generating mode of operation inline code,
        // merged with the $cipher_code algorithm
        // for encrypt- and decryption.
        switch ($this->mode) {
            case CRYPT_MODE_ECB:
                $encrypt = $init_encrypt.'
                    $_ciphertext = "";
                    $_text = $self->pad($_text);
                    $_plaintext_len = strlen($_text);

                    for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                        $in = substr($_text, $_i, '.$block_size.');
                        '.$encrypt_block.'
                        $_ciphertext.= $in;
                    }

                    return $_ciphertext;
                    ';

                $decrypt = $init_decrypt.'
                    $_plaintext = "";
                    $_text = str_pad($_text, strlen($_text) + ('.$block_size.' - strlen($_text) % '.$block_size.') % '.$block_size.', chr(0));
                    $_ciphertext_len = strlen($_text);

                    for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                        $in = substr($_text, $_i, '.$block_size.');
                        '.$decrypt_block.'
                        $_plaintext.= $in;
                    }

                    return $self->unpad($_plaintext);
                    ';
                break;
            case CRYPT_MODE_CTR:
                $encrypt = $init_encrypt.'
                    $_ciphertext = "";
                    $_plaintext_len = strlen($_text);
                    $_xor = $self->encryptIV;
                    $_buffer = &$self->enBuffer;

                    if (strlen($_buffer["encrypted"])) {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["encrypted"])) {
                                $in = $self->generateXor($_xor, '.$block_size.');
                                '.$encrypt_block.'
                                $_buffer["encrypted"].= $in;
                            }
                            $_key = $self->stringShift($_buffer["encrypted"], '.$block_size.');
                            $_ciphertext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            $in = $self->generateXor($_xor, '.$block_size.');
                            '.$encrypt_block.'
                            $_key = $in;
                            $_ciphertext.= $_block ^ $_key;
                        }
                    }
                    if ($self->continuousBuffer) {
                        $self->encryptIV = $_xor;
                        if ($_start = $_plaintext_len % '.$block_size.') {
                            $_buffer["encrypted"] = substr($_key, $_start) . $_buffer["encrypted"];
                        }
                    }

                    return $_ciphertext;
                ';

                $decrypt = $init_encrypt.'
                    $_plaintext = "";
                    $_ciphertext_len = strlen($_text);
                    $_xor = $self->decryptIV;
                    $_buffer = &$self->deBuffer;

                    if (strlen($_buffer["ciphertext"])) {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["ciphertext"])) {
                                $in = $self->generateXor($_xor, '.$block_size.');
                                '.$encrypt_block.'
                                $_buffer["ciphertext"].= $in;
                            }
                            $_key = $self->stringShift($_buffer["ciphertext"], '.$block_size.');
                            $_plaintext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            $in = $self->generateXor($_xor, '.$block_size.');
                            '.$encrypt_block.'
                            $_key = $in;
                            $_plaintext.= $_block ^ $_key;
                        }
                    }
                    if ($self->continuousBuffer) {
                        $self->decryptIV = $_xor;
                        if ($_start = $_ciphertext_len % '.$block_size.') {
                            $_buffer["ciphertext"] = substr($_key, $_start) . $_buffer["ciphertext"];
                        }
                    }

                    return $_plaintext;
                    ';
                break;
            case CRYPT_MODE_CFB:
                $encrypt = $init_encrypt.'
                    $_ciphertext = "";
                    $_buffer = &$self->enBuffer;

                    if ($self->continuousBuffer) {
                        $_iv = &$self->encryptIV;
                        $_pos = &$_buffer["pos"];
                    } else {
                        $_iv = $self->encryptIV;
                        $_pos = 0;
                    }
                    $_len = strlen($_text);
                    $_i = 0;
                    if ($_pos) {
                        $_orig_pos = $_pos;
                        $_max = '.$block_size.' - $_pos;
                        if ($_len >= $_max) {
                            $_i = $_max;
                            $_len-= $_max;
                            $_pos = 0;
                        } else {
                            $_i = $_len;
                            $_pos+= $_len;
                            $_len = 0;
                        }
                        $_ciphertext = substr($_iv, $_orig_pos) ^ $_text;
                        $_iv = substr_replace($_iv, $_ciphertext, $_orig_pos, $_i);
                    }
                    while ($_len >= '.$block_size.') {
                        $in = $_iv;
                        '.$encrypt_block.';
                        $_iv = $in ^ substr($_text, $_i, '.$block_size.');
                        $_ciphertext.= $_iv;
                        $_len-= '.$block_size.';
                        $_i+= '.$block_size.';
                    }
                    if ($_len) {
                        $in = $_iv;
                        '.$encrypt_block.'
                        $_iv = $in;
                        $_block = $_iv ^ substr($_text, $_i);
                        $_iv = substr_replace($_iv, $_block, 0, $_len);
                        $_ciphertext.= $_block;
                        $_pos = $_len;
                    }
                    return $_ciphertext;
                ';

                $decrypt = $init_encrypt.'
                    $_plaintext = "";
                    $_buffer = &$self->deBuffer;

                    if ($self->continuousBuffer) {
                        $_iv = &$self->decryptIV;
                        $_pos = &$_buffer["pos"];
                    } else {
                        $_iv = $self->decryptIV;
                        $_pos = 0;
                    }
                    $_len = strlen($_text);
                    $_i = 0;
                    if ($_pos) {
                        $_orig_pos = $_pos;
                        $_max = '.$block_size.' - $_pos;
                        if ($_len >= $_max) {
                            $_i = $_max;
                            $_len-= $_max;
                            $_pos = 0;
                        } else {
                            $_i = $_len;
                            $_pos+= $_len;
                            $_len = 0;
                        }
                        $_plaintext = substr($_iv, $_orig_pos) ^ $_text;
                        $_iv = substr_replace($_iv, substr($_text, 0, $_i), $_orig_pos, $_i);
                    }
                    while ($_len >= '.$block_size.') {
                        $in = $_iv;
                        '.$encrypt_block.'
                        $_iv = $in;
                        $cb = substr($_text, $_i, '.$block_size.');
                        $_plaintext.= $_iv ^ $cb;
                        $_iv = $cb;
                        $_len-= '.$block_size.';
                        $_i+= '.$block_size.';
                    }
                    if ($_len) {
                        $in = $_iv;
                        '.$encrypt_block.'
                        $_iv = $in;
                        $_plaintext.= $_iv ^ substr($_text, $_i);
                        $_iv = substr_replace($_iv, substr($_text, $_i), 0, $_len);
                        $_pos = $_len;
                    }

                    return $_plaintext;
                    ';
                break;
            case CRYPT_MODE_OFB:
                $encrypt = $init_encrypt.'
                    $_ciphertext = "";
                    $_plaintext_len = strlen($_text);
                    $_xor = $self->encryptIV;
                    $_buffer = &$self->enBuffer;

                    if (strlen($_buffer["xor"])) {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["xor"])) {
                                $in = $_xor;
                                '.$encrypt_block.'
                                $_xor = $in;
                                $_buffer["xor"].= $_xor;
                            }
                            $_key = $self->stringShift($_buffer["xor"], '.$block_size.');
                            $_ciphertext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                            $in = $_xor;
                            '.$encrypt_block.'
                            $_xor = $in;
                            $_ciphertext.= substr($_text, $_i, '.$block_size.') ^ $_xor;
                        }
                        $_key = $_xor;
                    }
                    if ($self->continuousBuffer) {
                        $self->encryptIV = $_xor;
                        if ($_start = $_plaintext_len % '.$block_size.') {
                             $_buffer["xor"] = substr($_key, $_start) . $_buffer["xor"];
                        }
                    }
                    return $_ciphertext;
                    ';

                $decrypt = $init_encrypt.'
                    $_plaintext = "";
                    $_ciphertext_len = strlen($_text);
                    $_xor = $self->decryptIV;
                    $_buffer = &$self->deBuffer;

                    if (strlen($_buffer["xor"])) {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $_block = substr($_text, $_i, '.$block_size.');
                            if (strlen($_block) > strlen($_buffer["xor"])) {
                                $in = $_xor;
                                '.$encrypt_block.'
                                $_xor = $in;
                                $_buffer["xor"].= $_xor;
                            }
                            $_key = $self->stringShift($_buffer["xor"], '.$block_size.');
                            $_plaintext.= $_block ^ $_key;
                        }
                    } else {
                        for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                            $in = $_xor;
                            '.$encrypt_block.'
                            $_xor = $in;
                            $_plaintext.= substr($_text, $_i, '.$block_size.') ^ $_xor;
                        }
                        $_key = $_xor;
                    }
                    if ($self->continuousBuffer) {
                        $self->decryptIV = $_xor;
                        if ($_start = $_ciphertext_len % '.$block_size.') {
                             $_buffer["xor"] = substr($_key, $_start) . $_buffer["xor"];
                        }
                    }
                    return $_plaintext;
                    ';
                break;
            case CRYPT_MODE_STREAM:
                $encrypt = $init_encrypt.'
                    $_ciphertext = "";
                    '.$encrypt_block.'
                    return $_ciphertext;
                    ';
                $decrypt = $init_decrypt.'
                    $_plaintext = "";
                    '.$decrypt_block.'
                    return $_plaintext;
                    ';
                break;
            // case CRYPT_MODE_CBC:
            default:
                $encrypt = $init_encrypt.'
                    $_ciphertext = "";
                    $_text = $self->pad($_text);
                    $_plaintext_len = strlen($_text);

                    $in = $self->encryptIV;

                    for ($_i = 0; $_i < $_plaintext_len; $_i+= '.$block_size.') {
                        $in = substr($_text, $_i, '.$block_size.') ^ $in;
                        '.$encrypt_block.'
                        $_ciphertext.= $in;
                    }

                    if ($self->continuousBuffer) {
                        $self->encryptIV = $in;
                    }

                    return $_ciphertext;
                    ';

                $decrypt = $init_decrypt.'
                    $_plaintext = "";
                    $_text = str_pad($_text, strlen($_text) + ('.$block_size.' - strlen($_text) % '.$block_size.') % '.$block_size.', chr(0));
                    $_ciphertext_len = strlen($_text);

                    $_iv = $self->decryptIV;

                    for ($_i = 0; $_i < $_ciphertext_len; $_i+= '.$block_size.') {
                        $in = $_block = substr($_text, $_i, '.$block_size.');
                        '.$decrypt_block.'
                        $_plaintext.= $in ^ $_iv;
                        $_iv = $_block;
                    }

                    if ($self->continuousBuffer) {
                        $self->decryptIV = $_iv;
                    }

                    return $self->unpad($_plaintext);
                    ';
                break;
        }

        // Create the $inline function and return its name as string. Ready to run!
        return create_function(
            '$_action, &$self, $_text',
            $init_crypt.'if ($_action == "encrypt") { '.$encrypt.' } else { '.$decrypt.' }'
        );
    }

    /**
     * Holds the lambda_functions table (classwide)
     *
     * Each name of the lambda function, created from
     * _setupInlineCrypt() && _createInlineCryptFunction()
     * is stored, classwide (!), here for reusing.
     *
     * The string-based index of $function is a classwide
     * uniqe value representing, at least, the $mode of
     * operation (or more... depends of the optimizing level)
     * for which $mode the lambda function was created.
     *
     * @return &Array
     */
    public function &getLambdaFunctions()
    {
        static $functions = array();
        return $functions;
    }
}
