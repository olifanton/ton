<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Events;

class NullEvent extends SessionEvent
{
    public function getName(): string
    {
        return "null";
    }
}
