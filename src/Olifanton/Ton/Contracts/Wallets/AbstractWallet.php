<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Wallets;

use ajf\TypedArrays\Uint8Array;
use Olifanton\Boc\Cell;
use Olifanton\Ton\Contracts\AbstractContract;
use Olifanton\Ton\Contracts\Wallet;
use Olifanton\Ton\Messages\StateInit;
use Olifanton\Utils\Address;
use Olifanton\Utils\Bytes;

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

    /**
     * @throws \Olifanton\Boc\Exceptions\BitStringException
     * @throws \Olifanton\Boc\Exceptions\CellException
     */
    public function getAddress(): Address
    {
        if (!$this->address) {
            $cell = new Cell();
            $state = new StateInit($this->getCode(), $this->getData());
            $state->writeTo($cell);

            return new Address($this->getWc() . ":" . Bytes::bytesToHexString($cell->hash()));
        }

        return $this->address;
    }
}
