<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Olifanton\Interop\Address;

class ExternalOutMessageOptions
{
    public function __construct(
        public Address $dest,
        public ?Address $src = null,
        public ?string $createdLt = null,
        public ?string $createdAt = null,
    ) {}
}
