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
 * IP class
 *
 * Parse, get version and converts an IP
 *
 * Usage:
 * <pre><code>
 * $ip1 = new IP('192.168.1.1');
 * echo $ip1->getVersion(); // IPv4
 *
 * $ip2 = new IP('fc00::');
 * echo $ip2->getVersion(); // IPv6
 * </code></pre>
 *
 * @see https://en.wikipedia.org/wiki/IP_address
 */
class IP
{
    /**
     * @const string    IP Version 4
     */
    const IP_V4 = 'IPv4';

    /**
     * @const string    IP Version 6
     */
    const IP_V6 = 'IPv6';

    /**
     * Max network prefix length (the number bits used for the network part)
     *
     * @const int   Max network prefix length for IPv4
     */
    const MAX_PREFIX_LENGTH_V4 = 32;

    /**
     * Max network prefix length (the number bits used for the network part)
     *
     * @const int   Max network prefix length for IPv6
     */
    const MAX_PREFIX_LENGTH_V6 = 128;

    /**
     * Number octets (group of 8 bits of the address)
     *
     * @const int   Number octets for IPv4
     */
    const NUMBER_OCTETS_V4 = 4;

    /**
     * Number octets (group of 8 bits of the address)
     *
     * @const int   Number octets for IPv6
     */
    const NUMBER_OCTETS_V6 = 16;

    /**
     * @var string  Human readable format of ip
     */
    protected $ip;

    /**
     * @var string  BIN format of ip
     */
    protected $bin;

    /**
     * @var string  HEX format of ip
     */
    protected $hex;

    /**
     * @var numeric Long numeric format of ip
     */
    protected $numeric;

    /**
     * @var string  Packed internet address format of ip
     */
    protected $inAddr;

    /**
     * @var string  IP version
     */
    protected $version;

    /**
     * @var int     Max network prefix length
     */
    protected $maxPrefixLength;

    /**
     * @var int     Number octects
     */
    protected $numberOctets;

    /**
     * Make IP object from a human readable IP address (Eg: 192.168.1.10)
     *
     * @param string $ip    A human readable IP address
     * @throws \InvalidArgumentException
     */
    public function __construct($ip)
    {
        if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            $this->version = self::IP_V4;
        } elseif (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
            $this->version = self::IP_V6;
        } else {
            throw new \InvalidArgumentException('Invalid IP address format');
        }

        $this->inAddr = inet_pton($ip);
    }

    /**
     * Converts ip to human readable format
     *
     * @return string
     */
    public function __toString()
    {
        return inet_ntop($this->inAddr);
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
            'bin' => $this->toBin(),
            'hex' => $this->toHex(),
            'numeric' => $this->toNumeric(),
            'version' => $this->getVersion(),
        ];
    }

    /**
     * Get ip verion
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * Get max network prefix
     *
     * @return int
     */
    public function getMaxPrefixLength()
    {
        if (null === $this->maxPrefixLength) {
            $this->maxPrefixLength = $this->version === self::IP_V4
                ? self::MAX_PREFIX_LENGTH_V4
                : self::MAX_PREFIX_LENGTH_V6;
        }

        return $this->maxPrefixLength;
    }

    /**
     * Get number octets
     *
     * @return int
     */
    public function getNumberOctets()
    {
        if (null === $this->numberOctets) {
            $this->numberOctets = $this->version === self::IP_V4
                ? self::NUMBER_OCTETS_V4
                : self::NUMBER_OCTETS_V6;
        }

        return $this->numberOctets;
    }

    /**
     * Get next IP
     *
     * @param int $step
     * @return IP|null
     */
    public function next($step = 1)
    {
        if ($step < 0) {
            throw new \InvalidArgumentException('Step must greater than zero');
        }

        $unpacked = unpack('C*', $this->inAddr);

        for ($i = 0; $i < $step; $i++) {
            for ($byte = count($unpacked); $byte >= 0; --$byte) {
                if ($unpacked[$byte] < 255) {
                    $unpacked[$byte]++;
                    break;
                } else {
                    $unpacked[$byte] = 0;
                }
            }
        }

        return new self(inet_ntop(call_user_func_array('pack', array_merge(['C*'], $unpacked))));
    }

    /**
     * Get previous IP
     *
     * @param int $step
     * @return IP|null
     */
    public function prev($step = 1)
    {
        if ($step < 0) {
            throw new \InvalidArgumentException('Step must greater than zero');
        }

        $unpacked = unpack('C*', $this->inAddr);

        for ($i = 0; $i < $step; $i++) {
            for ($byte = count($unpacked); $byte >= 0; --$byte) {
                if ($unpacked[$byte] == 0) {
                    $unpacked[$byte] = 255;
                } else {
                    $unpacked[$byte]--;
                    break;
                }
            }
        }

        return new self(inet_ntop(call_user_func_array('pack', array_merge(['C*'], $unpacked))));
    }

    /**
     * Converts ip to BIN format
     *
     * @return string
     */
    public function toBin()
    {
        if (null === $this->bin) {
            $binary = [];
            foreach (unpack('C*', $this->inAddr) as $char) {
                $binary[] = str_pad(decbin($char), 8, '0', STR_PAD_LEFT);
            }

            $this->bin = '0b' . implode($binary);
        }

        return $this->bin;
    }

    /**
     * Converts ip to HEX format
     *
     * @return string
     */
    public function toHex()
    {
        if (null === $this->hex) {
            $this->hex = '0x' . bin2hex($this->inAddr);
        }

        return $this->hex;
    }

    /**
     * Converts ip to a long numeric format
     *
     * @return numeric
     */
    public function toNumeric()
    {
        if (null === $this->numeric) {
            $numeric = 0;
            $octets = $this->getNumberOctets() - 1;
            foreach ($chars = unpack('C*', $this->inAddr) as $char) {
                $numeric = bcadd($numeric, bcmul($char, bcpow(256, $octets--)));
            }

            $this->numeric = $numeric;
        }

        return $this->numeric;
    }

    /**
     * Converts ip to a packed internet address
     *
     * @return string   A 32bit IPv4, or 128bit IPv6 address.
     */
    public function toInAddr()
    {
        return $this->inAddr;
    }

    /**
     * Validate a human readable IP address
     *
     * @param string $ip    A human readable IP address
     * @return boolean
     */
    public static function validate($ip)
    {
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4 | FILTER_FLAG_IPV6);
    }

    /**
     * Binary safe ip comparison
     *
     * @param IP|string $ip1
     * @param IP|string $ip2
     * @return int &lt; 0 if <i>ip1</i> is less than
     * <i>ip2</i>; &gt; 0 if <i>ip1</i>
     * is greater than <i>ip2</i>, and 0 if they are
     * equal.
     * @see strcmp
     */
    public static function cmp($ip1, $ip2)
    {
        foreach ($ips = [$ip1, $ip2] as $key => $ip) {
            if (!($ip instanceof self)) {
                $ips[$key] = self::parse($ip);
            }
        }

        return strcmp($ips[0]->toInAddr(), $ips[1]->toInAddr());
    }

    /**
     * Compress an IPv6 address
     * Eg:
     *     2001:0DB8:AC10:FE01:0000:0000:0000:0000 => 2001:0db8:ac10:fe01::
     *     ::127.0.0.1 => ::7f00:1
     *
     * @param string $ip    A IPv6 address
     * @return string       The compressed IPv6 address
     * @throws \InvalidArgumentException if parameter is not IPv6 address
     */
    public static function compressIPv6($ip)
    {
        if (!($ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))) {
            throw new \InvalidArgumentException();
        }

        $normal = inet_ntop(inet_pton($ip));

        // Check IPv6 has notation
        // https://tools.ietf.org/html/rfc2373#section-2.5.4
        if (strpos($normal, '.') !== false) {
            $octet = explode(':', $normal);
            $part = explode('.', array_pop($octet), 4);

            $octet[] = base_convert($part[0] * 256 + $part[1], 10, 16);
            $octet[] = base_convert($part[2] * 256 + $part[3], 10, 16);

            $normal = implode(':', $octet);
        }

        return $normal;
    }

    /**
     * Expand an IPv6 address
     *
     * This will take an IPv6 address written in short form and expand it to include all zeros.
     *
     * @param  string  $ip A valid IPv6 address
     * @return string  The expanded IPv6 address
     * @throws \InvalidArgumentException if parameter is not IPv6 address
     */
    public static function expandIPv6($ip)
    {
        if (!($ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))) {
            throw new \InvalidArgumentException();
        }

        $hex = bin2hex(inet_pton($ip));

        return implode(':', str_split($hex, 4));
    }

    /**
     * Parse an ip address
     *
     * Usage:
     * <pre><code>
     * echo (string)IP::parse(2130706433); // 127.0.0.1
     * echo (string)IP::parse('0b11000000101010000000000100000001'); // 192.168.1.1
     * echo (string)IP::parse('0x0a000001'); // 10.0.0.1
     * echo (string)IP::parse('192.168.1.1'); // 192.168.1.1
     * </code></pre>
     *
     * @param string|int $ip
     * @return \self
     * @throws \InvalidArgumentException
     */
    public static function parse($ip)
    {
        if (strpos($ip, '0x') === 0) {
            return self::fromHex($ip);
        } elseif (strpos($ip, '0b') === 0) {
            return self::fromBin($ip);
        } elseif (is_numeric($ip)) {
            // TODO: numeric IPv6
            return self::fromNumeric($ip, self::IP_V6);
        }

        return new static($ip);
    }

    /**
     * Parse an ip address from BIN format
     *
     * Usage:
     * <pre><code>
     * echo (string)IP::fromBin('0b11000000101010000000000100000001') // 192.168.1.1
     * </code></pre>
     *
     * @param string $bin
     * @return \self
     * @throws \InvalidArgumentException
     */
    public static function fromBin($bin)
    {
        if (!preg_match('/^0b([0-1]{32}|[0-1]{128})$/', $bin)) {
            throw new \InvalidArgumentException('Invalid binary IP address format');
        }
        $inAddr = '';
        foreach (array_map('bindec', str_split(substr($bin, 2), 8)) as $char) {
            $inAddr .= pack('C*', $char);
        }

        return new static(inet_ntop($inAddr));
    }

    /**
     * Parse an ip address from HEX format
     *
     * Usage:
     * <pre><code>
     * echo (string)IP::fromHex('0x0a000001') // 10.0.0.1
     * </code></pre>
     *
     * @param string $hex
     * @return \self
     * @throws \InvalidArgumentException
     */
    public static function fromHex($hex)
    {
        if (!preg_match('/^0x([0-9a-fA-F]{8}|[0-9a-fA-F]{32})$/', $hex)) {
            throw new \InvalidArgumentException('Invalid hexadecimal IP address format');
        }

        return new static(inet_ntop(hex2bin(substr($hex, 2)))); // return new self(inet_ntop(pack('H*', substr($hex, 2))));
    }

    /**
     * Parse an ip address from long numberic format
     *
     * Usage:
     * <pre><code>
     * echo (string)IP::fromNumeric('2130706433') // 127.0.0.1
     * echo (string)IP::fromNumeric('2130706433', IP::IP_V6) // ::127.0.0.1
     * </code></pre>
     *
     * @param string|int $num
     * @param string     $version
     * @return \self
     * @throws \InvalidArgumentException
     */
    public static function fromNumeric($num, $version = self::IP_V4)
    {
        $binary = [];
        $octets = (self::IP_V4 === $version) ? self::NUMBER_OCTETS_V4 : self::NUMBER_OCTETS_V6;
        for ($i = 0; $i < $octets; $i++) {
            $binary[] = bcmod($num, 256);
            $num = bcdiv($num, 256, 0);
        }

        $inAddr = call_user_func_array('pack', array_merge(['C*'], array_reverse($binary)));
        return new static(inet_ntop($inAddr));
    }

    /**
     * Parse an ip address from a packed internet address
     *
     * Usage:
     * <pre><code>
     * echo (string)IP::fromInAddr(inet_ptop('127.0.0.1')) // 127.0.0.1
     * </code></pre>
     *
     * @param string $inAddr    A 32bit IPv4, or 128bit IPv6 address.
     * @return \self
     * @throws \InvalidArgumentException
     */
    public static function fromInAddr($inAddr)
    {
        return new static(inet_ntop($inAddr));
    }
}
