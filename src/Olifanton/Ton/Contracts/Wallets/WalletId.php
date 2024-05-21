<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Olifanton\Ton\Network;

class WalletId
{
    public function __construct(
        public readonly Network $networkId = Network::MAIN,
        public readonly int $subwalletId = 0,
        public readonly string $walletVersion = "v5",
        public readonly ?int $workchain = null,
    ) {}
}
