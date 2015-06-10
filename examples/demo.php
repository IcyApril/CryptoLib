<?php
/**
 * Demonstration file of the class CryptoLib (v0.8 Christmas)
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

/**
 * Start off my requiring the library in our demo script.
 */
require_once('../src/CryptoLib.php');

/**
 * Some nice debugging messages.
 */
ini_set('display_errors',1);
ini_set('display_startup_errors',1);

use IcyApril\CryptoLib;

/**
 * Standard procedural testers.
 */
$randomInteger = CryptoLib::randomInt(0, 255);
$randomHex = CryptoLib::randomHex(10);
$randomString = CryptoLib::randomString(10);
$repeatPercentage = CryptoLib::checkRandomNumberRepeatability();

$salt = CryptoLib::generateSalt();
$testHash = CryptoLib::hash("test");
$validateHash = CryptoLib::validateHash($testHash, "test");

$encryptedString = CryptoLib::encryptData("Test string.", "passwd");
$decryptedString = CryptoLib::decryptData($encryptedString, 'passwd');

?>

<html>
    <head>
        <title>CryptoLib Tester</title>
        <style>
            body {
                font-family: Helvetica, Arial, sans-serif;
                font-size: 16px;
            }

            a, a:link, a:hover, a:visited, a:active {
                color: #3b5998;
            }

            .container {
                width: 960px;
                margin: 20px auto;
            }

            .container h1 {
                text-align: center;
                margin-bottom: 20px;
            }

            section {
                border-top: thin dashed #C0C0C0;
            }

            .row {
                clear: both;
                padding: 10px 0 30px 0;
                border-bottom: thin dashed #C0C0C0;
            }

            .left {
                width: 720px;
                float: left;
                text-align: left;
            }

            .right {
                width: 240px;
                float: right;
                text-align: right;
            }

            .lead {
                text-align: justify;
                font-size: 1.1em;
                border: medium dashed #C0C0C0;
                padding: 10px;
            }

            .sectionHeader {
                border: thin solid #C0C0C0;
                padding: 10px;
                margin: 80px 20px 20px 20px;
            }
        </style>
    </head>

    <body>
        <div class="container">
            <h1>CryptoLib lives!</h1>
            <p class="lead">
                CrytoLib is alive and working. Below you should see some examples of what CryptoLib is capable, though better documentation is at <a href="http://cryptolib.ju.je">CryptoLib.ju.je</a>.
                Below you will see tables with explanations of the data CryptoLib can produce; each section has an explanation and below them; on the right is the function that will create that data
                and on the left is the data it resulted in.<br />
                You will notice that if you refresh the page the various values will change; that is because all the data is created using live functions on your PHP server, so whenever you refresh all
                the hashed passwords will have a new randomly generated salt and all the encrypted data will change the encrypted data it uses the encryption process. Please open and experiment this file.
            </p>

            <header class="sectionHeader">
                <h2>Pseudorandom</h2>
                <p>
                    CryptoLib has a number of pseudo-random number generators (technically you can only achieve true randomness using quantum phenomena, like using radioactive decay) but this random for
                    most purposes. Below I have sampled out the functions for you.
                </p>
            </header>

            <section id="random">
                <div class="row" id="randomInt">
                    <div class="left">
                        CryptoLib::randomInt(0, 255);
                    </div>
                    <div class="right">
                        <?php echo $randomInteger; ?>
                    </div>
                </div>

                <div class="row" id="randomHex">
                    <div class="left">
                        CryptoLib::randomHex(10);
                    </div>
                    <div class="right">
                        <?php echo $randomHex; ?>
                    </div>
                </div>

                <div class="row" id="randomString">
                    <div class="left">
                        CryptoLib::randomString(10);
                    </div>
                    <div class="right">
                        <?php echo $randomString; ?>
                    </div>
                </div>

                <div class="row" id="checkRandomNumberRepeatability">
                    <div class="left">
                        CryptoLib::checkRandomNumberRepeatability();
                    </div>
                    <div class="right">
                        <?php echo $repeatPercentage; ?>%
                    </div>
                </div>
            </section>

            <header class="sectionHeader">
                <h2>Hashing</h2>
                <p>
                    CryptoLib can hash passwords and other strings (turn something into a cryptographic string but so it can't be easily turned back into the original string).
                    This makes them safe for database entry; you can then use the validateHash function that is outlined below to ensure the hash in the database matches the
                    password the user has provided. The hashes have been truncated to 20 charectars, they are normally 128 characters long. The hashing algorithm is SOHA (Server
                    Oriented Hashing Algorithm) which is derived from PBKDF2 with SHA512 and Whirlpool alternations.
                </p>
            </header>
            <section id="hashing">
                <div class="row" id="generateSalt">
                    <div class="left">
                        $salt = CryptoLib::generateSalt();
                    </div>
                    <div class="right">
                        <?php echo substr($salt, 0, 20); ?>&hellip;
                    </div>
                </div>
                <div class="row" id="hash">
                    <div class="left">
                        $testHash = CryptoLib::hash("test");
                    </div>
                    <div class="right">
                        <?php echo substr($testHash, 0, 20); ?>&hellip;
                    </div>
                </div>
                <div class="row" id="validateHash">
                    <div class="left">
                        $validateHash = CryptoLib::validateHash($testHash, "test", $salt);
                    </div>
                    <div class="right">
                        <?php echo ($validateHash ? "TRUE" : "FALSE"); ?>
                    </div>
                </div>
            </section>

            <header class="sectionHeader">
                <h2>Cascading Encryption</h2>
                <p>
                    CryptoLib can generate encrypted data and decrypt it using the functions outlined below. As above I have truncated the encrypted strings down to 20 charecters.
                    The cryptographic algorithm uses cascade encryption with Rijndael 256, Twofish and Serpent.
                </p>
            </header>

            <section id="encryption">
                <div class="row" id="encryptData">
                    <div class="left">
                        $encryptedString = CryptoLib::encryptData("Test string.", "passwd");
                    </div>
                    <div class="right">
                        <?php echo substr($encryptedString, 0, 20); ?>&hellip;
                    </div>
                </div>
                <div class="row" id="decryptData">
                    <div class="left">
                        $decryptedString = CryptoLib::decryptData($encryptedString, 'passwd');
                    </div>
                    <div class="right">
                        <?php echo $decryptedString; ?>
                    </div>
                </div>
            </section>

        </div>
    </body>
</html>