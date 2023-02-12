<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use Olifanton\TypedArrays\Uint8Array;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Ton\Contracts\AbstractContract;
use Olifanton\Ton\Contracts\Wallet;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Messages\StateInit;
use Olifanton\Interop\Address;
use Olifanton\Interop\Bytes;

abstract class AbstractWallet extends AbstractContract implements Wallet
{
    protected Uint8Array $publicKey;

    protected int $wc;

    private ?Address $address = null;

    public function __construct(Uint8Array $publicKey, int $wc)
    {
        $this->publicKey = $publicKey;
        $this->wc = $wc;
    }

    protected function getPublicKey(): Uint8Array
    {
        return $this->publicKey;
    }

    protected function getWc(): int
    {
        return $this->wc;
    }

    public function getAddress(): Address
    {
        if (!$this->address) {
            try {
                $cell = new Cell();
                $state = new StateInit($this->getCode(), $this->getData());
                $state->writeTo($cell);

                return new Address($this->getWc() . ":" . Bytes::bytesToHexString($cell->hash()));
            } catch (BitStringException | CellException $e) {
                throw new WalletException("Address calculation error: " . $e->getMessage(), $e->getCode(), $e);
            }
        }

        return $this->address;
    }

    protected function createData(): Cell
    {
        try {
            $cell = new Cell();
            $cell->bits->writeUint(0, 32); // seqno
            $cell->bits->writeBytes($this->getPublicKey());

            return $cell;
        } catch (BitStringException $e) {
            throw new WalletException("Wallet data creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
    }
}
