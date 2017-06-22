<?php
require 'vendor/autoload.php';

use Lemon\Lib\IP;

//$ip = new IP('::127.0.0.1');
$ip = new IP('::7f00:1');

echo $ip . PHP_EOL;
echo $ip->toBin() . PHP_EOL;
echo $ip->toHex() . PHP_EOL;
echo $ip->toNumeric() . PHP_EOL;
echo $ip->getVersion() . PHP_EOL;

echo IP::parse('2130706433');
