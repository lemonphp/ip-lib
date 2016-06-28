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
 * Trait IPRangeIterable
 * That help implement IPRangeIterator interface
 */
trait IPRangeIterable
{
    /**
     * Get span network
     * Span network is smallest network, that wraps this range.
     *
     * @return Network
     */
    public function getSpanNetwork()
    {
        $xorIP = IP::fromInAddr($this->getFirstIP()->toInAddr() ^ $this->getLastIP()->toInAddr());
        preg_match('/^0b(0*)/', $xorIP->toBin(), $matches);

        $networkPrefix = strlen($matches[1]);
        $networkIP = IP::fromBin('0b' . str_pad(
            substr($this->getFirstIP()->toBin(), 2, $networkPrefix),
            $xorIP->getMaxPrefixLength(),
            '0'
        ));

        return new Network($networkIP, Netmask::fromPrefixLength($networkPrefix, $networkIP->getVersion()));
    }

    /**
     * Check this range contains an IP or IPRangeIterator instance
     *
     * @param IP|IPRangeIterator $needle
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function contains($needle)
    {
        if ($needle instanceof IP) {
            $contained = IP::cmp($needle, $this->getFirstIP()) >= 0 && IP::cmp($needle, $this->getLastIP()) <= 0;
        } elseif ($needle instanceof IPRange) {
            $contained = IP::cmp($needle->getFirstIP(), $this->getFirstIP()) >= 0 &&
                IP::cmp($needle->getLastIP(), $this->getLastIP()) <= 0;
        } else {
            throw new \InvalidArgumentException("\$needle must be instance of IP or IPRangeIterator");
        }

        return $contained;
    }

    /**
     * Return the current element
     *
     * @return IP
     */
    public function current()
    {
        return $this->getFirstIP()->next($this->currentKey);
    }

    /**
     * Return the key of the current element
     *
     * @return int
     */
    public function key()
    {
        return $this->currentKey;
    }

    /**
     * Move forward to next element
     */
    public function next()
    {
        ++$this->currentKey;
    }

    /**
     * Rewind the Iterator to the first element
     */
    public function rewind()
    {
        $this->currentKey = 0;
    }

    /**
     * Checks if current element is valid
     *
     * @return bool
     */
    public function valid()
    {
        return IP::cmp($this->current(), $this->getLastIP() <= 0);
    }

    /**
     * Count number elements
     *
     * @return int
     */
    public function count()
    {
        if ($this->getFirstIP()->getVersion() === IP::IP_V4) {
            $count = $this->getLastIP()->toNumeric() - $this->getFirstIP()->toNumeric();
        } else {
            $count = bcsub($this->getLastIP()->toNumeric(), $this->getFirstIP()->toNumeric());
        }

        return $count;
    }
}
