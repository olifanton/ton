<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts;

use Olifanton\Ton\Transport;

interface TransportAwareInterface
{
    public function setTransport(Transport $transport): void;
}
