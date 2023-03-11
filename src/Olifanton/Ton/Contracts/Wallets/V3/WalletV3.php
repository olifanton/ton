<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets\V3;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Ton\Contracts\Wallets\AbstractWallet;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Contracts\Wallets\Wallet;

abstract class WalletV3 extends AbstractWallet implements Wallet
{
    public function __construct(protected readonly WalletV3Options $options)
    {
        parent::__construct($this->options);
    }

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
        return $this->options->walletId + $this->getWc();
    }

    public function createSigningMessage(int $seqno, ?int $expireAt = null): Cell
    {
        $expireAt = $expireAt ?? time() + 60;

        $message = new Cell();
        $bs = $message->bits;
        $bs->writeUint($this->getWalletId(), 32);

        if ($seqno === 0) {
            for ($i = 0; $i < 32; $i++) {
                $bs->writeBit(1);
            }
        } else {
            $bs->writeUint($expireAt, 32);
        }

        $bs->writeUint($seqno, 32);

        return $message;
    }
}
