<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Events;

abstract class SessionEvent
{
    public abstract function getName(): string;
}
