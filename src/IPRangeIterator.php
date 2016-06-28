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
 * IPRangeIterator interface
 */
interface IPRangeIterator extends \Iterator, \Countable
{
    /**
     * Get first IP of range
     *
     * @return IP
     */
    public function getFirstIP();

    /**
     * Get last IP of range
     *
     * @return IP
     */
    public function getLastIP();

    /**
     * Get span network
     * Span network is smallest network, that wraps this range.
     *
     * @return Network
     */
    public function getSpanNetwork();

    /**
     * @return Network[]
     */
    public function getNetworks();

    /**
     * Check this range contains an IP or IPRangeIterator instance
     *
     * @param IP|IPRangeIterator $needle
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function contains($needle);
}
