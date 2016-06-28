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
 * IP range class
 */
class IPRange implements IPRangeIterator
{

    use IPRangeIterable;

    /**
     * First IP of range
     *
     * @var IP
     */
    protected $firstIP;

    /**
     * Last IP of range
     *
     * @var IP
     */
    protected $lastIP;

    /**
     * Key of current element
     *
     * @var int
     */
    protected $currentKey = 0;

    /**
     * Make an IP range by first and last IP
     *
     * @param IP $firstIP
     * @param IP $lastIP
     * @throws \InvalidArgumentException
     */
    public function __construct(IP $firstIP, IP $lastIP)
    {
        if ($firstIP->getVersion() === $lastIP->getVersion()) {
            throw new \InvalidArgumentException('Two IP must be the same version');
        }

        $this->setFirstIP($firstIP);
        $this->setLastIP($lastIP);
    }

    /**
     * Converts IP range to string
     *
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s-%s', $this->getFirstIP(), $this->getLastIP());
    }

    /**
     * Metadata for debugging
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'first_ip' => (string) $this->getFirstIP(),
            'last_ip' => (string) $this->getLastIP(),
        ];
    }

    /**
     * Set first IP of range
     *
     * @param IP $ip
     * @throws \InvalidArgumentException
     */
    public function setFirstIP(IP $ip)
    {
        if ($this->lastIP && IP::cmp($ip, $this->lastIP) > 0) {
            throw new \InvalidArgumentException();
        }

        $this->firstIP = $ip;
    }

    /**
     * Get first IP of range
     *
     * @return IP
     */
    public function getFirstIP()
    {
        return $this->firstIP;
    }

    /**
     * Set last IP of range
     *
     * @param IP $ip
     * @throws \InvalidArgumentException
     */
    public function setLastIP(IP $ip)
    {
        if ($this->firstIP && IP::cmp($ip, $this->firstIP) < 0) {
            throw new \InvalidArgumentException();
        }

        $this->lastIP = $ip;
    }

    /**
     * Get last IP of range
     *
     * @return IP
     */
    public function getLastIP()
    {
        return $this->lastIP;
    }

    /**
     * Parse data to an ip range
     *
     * Usage:
     * <pre><code>
     * $range = IPRange::parse('192.168.1.0-192.168.1.255');
     * $range = IPRange::parse('192.168.1.*');
     * $range = IPRange::parse('192.168.1.0/24');
     * $range = IPRange::parse('192.168.1.0 255.255.255.0');
     * $range = IPRange::parse('127.0.0.1');
     * </code></pre>
     *
     * @param string $data
     * @return \self
     */
    public static function parse($data)
    {
        if (strpos($data, '/') || strpos($data, ' ')) {
            // Range is defined by a network
            $network = Network::parse($data);
            $firstIP = $network->getFirstIP();
            $lastIP = $network->getLastIP();
        } elseif (strpos($data, '*')) {
            // Range is defined by wildcard
            $firstIP = IP::parse(str_replace('*', '0', $data));
            $lastIP = IP::parse(str_replace('*', '255', $data));
        } elseif (strpos($data, '-')) {
            // Range is defined by two IP
            list($first, $last) = explode('-', $data, 2);
            $firstIP = IP::parse($first);
            $lastIP = IP::parse($last);
        } else {
            // Range is defined by an IP
            $firstIP = IP::parse($data);
            $lastIP = clone $firstIP;
        }

        return new self($firstIP, $lastIP);
    }
}
