<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;

class ExternalMessageOptions
{
    public function __construct(
        public ?Address $src = null,
        public ?Address $dest = null,
        public ?BigInteger $importFee = null,
    ) {}
}
