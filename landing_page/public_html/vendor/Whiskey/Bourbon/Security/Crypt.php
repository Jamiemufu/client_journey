<?php


namespace Whiskey\Bourbon\Security;


use InvalidArgumentException;
use Whiskey\Bourbon\Exception\MissingDependencyException;
use Whiskey\Bourbon\Exception\Security\Crypt\AlgorithmNotSupportedException;


/**
 * Crypt class
 * @package Whiskey\Bourbon\Security
 */
class Crypt
{


    protected $_default_key = 'bd305e29843227105aa7c820ddef8e2a6b4c88831abd84a8702370d401b44245';
    protected $_algorithms  = [];


    /**
     * Set the default key
     * @param  string $key Key
     * @return bool        Whether the key was set
     */
    public function setDefaultKey($key = '')
    {

        $key = (string)$key;

        if ($key != '')
        {

            $this->_default_key = $key;

            return true;

        }

        return false;

    }


    /**
     * Check if an algorithm is supported
     * @param  string $algorithm Name of algorithm
     * @return bool              Whether the algorithm is supported
     */
    public function isAlgorithmSupported($algorithm = '')
    {

        if (empty($this->_algorithms))
        {
            $this->_algorithms = array_map('strtolower', hash_algos());
        }

        return in_array($algorithm, $this->_algorithms);

    }


    /**
     * Hash a string incorporating a salt (if not passed, the contents of
     * $this->_default_key will be used instead)
     * @param  string $string    String to hash
     * @param  string $salt      Optional salt
     * @param  string $algorithm Hash algorithm
     * @return string            Hashed string
     * @throws AlgorithmNotSupportedException if the algorithm is not supported
     */
    public function hash($string = '', $salt = null, $algorithm = 'sha512')
    {

        if (!$this->isAlgorithmSupported($algorithm))
        {
            throw new AlgorithmNotSupportedException('Unsupported algorithm \'' . $algorithm . '\'');
        }
    
        if (is_null($salt))
        {
            $salt = $this->_default_key;
        }
        
        return hash($algorithm, $salt . $string . $salt, false);
    
    }


    /**
     * Create a HMAC hash of a string
     * @param  string $string    String to hash
     * @param  string $salt      Optional salt
     * @param  string $algorithm Hash algorithm
     * @return string            Hashed string
     * @throws AlgorithmNotSupportedException if the algorithm is not supported
     */
    public function hashHmac($string = '', $salt = null, $algorithm = 'sha512')
    {

        if (!$this->isAlgorithmSupported($algorithm))
        {
            throw new AlgorithmNotSupportedException('Unsupported algorithm \'' . $algorithm . '\'');
        }

        if (is_null($salt))
        {
            $salt = $this->_default_key;
        }
        
        return hash_hmac($algorithm, $string, $salt, false);

    }


    /**
     * Encrypt a string with the Rijndael 256 cipher, incorporating a custom
     * key (if not passed, the contents of $this->_default_key will be used
     * instead)
     * @param  string $string      String to encrypt
     * @param  string $key         Optional key
     * @param  bool   $true_random Whether to use a true random IV source
     * @return string              Encrypted string, Base64-encoded
     * @throws MissingDependencyException if mcrypt is missing
     */
    public function encrypt($string = '', $key = null, $true_random = false)
    {

        if (!extension_loaded('mcrypt'))
        {
            throw new MissingDependencyException('mcrypt extension missing');
        }

        /*
         * Initialise the encryption descriptor and determine the required IV
         * size
         */
        $td      = mcrypt_module_open('rijndael-256', '', 'cbc', '');
        $iv_size = mcrypt_enc_get_iv_size($td);
        
        /*
         * Generate and trim the key
         */
        $key = ($key !== null) ? $key : $this->_default_key;
        $key = hash('sha512', $key);
        $key = mb_substr($key, 0, $iv_size);
        
        /*
         * Generate a random initialisation vector
         */
        $random_source = ($true_random ? MCRYPT_DEV_RANDOM : MCRYPT_DEV_URANDOM);
        $iv            = mcrypt_create_iv($iv_size, $random_source);

        /*
         * Encrypt the string
         */
        mcrypt_generic_init($td, $key, $iv);
        $result = mcrypt_generic($td, $string);
        
        /*
         * Tidy up
         */
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        
        /*
         * Return a package containing the encrypted string and the
         * initialisation vector
         */
        return base64_encode(json_encode(['result' => base64_encode($result),
                                          'iv'     => base64_encode($iv)]));

    }


    /**
     * Decrypt a string with the Rijndael 256 cipher, incorporating a custom
     * key (if not passed, the contents of $this->_default_key will be used
     * instead)
     * @param  string $string String encrypted by _encrypt()
     * @param  string $key    Optional key
     * @return string         Decrypted string
     * @throws MissingDependencyException if mcrypt is missing
     * @throws InvalidArgumentException if the input string or key are not valid
     */
    public function decrypt($string = '', $key = null)
    {

        if (!extension_loaded('mcrypt'))
        {
            throw new MissingDependencyException('mcrypt extension missing');
        }

        /*
         * Initialise the encryption descriptor and determine the required IV
         * size
         */
        $td      = mcrypt_module_open('rijndael-256', '', 'cbc', '');
        $iv_size = mcrypt_enc_get_iv_size($td);

        /*
         * Generate and trim the key
         */
        $key = ($key !== null) ? $key : $this->_default_key;
        $key = hash('sha512', $key);
        $key = mb_substr($key, 0, $iv_size);

        $data = base64_decode($string);

        if ($data = json_decode($data))
        {

            /*
             * Decrypt the string
             */
            mcrypt_generic_init($td, $key, base64_decode($data->iv));
            $result = mdecrypt_generic($td, base64_decode($data->result));
            $result = rtrim($result, "\0");

            /*
             * Tidy up
             */
            mcrypt_generic_deinit($td);
            mcrypt_module_close($td);

            /*
             * Return the result
             */
            return $result;

        }

        throw new InvalidArgumentException('Decryption error - invalid string or key');

    }


}