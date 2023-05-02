<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts;

use Olifanton\Interop\Address;

class ContractOptions
{
    public function __construct(
        public readonly int $workchain = 0,
        public readonly ?Address $address = null,
    ) {}
}
