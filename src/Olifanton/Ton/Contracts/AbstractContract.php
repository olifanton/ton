<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;
use Olifanton\Ton\Contracts\Messages\StateInit;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;
use Olifanton\TypedArrays\Uint8Array;

abstract class AbstractContract implements Contract
{
    protected Uint8Array $publicKey;

    protected int $wc;

    protected ?Cell $code = null;

    protected ?Cell $data = null;

    private ?Address $address = null;

    public function __construct(ContractOptions $contractOptions)
    {
        $this->publicKey = $contractOptions->publicKey;
        $this->wc = $contractOptions->workchain;
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
                $stateCell = $this->getStateInit()->cell();
                $this->address = new Address($this->getWc() . ":" . Bytes::bytesToHexString($stateCell->hash()));
            // @codeCoverageIgnoreStart
            } catch (MessageException | CellException $e) {
                throw new WalletException("Address calculation error: " . $e->getMessage(), $e->getCode(), $e);
            }
            // @codeCoverageIgnoreEnd
        }

        return $this->address;
    }

    public function getPublicKey(): Uint8Array
    {
        return $this->publicKey;
    }

    public function getWc(): int
    {
        return $this->wc;
    }

    /**
     * @throws ContractException
     */
    public function getStateInit(): StateInit
    {
        return new StateInit(
            $this->getCode(),
            $this->getData(),
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
