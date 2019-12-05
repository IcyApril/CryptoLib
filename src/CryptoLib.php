<?php
namespace IcyApril;

/**
 * CryptoLib
 * 
 * An open-source PHP Cryptography library, originally by Junade Ali <junade@cloudflare.com>.
 * 
 * @author Junade Ali
 * @author Damir Mitrovic (v2.0.0)
 * @version 2.0.0
 * @license AGPL
 */
class CryptoLib
{
    // Please change the pepper below for each project (do not change after data has been hashed using this class).
    private static $pepper = '+?*er*F+AY%vJ,tmwt$e[AzIy|(}(;W7]-Gw}Nazr}iD}--vA}+Jq%+$LCPsP#J#';

    public static function changePepper(string $new)
    {
        if ((empty($new))) {
            throw new \Exception('The new pepper value must be a non-null string');
        }

        self::$pepper = $new;
    }

    /**
     * Will return openssl_random_pseudo_bytes with desired length if the server uses 
     * a cryptographically strong algorithm. Throws an exception otherwise.
     *
     * @param int $length
     * @return int $bytes
     * @throws Exception
     */
    private static function pseudoBytes(int $length = 1)
    {
        $bytes = \openssl_random_pseudo_bytes($length, $strong);

        if ($strong == true) {
            return $bytes;
        }

        throw new \Exception (
            'ERROR: Your server did not use a cryptographically secure algorithm 
                    to generate a random string. This indicates a broken or old system. 
                    Because this is insecure, we\'re refusing to work.'
        );
    }

    /**
     * Random hex generator using the pseudoBytes function in this class.
     *
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function randomHex(int $length = 128)
    {
        if ($length < 1) {
            throw new \Exception('Length must be a positive integer.');
        }

        $bytes = \ceil($length / 2);
        $hex   = \bin2hex(self::pseudoBytes($bytes));

        return $hex;
    }

    /**
     * Random integer generator using the pseudoBytes function in this class.
     *
     * @param $min
     * @param $max
     * @return mixed
     * @throws \Exception
     */
    public static function randomInt(int $min, int $max)
    {
        if ($max <= $min) {
            throw new \Exception('Minimum must be an integer less than maximum.');
        }

        if ($max < 0 || $min < 0) {
            throw new \Exception('Minimum and maximum must both be positive integers.');
        }

        $difference = $max - $min;

        for ($power = 8; \pow(2, $power) < $difference; $power = $power * 2) {
            ;
        }
        $powerExp = $power / 8;

        do {
            $randDiff = \hexdec(\bin2hex(self::pseudoBytes($powerExp)));
        } while
        ($randDiff > $difference);

        return $min + $randDiff;
    }

    /**
     * Random string generator using the randomInt function in this class.
     *
     * @param $length
     * @return string
     * @throws \Exception
     */
    public static function randomString(int $length)
    {
        if ($length < 1) {
            throw new \Exception('Length must be a positive integer.');
        }

        $charactersArr = \array_merge(\range('a', 'z'), \range('A', 'Z'), \range('0', '9'));
        $charactersCount = \count($charactersArr);
        $stringArr       = array();

        for ($character = 0; $character !== $length; $character++) {
            $stringArr[$character] = $charactersArr[self::randomInt(0, $charactersCount - 1)];
        }

        return \implode($stringArr);
    }

    /**
     * When an anonymous function is passed as $function it will check if a random number has repeated itself.
     *
     * @param null $function - anonymous function
     * @param int $numbers - Amount of numbers to check
     * @param int $checks - How many times to check and the max random number
     * @return float
     * @throws \Exception
     */
    public static function checkRandomNumberRepeatability($function = null, int $numbers = 5, int $checks = 1000)
    {
        if ($numbers <= 1) {
            throw new \Exception('Numbers must be an integer greater than 1.');
        }

        if ($checks <= 1) {
            throw new \Exception('Checks must be an integer greater than 1.');
        }

        if (!(\is_callable($function) === true)) {
            $function = function($min, $max) {
                return self::randomInt($min, $max);
            };
        }

        $repeats = 0;

        for ($check = 0; $check !== $numbers; $check++) {
            $$check = $function(0, $checks);

            for ($repeat = 0; $repeat !== $checks; $repeat++) {
                if ($$check === $function(0, $checks)) {
                    $repeats++;
                }
            }
        }

        return $repeats / ($checks * $numbers);
    }


    /**
     * Salt generation using our random string generator (32 charectars).
     * @return string
     */
    public static function generateSalt()
    {
        return self::randomString(127);
    }

    /**
     * Hash which will recursively rehash data 64 times (each time being hashed 32 times PBKDF2 standard)
     * alternating between Whirlpool and SHA512.
     *
     * @param $data
     * @param $salt
     * @param int $iterations - Recommended to leave at the default of 96, ensure it is divisible by 3 (to get a precise amount of iterations).
     * @return mixed
     * @throws \Exception
     */
    public static function hash($data, $salt = null, int $iterations = 96)
    {

        if (empty($salt) || \is_null($salt)) {
            $salt = self::generateSalt();
        }

        if ((\in_array("whirlpool", \hash_algos()) && \in_array("sha512", \hash_algos())) !== true) {
            throw new \Exception ('Your PHP installation does not support Whirlpool and/or SHA512 hashing.');
        }

        $outerIterations = \ceil(($iterations / 3) * 2);
        $pbkdf2Iteration = \ceil($iterations / 3);

        $hashed = $data;

        $algorithm = "whirlpool";
        $iteration = 1;
        $peppered  = $hashed . self::$pepper;
        while ($iteration <= $outerIterations) {

            $hashed = \hash_pbkdf2($algorithm, $peppered, $salt, $pbkdf2Iteration, 0);

            $algorithm = self::flipHashAlgo($algorithm);

            $iteration++;
        }

        if (($data === $hashed) || ($data === $data . self::$pepper)) {
            throw new \Exception ('Hash failed.');
        }

        return $salt . '_' . $hashed;

    }

    private static function flipHashAlgo($algorithm)
    {
        if ($algorithm === 'whirlpool') {
            return 'sha512';
        } else if ($algorithm === 'sha512') {
            return 'whirlpool';
        }

        throw new \Exception ('Something went horribly wrong.');
    }

    /**
     * Validate hash by providing the hashed string (e.g. from password field in database) 
     * with a plain-text input (e.g. password field from user).
     *
     * @param $original - Original hash input.
     * @param $input - Hash to test against.
     * @throws \Exception
     * @return bool
     */
    public static function validateHash($original, $input)
    {
        $originalExploded = \explode('_', $original);

        if (sizeof($originalExploded) !== 2) {
            throw new \Exception("Invalid hash.");
        }

        $salt = $originalExploded[0];
        $hash = $originalExploded[1];

        $rehashed = \explode('_', self::hash($input, $salt));

        if (hash_equals($hash, $rehashed[1])) {
            return true;
        }

        return false;
    }

    /**
     * Encrypt data using a specified key; uses cascading layered encryption with hash salting.
     *
     * @param $data
     * @param $key
     * @return string
     * @throws \Exception
     */
    public static function encryptData($data, string $key)
    {
        if (empty($data)) {
            throw new \Exception('Some data is required to encrypt.');
        }
        if ($key === null) {
            throw new \Exception('Key cannot be null.');
        }

        $salt = self::generateSalt();
        $cipher = 'aes-256-gcm';

        $ivSize = \openssl_cipher_iv_length($cipher);
        $iv = self::pseudoBytes($ivSize);
        $key = self::hash($key, $salt);
        $key = \hash('sha3-512', $key, true);
        $tag = self::pseudoBytes(16);
        $cipherText = \openssl_encrypt($data, $cipher, $key, $options=0, $iv, $tag);

        return $salt . '_' . \base64_encode($iv) . '_' . $cipherText . '_' . \base64_encode($tag);
    }

    /**
     * Decrypt data which has been encrypted with the encryptData function.
     *
     * @param $data
     * @param $key
     * @return string
     * @throws \Exception
     */
    public static function decryptData($data, string $key)
    {
        if (empty($data)) {
            throw new \Exception('Some data is required to decrypt.');
        }
        if ($key === null) {
            throw new \Exception('Key cannot be null.');
        }

        $explodedData = \explode('_', $data);
        $cipher = 'aes-256-gcm';

        $salt = $explodedData[0];
        $iv = \base64_decode($explodedData[1], true);
        $cipherText = $explodedData[2];
        $tag = \base64_decode($explodedData[3], true);

        unset($explodedData);

        $key = self::hash($key, $salt);
        $key = \hash('sha3-512', $key, true);

        $data = \openssl_decrypt($cipherText, $cipher, $key, 0, $iv, $tag);

        if ((isset($data)) && (\strlen($data) > 0)) {
            return $data;
        }

        throw new \Exception('Decryption failed (likely incorrect password).');
    }
}
