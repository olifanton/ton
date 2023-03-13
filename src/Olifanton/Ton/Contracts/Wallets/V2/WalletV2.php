<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V2;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;

abstract class WalletV2 extends AbstractWallet
{
    public function createSigningMessage(int $seqno, ?int $expireAt = null): Cell
    {
        try {
            $expireAt = $expireAt ?? time() + 60;

            $cell = new Cell();
            $bs = $cell->bits;
            $bs->writeUint($seqno, 32);

            if ($seqno === 0) {
                for ($i = 0; $i < 32; $i++) {
                    $bs->writeBit(1);
                }
            } else {
                $bs->writeUint($expireAt, 32);
            }

            return $cell;
        } catch (BitStringException $e) {
            throw new WalletException($e->getMessage(), $e->getCode(), $e);
        }
    }
}
