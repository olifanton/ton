<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts;

use Olifanton\TypedArrays\Uint8Array;

class ContractOptions
{
    public function __construct(
        public readonly Uint8Array $publicKey,
        public readonly int $workchain = 0,
    ) {}
}
