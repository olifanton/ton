<?php declare(strict_types=1);

namespace Olifanton\Ton\Dns\Helpers;

use Olifanton\Interop\Bytes;
use Olifanton\Ton\Dns\Exceptions\DnsException;
use Olifanton\TypedArrays\Uint8Array;

final class DomainHelper
{
    /**
     * @throws DnsException
     */
    public static function domainToBytes(string $domain): Uint8Array
    {
        if ($domain === '.') {
            return new Uint8Array([0]);
        }

        $domain = mb_strtolower($domain);

        if (!preg_match("#^[a-z0-9-.]+$#", $domain)) {
            throw new DnsException("Domain contains illegal characters");
        }

        $components = explode('.', $domain);

        foreach ($components as $component) {
            if (empty($component)) {
                throw new DnsException("Domain name contains empty component");
            }
        }

        $rawDomain = implode("\0", array_reverse($components)) . "\0";

        return Bytes::stringToBytes($rawDomain);
    }
}
