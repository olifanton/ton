<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Messages\ExternalMessage;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Transport;

interface Wallet extends Contract
{
    /**
     * @throws WalletException
     */
    public function createTransferMessage(TransferMessageOptions $options): ExternalMessage;

    /**
     * @throws WalletException
     */
    public function seqno(Transport $transport): ?int;

    /**
     * @throws WalletException
     */
    public function createSigningMessage(int $seqno): Cell;
}
