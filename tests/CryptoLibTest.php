<?php

/**
 * User: junade
 * Date: 23/09/2016
 * Time: 18:03
 */
class CryptoLibTest extends PHPUnit_Framework_TestCase
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

        $rand = \IcyApril\CryptoLib::randomHex(0);
        $this->assertEquals(0, strlen($rand));
    }

    public function testRandomInt()
    {
        $rand = \IcyApril\CryptoLib::randomInt(0, 1);
        $this->assertEquals($rand, 0);

        $this->expectException(Exception::class);
        \IcyApril\CryptoLib::randomInt(0, 0);
    }
}
