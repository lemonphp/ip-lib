<?php

namespace Lemon\Lib\IP;

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
function compress_ip6($ip)
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
function expand_ip6($ip)
{
    if (!($ip = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_IPV6))) {
        throw new \InvalidArgumentException();
    }

    $hex = bin2hex(inet_pton($ip));

    return implode(':', str_split($hex, 4));
}
