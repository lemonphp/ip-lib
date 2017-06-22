<?php

namespace Lemon\Lib\Tests;

use Lemon\Lib\IP;

class IPTest extends \PHPUnit_Framework_TestCase
{

    use NonPublicAccessableTrait;

    /**
     * Data provider for test constructor
     *
     * @return array
     */
    public function dataForTestConstructs()
    {
        return [
            ['127.0.0.1', IP::IP_V4],
            ['::127.0.0.1', IP::IP_V6],
        ];
    }

    /**
     * Data provider for test converts
     *
     * @return array
     */
    public function dataForTestConverts()
    {
        return [
            [
                '127.0.0.1',
                '0b01111111000000000000000000000001',
                '0x7f000001',
                '2130706433',
                inet_pton('127.0.0.1')
            ],
            [
                '192.168.11.3',
                '0b11000000101010000000101100000011',
                '0xc0a80b03',
                '3232238339',
                inet_pton('192.168.11.3')
            ],
            [
                '2001:0DB8:AC10:FE01::',
                '0b00100000000000010000110110111000101011000001000011111110000000010000000000000000000000000000000000000000000000000000000000000000',
                '0x20010db8ac10fe010000000000000000',
                '42540766464534556858822563802705297408',
                inet_pton('2001:db8:ac10:fe01::')
            ],
        ];
    }

    /**
     * Data provider for test parse
     *
     * @return array
     */
    public function dataForTestParse()
    {
        return [
            ['42540766464534556858822563802705297408', '2001:db8:ac10:fe01::'],
            ['0xc0a80b03', '192.168.11.3'],
            ['0b01111111000000000000000000000001', '127.0.0.1'],
            ['2130706433', '::127.0.0.1'],
//            ['2130706433', '127.0.0.1'],
        ];
    }

    /**
     * Test constructor method
     *
     * @param string $ip
     * @param string $version
     * @dataProvider dataForTestConstructs
     */
    public function testContructor($ip, $version)
    {
        $obj = new IP($ip);

        $this->assertSame($version, $this->getNonPublicProperty($obj, 'version'));
        $this->assertSame(inet_pton($ip), $this->getNonPublicProperty($obj, 'inAddr'));
    }

    /**
     * Test method <code>IP::__constructor()</code> with invalid IP case
     *
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage Invalid IP address format
     */
    public function testContructorWithInvalidIP()
    {
        new IP('127.0.0.0.1');
    }

    /**
     * Test method <code>IP::getVersion()</code>
     *
     * @param string $ip
     * @param string $version
     * @dataProvider dataForTestConstructs
     */
    public function testGetVersion($ip, $version)
    {
        $this->assertSame($version, (new IP($ip))->getVersion());
    }

    /**
     * Test method <code>IP::getMaxPrefixLength()</code>
     *
     * @param string $ip
     * @param string $version
     * @dataProvider dataForTestConstructs
     */
    public function testGetMaxPrefixLength($ip, $version)
    {
        $maxLength = (IP::IP_V4 === $version) ? IP::MAX_PREFIX_LENGTH_V4 : IP::MAX_PREFIX_LENGTH_V6;

        $this->assertSame($maxLength, (new IP($ip))->getMaxPrefixLength());
    }

    /**
     * Test method <code>IP:getNumberOctets</code>
     *
     * @param string $ip
     * @param string $version
     * @dataProvider dataForTestConstructs
     */
    public function testGetNumberOctets($ip, $version)
    {
        $numberOctets = (IP::IP_V4 === $version) ? IP::NUMBER_OCTETS_V4 : IP::NUMBER_OCTETS_V6;

        $this->assertSame($numberOctets, (new IP($ip))->getNumberOctets());
    }

    /**
     * Test methods: <code>IP::__toString()</code>, <code>IP::toBin()</code>, <code>IP::toHex()</code>,
     * <code>IP::toNumeric()</code>, <code>IP::toInAddr()</code>
     *
     * @param string  $ip
     * @param string  $bin
     * @param string  $hex
     * @param numeric $numeric
     * @param string  $inAddr
     * @dataProvider dataForTestConverts
     */
    public function testConverts($ip, $bin, $hex, $numeric, $inAddr)
    {
        $obj = new IP($ip);

        $this->assertSame($bin, $obj->toBin());
        $this->assertSame($hex, $obj->toHex());
        $this->assertEquals($numeric, $obj->toNumeric());
        $this->assertSame($inAddr, $obj->toInAddr());
    }

    /**
     * Test method <code>IP::parse()</code>
     *
     * @param string $input
     * @param string $ip    Expected IP
     * @dataProvider dataForTestParse
     */
    public function testParse($input, $ip)
    {
        $this->assertSame(inet_pton($ip), IP::parse($input)->toInAddr());
    }

//    public function testFromBin();
//
//    public function testFromHex();
//
//    public function testFromNumeric();
//
//    public function testFromInAddr();

    public function testCmp()
    {
        $this->assertEquals(0, IP::cmp('127.0.0.1', '127.0.0.1'));
    }
//    public function testParse();
//
//    public function testValidate();
}