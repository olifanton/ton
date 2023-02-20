<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Traits;

use Olifanton\Ton\Transport;

trait TransportAwareTrait
{
    protected ?Transport $transport = null;

    public function setTransport(Transport $transport): void
    {
        $this->transport = $transport;
    }
}
