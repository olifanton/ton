<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts;

use Olifanton\Interop\Address;
use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contract;
use Olifanton\Ton\Contracts\Exceptions\ContractException;
use Olifanton\Ton\Contracts\Interfaces\Deployable;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;
use Olifanton\Ton\Contracts\Messages\StateInit;
use Olifanton\Ton\Contracts\Wallets\Exceptions\WalletException;

abstract class AbstractContract implements Contract, Deployable
{
    protected ?Cell $code = null;

    protected ?Cell $data = null;

    private ?Address $address;

    private int $wc;

    public function __construct(ContractOptions $contractOptions)
    {
        $this->address = $contractOptions->address;
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

    public function getWc(): int
    {
        return $this->wc;
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
    protected static function deserializeCode(string $serializedBoc, bool $isBase64 = false): Cell
    {
        try {
            return Cell::oneFromBoc($serializedBoc, $isBase64);
        // @codeCoverageIgnoreStart
        } catch (CellException $e) {
            throw new WalletException("Wallet code creation error: " . $e->getMessage(), $e->getCode(), $e);
        }
        // @codeCoverageIgnoreEnd
    }
}
