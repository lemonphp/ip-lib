<?php
/**
 * This file is part of `lemonphp/ip-lib` project.
 *
 * (c) 2015-2016 LemonPHP Team
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Lemon\Lib;

/**
 * Netmask class
 *
 * Parse and converts a netmask
 */
class Netmask extends IP
{
    /**
     * Make netmask object from a human readable IP address (Eg: 255.255.255.0)
     *
     * @param string $ip    A human readable IP address
     * @throws \InvalidArgumentException
     */
    public function __construct($ip)
    {
        parent::__construct($ip);
        if (!preg_match('/^0b1*0*$/', $this->toBin())) {
            throw new \InvalidArgumentException();
        }
    }

    /**
     * Get meta data for debugging
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'ip' => $this->ip,
            'prefix_length' => $this->toPrefixLength(),
        ];
    }

    /**
     * Get network prefix length
     *
     * @return int
     */
    public function toPrefixLength()
    {
        return strlen(rtrim($this->toBin(), '0'));
    }

    /**
     * Validate a human readable IP address is netmask
     *
     * @param string $ip    A human readable IP address
     * @return boolean
     */
    public static function validate($ip)
    {
        if (!parent::validate($ip)) {
            return false;
        }

        try {
            new self($ip);
        } catch (\Exception $e) {
            return false;
        }

        return true;
    }

    /**
     * Converts from network prefix length
     *
     * @param int    $length
     * @param string $version
     * @return \self
     */
    public static function fromPrefixLength($length, $version = IP::IP_V4)
    {
        $maxPrefixLengths = [
            IP::IP_V4 => IP::MAX_PREFIX_LENGTH_V4,
            IP::IP_V6 => IP::MAX_PREFIX_LENGTH_V6,
        ];

        if (!isset($maxPrefixLengths[$version])) {
            throw new \InvalidArgumentException();
        }

        $maxPrefixLength = $maxPrefixLengths[$version];

        if (!is_numeric($length) || !($length >= 0 && $length <= $maxPrefixLength)) {
            throw new \InvalidArgumentException();
        }

        $bin = str_pad(str_pad('', (int) $length, '1'), $maxPrefixLength, '0');

        return self::fromBin($bin);
    }
}
