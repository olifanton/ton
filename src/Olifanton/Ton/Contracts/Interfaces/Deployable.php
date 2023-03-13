<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Interfaces;

use Olifanton\Interop\Address;
use Olifanton\Ton\Contracts\Messages\StateInit;

interface Deployable
{
    public function getStateInit(): StateInit;

    public function getAddress(): Address;
}
