<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect\Sse;

use Olifanton\Ton\Connect\Cancellation;

class TimeoutCancellation implements Cancellation
{
    private float $endTime;

    public function __construct(float $seconds)
    {
        $this->endTime = microtime(true) + $seconds;
    }

    public function isCanceled(): bool
    {
        return microtime(true) > $this->endTime;
    }
}
