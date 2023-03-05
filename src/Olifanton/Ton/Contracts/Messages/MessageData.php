<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Olifanton\Interop\Boc\Cell;

class MessageData
{
    public function __construct(
        public readonly ?Cell $body = null,
        public readonly ?Cell $state = null,
    )
    {
    }
}
