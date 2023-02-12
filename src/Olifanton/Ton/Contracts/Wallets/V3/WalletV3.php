<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V3;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Ton\Contracts\Wallet;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;

abstract class WalletV3 extends AbstractWallet implements Wallet
{
    protected function createData(): Cell
    {
        try {
            $cell = new Cell();
            $cell->bits->writeUint(0, 32);
            $cell->bits->writeUint($this->getWalletId(), 32);
            $cell->bits->writeBytes($this->getPublicKey());

            return $cell;
        } catch (BitStringException $e) {
            throw new WalletException("Wallet data creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }

    protected function getWalletId(): int
    {
        // @TODO: Create option `walletId`

        return 698983191 + $this->getWc();
    }
}
