<?php
namespace IcyApril;
/**
 * Class CryptoLib (v0.8 Christmas)
 * Created by Junade Ali
 * Requires OpenSSL, MCrypt > 2.4.x, PHP 5.3.0+
 */

/*
    CryptoLib is an open-source PHP Cryptography library.
    Copyright (C) 2014  Junade Ali

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

class CryptoLib {

    // Please change the pepper below for each project (do not change after data has been hashed using this class).
    private static $pepper = '+?*er*F+AY%vJ,tmwt$e[AzIy|(}(;W7]-Gw}Nazr}iD}--vA}+Jq%+$LCPsP#J#';

    // Ciphers used, in order of use, if you change this after encryption you will not be able to decrypt, they must support MCRYPT_MODE_CBC:
    public static $mcryptCiphers = array(\MCRYPT_SERPENT, \MCRYPT_TWOFISH, \MCRYPT_RIJNDAEL_256);

    public function changePepper ($new) {

        if ((empty($new)) || (!is_string($new))) {
            throw new \Exception("You must set a pepper and it cannot change the pepper to something that isn't a string.");
        }

        self::$pepper = $new;
    }

    /**
     * Will return openssl_random_pseudo_bytes with desired length is $strong is set to true.
     * @param int $length
     * @throws \Exception
     * @returns int $bytes
     */
    private static function pseudoBytes ($length = 1) {
        $bytes = \openssl_random_pseudo_bytes($length, $strong);

        if ($strong === TRUE) {
            return $bytes;
        } else {
            throw new \Exception ('Insecure server! (OpenSSL Random byte generation insecure.)');
        }
    }

    /**
     * Random hex generator using pseudoBytes function in this class.
     * @param int $length
     * @return string
     * @throws \Exception
     */
    public static function randomHex ($length = 128) {

        $bytes = \ceil($length/2);
        $hex = \bin2hex(self::pseudoBytes($bytes));
        return $hex;
    }

    /**
     * Random integer generator using pseudoBytes function in this class.
     * @param $min
     * @param $max
     * @return mixed
     * @throws \Exception
     */
    public static function randomInt ($min, $max) {

        if ($max <= $min) {
            throw new \Exception('Minimum equal or greater than maximum!');
        }

        if ($max < 0 || $min < 0) {
            throw new \Exception('Only positive integers supported for now!');
        }

        $difference = $max - $min;

        for ($power = 8; \pow(2, $power) < $difference; $power = $power*2);
        $powerExp = $power/8;

        do {
            $randDiff = \hexdec(\bin2hex(self::pseudoBytes($powerExp)));
        } while
        ($randDiff > $difference);

        return $min + $randDiff;

    }

    /**
     * Random string generator using randomInt function in this class.
     * @param $length
     * @return string
     * @throws \Exception
     */
    public static function randomString ($length) {

        $charactersArr = \array_merge(\range('a', 'z'), \range('A', 'Z'), \range('0', '9'));

        $charactersCount = \count($charactersArr);
        $stringArr = array();

        for ($character = 0; $character !== $length; $character++) {
            $stringArr[$character] = $charactersArr[self::randomInt(0, $charactersCount-1)];
        }

        return \implode($stringArr);

    }

    /**
     * When an anonymous function is passed as $function it will check if a random number has repeated itself.
     * @param null $function - anonymous function
     * @param int $numbers - Amount of numbers to check
     * @param int $checks - How many times to check and the max random number
     * @return float
     */
    public static function checkRandomNumberRepeatability ($function = NULL, $numbers = 5, $checks = 1000) {

        if (!\is_callable($function)) {
            $function = function($min, $max) {
                return self::randomInt($min, $max);
            };

            $repeats = 0;

            for ($check = 0; $check !== $numbers; $check++) {

                $$check = $function(0, $checks);

                for ($repeat = 0; $repeat !== $checks; $repeat++) {
                    if ($$check === $function(0, $checks)) {
                        $repeats++;
                    }
                }

            }

            return $repeats/($checks*$numbers);

        }

        throw new \Exception("You must provide an executable anonymous function as the first argument of the function.");

    }


    /**
     * Salt generation using classes random string generator (32 charectars).
     * @return string
     */
    public static function generateSalt () {
        return self::randomString(127);
    }

    /**
     * Hash which will recursively rehash data 64 times (each time being hashed 32 times PBKDF2 standard) alternating between Whirlpool and SHA512.
     * @param $data
     * @param $salt
     * @param bool $raw_output
     * @param int $iterations - Recommended to leave at the default of 96, ensure it is divisible by 3 (to get a precise amount of iterations).
     * @return mixed
     * @throws \Exception
     */
    public static function hash ($data, $salt = NULL, $raw_output = FALSE, $iterations = 96) {

        if (empty($salt) || \is_null($salt)) {
            $salt = self::generateSalt();
        }

        if ((\in_array("whirlpool", \hash_algos()) && \in_array("sha512", \hash_algos())) !== TRUE) {
            throw new \Exception ('Your PHP installation does not support Whirlpool or SHA512 hashing.');
        }

        $outerIterations = \ceil(($iterations/3)*2);
        $pbkdf2Iteration = \ceil($iterations/3);

        $hashed = $data;

        $algorithm = "whirlpool";
        $iteration = 1;
        $peppered = $hashed.self::$pepper;
        while ($iteration <= $outerIterations) {

            $hashed = \hash_pbkdf2($algorithm, $peppered, $salt, $pbkdf2Iteration, 0, $raw_output);

            if ($algorithm == "whirlpool") {
                $algorithm = "sha512";
            } else {
                $algorithm = "whirlpool";
            }
            $iteration++;
        }

        if (($data === $hashed) || ($data === $data.self::$pepper)) {
            throw new \Exception ('Hash failed.');
        } else {
            return $salt.'_'.$hashed;
        }
    }

    /**
     * Validate hash by providing the hashed string (e.g. from password field in database) with a plain-text input (e.g. password field from user).
     * @param $original
     * @param $input
     * @param $salt
     * @return bool
     * @throws \Exception
     */
    public static function validateHash ($original, $input) {

        $originalExploded = \explode('_', $original);

        $salt = $originalExploded[0];
        $hash = $originalExploded[1];

        $rehashed = \explode('_', self::hash($input, $salt));

        if ($hash === $rehashed[1]) {
            return TRUE;
        } else {
            return FALSE;
        }

    }

    /**
     * Check MCrypt supports the specified ciphers.
     * @return bool
     * @throws \Exception
     */
    protected static function checkMCrypt () {
        foreach (self::$mcryptCiphers as $cipher) {
            $ivSize = \mcrypt_get_iv_size($cipher, \MCRYPT_MODE_CBC);
            if (!($ivSize % 16) && !($ivSize > 0)) {
                throw new \Exception ('Your MCrypt version is too old and does not support the Rijndael, Serpant or Twofish ciphers (or the MCrypt ciphers have been changed).');
            }
        }
        return TRUE;
    }

    /**
     * Encrypt data using a specified key; uses cascading layered encryption with hash salting.
     * @param $data
     * @param $key
     * @return string
     * @throws \Exception
     */
    public static function encryptData ($data, $key) {

        self::checkMcrypt();

        $salt = self::generateSalt();

        foreach (self::$mcryptCiphers as $cipher) {

            $ivSize = \mcrypt_get_iv_size($cipher, \MCRYPT_MODE_CBC);
            $iv = \mcrypt_create_iv($ivSize, \MCRYPT_RAND);
            $key = self::hash($key, $salt);
            $key = \hash('SHA256', $key, true);
            $cipherText = \mcrypt_encrypt($cipher, $key, $data, \MCRYPT_MODE_CBC, $iv);
            $data = \base64_encode($iv)."_".\base64_encode($cipherText);

        }

        $return = $salt."_".$data;

        return $return;

    }

    /**
     * Decrypt data which has been encrypted with the encryptData function.
     * @param $data
     * @param $key
     * @return string
     * @throws \Exception
     */
    public static function decryptData ($data, $key) {

        self::checkMCrypt();

        $cipherCount = \count(self::$mcryptCiphers);
        $explodedData = \explode('_', $data);

        $salt = $explodedData[0];
        \array_shift($explodedData);

        $data = \implode('_', $explodedData);
        unset($explodedData);

        $hashes = array();

        for($hash = 1; $hash <= $cipherCount; $hash++) {

            $key = self::hash($key, $salt);
            $key = \hash('SHA256', $key, true);
            \array_unshift($hashes, $key);

        }

        $mcryptCiphersInverted = \array_reverse(self::$mcryptCiphers);

        foreach ($mcryptCiphersInverted as $num => $cipher) {

            $explodedData = \explode('_', $data);
            $data = \mcrypt_decrypt($cipher, $hashes[$num], \base64_decode($explodedData[1]), \MCRYPT_MODE_CBC, \base64_decode($explodedData[0]));
            $data = \rtrim($data, "\0");

            unset($explodedData);

        }

        if ((isset($data)) && (\strlen($data) > 0)) {
            return $data;
        } else {
            throw new \Exception('Decryption failed (likely incorrect password).');
        }

    }



}
