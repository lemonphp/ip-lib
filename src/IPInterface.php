<?php

namespace Lemon\Lib;

abstract class IP
{
    const IP_V4 = 'IPv4';
    const IP_V6 = 'IPv6';

    public function getVersion();

    public function getMaxPrefixLength();

    public function getNumberOctets();

    public function toBin();

    public function toHex();

    public function toNumeric();

    public function toInAddr();

    public static function parse();
}