<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

interface Cancellation
{
    public function isCanceled(): bool;
}
