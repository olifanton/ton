<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Ton\Contracts\AbstractContract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;

abstract class AbstractWallet extends AbstractContract implements Wallet
{
    protected function createData(): Cell
    {
        try {
            $cell = new Cell();
            $cell->bits->writeUint(0, 32); // seqno
            $cell->bits->writeBytes($this->getPublicKey());

            return $cell;
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new WalletException("Wallet data creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws ContractException
     */
    protected function deserializeCode(string $serializedBoc): Cell
    {
        try {
            return Cell::oneFromBoc($serializedBoc);
        // @codeCoverageIgnoreStart
        } catch (CellException $e) {
            throw new WalletException("Wallet code creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
}
