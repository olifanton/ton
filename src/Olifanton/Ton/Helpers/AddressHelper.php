<?php declare(strict_types=1);

namespace Olifanton\Ton\Helpers;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Exceptions\SliceException;
use Olifanton\Interop\Boc\Slice;
use Olifanton\Interop\Bytes;

final class AddressHelper
{
    /**
     * @throws SliceException
     */
    public static function parseAddressSlice(Slice $slice): Address
    {
        $slice->skipBits(3);
        $n = $slice->loadInt(8)->toInt();

        if ($n > 127) {
            $n = $n - 256;
        }

        $hashPart = $slice->loadBits(256);

        return new Address(sprintf(
            "%s:%s",
            $n,
            Bytes::bytesToHexString($hashPart),
        ));
    }
}
