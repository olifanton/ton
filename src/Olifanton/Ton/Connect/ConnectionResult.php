<?php declare(strict_types=1);

namespace Olifanton\Ton\Connect;

use Olifanton\Ton\Connect\Events\Connect;
use Olifanton\Ton\Connect\Models\WalletApplication;
use Olifanton\Ton\Connect\Replies\TonAddr;
use Olifanton\Ton\Connect\Replies\TonProof;

class ConnectionResult
{
    public function __construct(
        public readonly string $preconnectedId,
        public readonly TonAddr $tonAddr,
        public readonly TonProof $proof,
        public readonly Session $session,
        public readonly WalletApplication $walletApplication,
        public readonly Connect $connectEvent,
    ) {}
}
