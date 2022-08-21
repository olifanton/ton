<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V4;

use Olifanton\Boc\Cell;
use Olifanton\Ton\Contracts\Wallet;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;

abstract class WalletV4 extends AbstractWallet implements Wallet
{
    /**
     * @throws \Olifanton\Boc\Exceptions\BitStringException
     */
    protected function createData(): Cell
    {
        $cell = new Cell();
        $cell->bits->writeUint(0, 32);
        $cell->bits->writeUint($this->getWalletId(), 32);
        $cell->bits->writeBytes($this->getPublicKey());
        $cell->bits->writeUint(0, 1);

        return $cell;
    }

    protected function getWalletId(): int
    {
        // @TODO: Create option `walletId`

        return 698983191 + $this->getWc();
    }
}
