<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Brick\Math\BigInteger;
use Olifanton\Interop\Address;

class InternalMessageOptions
{
    public function __construct(
        public bool $bounce,
        public Address $dest,
        public BigInteger $value,
        public ?Address $src = null,
        public ?BigInteger $ihrFee = null,
        public ?BigInteger $fwdFee = null,
        public ?bool $ihrDisabled = null,
        public ?bool $bounced = null,
        public ?string $createdLt = null,
        public ?string $createdAt = null,
    )
    {
    }
}
