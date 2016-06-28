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
 * Network class
 */
class Network implements IPRangeIterator
{
    use IPRangeIterable;

    /**
     * Network IP
     *
     * @var IP
     */
    protected $networkIP;

    /**
     * Network broadcast IP
     *
     * @var IP
     */
    protected $broadcastIP;

    /**
     * Network netmask
     *
     * @var Netmask
     */
    protected $netmask;

    /**
     * Constructor
     *
     * @param IP $ip
     * @param Netmask $netmask
     */
    public function __construct(IP $ip, Netmask $netmask)
    {
        $this->setNetworkIP($ip);
        $this->setNetmask($netmask);
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toCIDR();
    }

    /**
     * Metadata for debugging
     *
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'ip' => (string) $this->getNetworkIP(),
            'netmask' => (string) $this->getNetmask(),
            'cidr' => $this->toCIDR(),
            'broadcast_ip' => (string) $this->getBroadcastIP(),
        ];
    }

    /**
     * Set network IP
     *
     * @param IP $ip
     * @throws InvalidArgumentException
     */
    public function setNetworkIP(IP $ip)
    {
        if (isset($this->netmask) && $this->netmask->getVersion() !== $ip->getVersion()) {
            throw new \InvalidArgumentException('IP version is not same as Netmask version');
        }

        $this->networkIP = new IP(inet_ntop($ip->toInAddr() & $this->netmask->toInAddr()));
        $this->broadcastIP = null;
    }

    /**
     * Get network IP
     *
     * @return IP
     */
    public function getNetworkIP()
    {
        return $this->networkIP;
    }

    /**
     * @param Netmask $netmask
     * @throws \InvalidArgumentException
     */
    public function setNetmask(Netmask $netmask)
    {
        if (isset($this->ip) && $netmask->getVersion() !== $this->ip->getVersion()) {
            throw new \InvalidArgumentException('Netmask version is not same as IP version');
        }

        $this->netmask = $netmask;
        $this->broadcastIP = null;
    }

    /**
     * @return Netmask
     */
    public function getNetmask()
    {
        return $this->netmask;
    }

    /**
     * Set network prefix length
     * <b>Note :</b> this method will override network netmask
     *
     * @param int $length
     */
    public function setPrefixLength($length)
    {
        $this->setNetmask(Netmask::fromPrefixLength($length));
    }

    /**
     * Get network prefix length
     *
     * @return int
     */
    public function getPrefixLength()
    {
        return $this->getNetmask()->toPrefixLength();
    }

    /**
     * Get broadcast IP of network
     *
     * @see https://en.wikipedia.org/wiki/Broadcast_address
     * @return IP
     */
    public function getBroadcastIP()
    {
        if (null === $this->broadcastIP) {
            $this->broadcastIP = new IP(
                inet_ntop($this->getNetworkIP()->toInAddr() | ~$this->getNetmask()->toInAddr())
            );
        }

        return $this->broadcastIP;
    }

    /**
     * Get first IP of network
     *
     * @return IP
     */
    public function getFirstIP()
    {
        return $this->getNetworkIP();
    }

    /**
     * Get last IP of network
     *
     * @return IP
     */
    public function getLastIP()
    {
        return $this->getBroadcastIP();
    }

    /**
     * Convert to CIDR format
     *
     * @return string
     */
    public function toCIDR()
    {
        return sprintf('%s/%d', $this->getNetworkIP(), $this->getPrefixLength());
    }

    /**
     * Parse data to a network
     *
     * Usage:
     * <pre><code>
     * $network = Network::parse('192.168.1.0/24');
     * $network = Network::parse('192.168.1.0 255.255.255.0');
     * $network = Network::parse('127.0.0.1');
     * </code></pre>
     *
     * @param string $data
     * @return \self
     */
    public static function parse($data)
    {
        if (strpos($data, '/')) {
            // Network is defined by CIDR format (192.168.100.1/24)
            list($ipPart, $prefixLength) = explode('/', $data, 2);
            $ip = IP::parse($ipPart);
            $netmask = Netmask::fromPrefixLength((int) $prefixLength, $ip->getVersion());
        } elseif (strpos($data, ' ')) {
            // Network is defined by Ip and netmask (192.168.100.1 255.255.255.0)
            list($ipPart, $netmaskPart) = explode(' ', $data, 2);
            $ip = IP::parse($ipPart);
            $netmask = Netmask::parse($netmaskPart);
        } else {
            // Network is defined by only an IP
            $ip = IP::parse($data);
            $netmask = Netmask::fromPrefixLength($ip->getMaxPrefixLength(), $ip->getVersion());
        }

        return new self($ip, $netmask);
    }
}
