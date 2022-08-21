<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts;

use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Utils\Address;

interface Wallet extends Contract
{
    /**
     * @throws WalletException
     */
    public function getAddress(): Address;
}
