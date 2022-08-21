<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts;

use Olifanton\Ton\Contract;
use Olifanton\Utils\Address;

interface Wallet extends Contract
{
    public function getAddress(): Address;
}
