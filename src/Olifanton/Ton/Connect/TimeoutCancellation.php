<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

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

    public function forceCancel(): void
    {
        $this->endTime = 0;
    }
}
