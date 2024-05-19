<?php declare(strict_types=1);

namespace Olifanton\Ton\Reflection\Models;

use Olifanton\Interop\Address;
use Olifanton\TypedArrays\Uint8Array;

class AddressPubkeyPair
{
    public function __construct(
        public readonly Address $address,
        public readonly Uint8Array $publicKey,
    ) {}
}
