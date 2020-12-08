<?php

use IcyApril\CryptoLib;
use PHPUnit\Framework\TestCase;

class CryptoLibTest extends TestCase
{
    public function testChangePepper()
    {
        $hashOrig = \IcyApril\CryptoLib::hash("test string");
        $this->assertNotEmpty($hashOrig);

        \IcyApril\CryptoLib::changePepper("TEST PEPPER");

        $hashNew = \IcyApril\CryptoLib::hash("test string");
        $this->assertNotEmpty($hashNew);

        $this->assertNotEquals($hashOrig, $hashNew);

    }

    public function testRandomHex()
    {
        $rand = \IcyApril\CryptoLib::randomHex();
        $this->assertEquals(128, strlen($rand));

        $rand = \IcyApril\CryptoLib::randomHex(256);
        $this->assertEquals(256, strlen($rand));

        $rand = \IcyApril\CryptoLib::randomHex(128);
        $this->assertEquals(128, strlen($rand));

        $rand = \IcyApril\CryptoLib::randomHex(64);
        $this->assertEquals(64, strlen($rand));

        $rand = \IcyApril\CryptoLib::randomHex(2);
        $this->assertEquals(2, strlen($rand));

        $rand = \IcyApril\CryptoLib::randomHex(2);
        $this->assertEquals(2, strlen($rand));

        $this->expectException(Exception::class);
        \IcyApril\CryptoLib::randomHex(0);
    }

    public function testRandomInt()
    {
        $rand      = \IcyApril\CryptoLib::randomInt(0, 1);
        $randRange = $rand == 0 || 1;
        $this->assertTrue($randRange);

        $rand      = \IcyApril\CryptoLib::randomInt(0, 100);
        $randRange = $rand >= 0 && $rand <= 100;
        $this->assertTrue($randRange);

        $randB     = \IcyApril\CryptoLib::randomInt(0, 100);
        $randRange = $randB >= 0 && $randB <= 100;
        $this->assertTrue($randRange);

        $this->expectException(Exception::class);
        \IcyApril\CryptoLib::randomInt(0, 0);

        $this->expectException(Exception::class);
        \IcyApril\CryptoLib::randomInt(-10, -1);

        $this->expectException(Exception::class);
        \IcyApril\CryptoLib::randomInt(-10, 0);
    }

    public function testRandomString()
    {
        $rand = \IcyApril\CryptoLib::randomString(5);
        $this->assertEquals(5, strlen($rand));

        $rand = \IcyApril\CryptoLib::randomString(1);
        $this->assertEquals(1, strlen($rand));

        $this->expectException(Exception::class);
        \IcyApril\CryptoLib::randomString(0);

        $this->expectException(Exception::class);
        \IcyApril\CryptoLib::randomString(-10);
    }

    public function testCheckRandomNumberRepeatability()
    {
        $repeatability = \IcyApril\CryptoLib::checkRandomNumberRepeatability();
        $this->assertLessThan(1, $repeatability);
        $this->assertGreaterThan(0, $repeatability);

        $repeatability = \IcyApril\CryptoLib::checkRandomNumberRepeatability(
            function ($min, $max) {
                return \IcyApril\CryptoLib::randomInt($min, $max);
            }
        );
        $this->assertLessThan(1, $repeatability);
        $this->assertGreaterThan(0, $repeatability);

        $repeatability = \IcyApril\CryptoLib::checkRandomNumberRepeatability(
            function ($min, $max) {
                return 5;
            }
        );
        $this->assertEquals(1, $repeatability);

        $repeatability = \IcyApril\CryptoLib::checkRandomNumberRepeatability(
            function ($min, $max) {
                return microtime() . rand(0, 10000000000);
            }
        );
        $this->assertEquals(0, $repeatability);

        $this->expectException(Exception::class);
        \IcyApril\CryptoLib::checkRandomNumberRepeatability(null, 1, 100);

        $this->expectException(Exception::class);
        \IcyApril\CryptoLib::checkRandomNumberRepeatability(null, 1, 1);

        $this->expectException(Exception::class);
        \IcyApril\CryptoLib::checkRandomNumberRepeatability(null, 5, 1);
    }

    public function testGenerateSalt()
    {
        $saltA = \IcyApril\CryptoLib::generateSalt();
        $this->assertEquals(127, strlen($saltA));

        $saltB = \IcyApril\CryptoLib::generateSalt();
        $this->assertEquals(127, strlen($saltB));
        $this->assertNotEquals($saltA, $saltB);
    }

    public function testHash()
    {
        $hashA = \IcyApril\CryptoLib::hash("test");
        $this->assertEquals(256, strlen($hashA));

        $hashB = \IcyApril\CryptoLib::hash("test");
        $this->assertNotEquals($hashA, $hashB);

        $hashA = \IcyApril\CryptoLib::hash("test", "salt");
        $hashB = \IcyApril\CryptoLib::hash("test", "salt");
        $this->assertEquals($hashA, $hashB);

        $hashA = \IcyApril\CryptoLib::hash("test", "");
        $hashB = \IcyApril\CryptoLib::hash("test", "");
        $this->assertNotEquals($hashA, $hashB);

        $hashA = \IcyApril\CryptoLib::hash("test", "salt", 96);
        $hashB = \IcyApril\CryptoLib::hash("test", "salt", 100);
        $this->assertNotEquals($hashA, $hashB);

        $hashA = \IcyApril\CryptoLib::hash("test", "salt", 96);
        $hashB = \IcyApril\CryptoLib::hash("test", "salt", 100);
        $this->assertNotEquals($hashA, $hashB);
    }

    public function testValidateHash()
    {
        $hash  = \IcyApril\CryptoLib::hash("test");
        $valid = \IcyApril\CryptoLib::validateHash($hash, "test");
        $this->assertTrue($valid);

        $this->expectException(\Exception::class);
        $hash = \IcyApril\CryptoLib::hash("test");
        \IcyApril\CryptoLib::validateHash("test", $hash);
    }

    public function testEncryptData()
    {
        $enc = \IcyApril\CryptoLib::encryptData("test data", "test key");
        $this->assertNotEquals("test data", $enc);

        $dec = \IcyApril\CryptoLib::decryptData($enc, "test key");
        $this->assertEquals("test data", $dec);

        $this->expectException(\Exception::class);
        \IcyApril\CryptoLib::encryptData("", "test key");
    }
}
