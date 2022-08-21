<?php

namespace Olifanton\Ton\Contracts\Wallets\Simple;

use Olifanton\Boc\Cell;
use Olifanton\Ton\Contracts\Wallet;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;

abstract class SimpleWallet extends AbstractWallet implements Wallet
{
    /**
     * @throws \Olifanton\Boc\Exceptions\BitStringException
     */
    protected function createData(): Cell
    {
        $cell = new Cell();
        $cell->bits->writeUint(0, 32); // seqno
        $cell->bits->writeBytes($this->getPublicKey());

        return $cell;
    }
}
