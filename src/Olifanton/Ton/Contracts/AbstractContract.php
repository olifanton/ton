<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Traits\TransportAwareTrait;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\Ton\Messages\StateInit;
use Olifanton\TypedArrays\Uint8Array;

/**
 * @phpstan-consistent-constructor
 */
abstract class AbstractContract implements Contract, TransportAwareInterface
{
    use TransportAwareTrait;

    protected ?Cell $code = null;

    protected ?Cell $data = null;

    protected Uint8Array $publicKey;

    protected int $wc;

    private ?Address $address = null;

    public function __construct(Uint8Array $publicKey, int $wc)
    {
        $this->publicKey = $publicKey;
        $this->wc = $wc;
    }

    public function getCode(): Cell
    {
        if (!$this->code) {
            $this->code = $this->createCode();
        }

        return $this->code;
    }

    public function getData(): Cell
    {
        if (!$this->data) {
            $this->data = $this->createData();
        }

        return $this->data;
    }

    public function getAddress(): Address
    {
        if (!$this->address) {
            try {
                $cell = new Cell();
                $state = new StateInit($this->getCode(), $this->getData());
                $state->writeTo($cell);

                $this->address = new Address($this->getWc() . ":" . Bytes::bytesToHexString($cell->hash()));
            // @codeCoverageIgnoreStart
            } catch (BitStringException | CellException $e) {
                throw new WalletException("Address calculation error: " . $e->getMessage(), $e->getCode(), $e);
            }
            // @codeCoverageIgnoreEnd
        }

        return $this->address;
    }

    public static function create(Uint8Array $publicKey, int $wc): Contract
    {
        return new static(
            $publicKey,
            $wc,
        );
    }

    /**
     * @throws ContractException
     */
    protected abstract function createCode(): Cell;

    /**
     * @throws ContractException
     */
    protected abstract function createData(): Cell;

    protected function getPublicKey(): Uint8Array
    {
        return $this->publicKey;
    }

    protected function getWc(): int
    {
        return $this->wc;
    }
}
