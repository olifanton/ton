<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

class ManualCancellation implements Cancellation
{
    private bool $isCanceled = false;

    public function cancel(): void
    {
        $this->isCanceled = true;
    }

    public function isCanceled(): bool
    {
        return $this->isCanceled;
    }
}
