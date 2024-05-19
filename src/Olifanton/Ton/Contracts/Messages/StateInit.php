<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Interop\Boc\Exceptions\CellException;
use Olifanton\Interop\Bytes;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;

class StateInit
{
    public function __construct(
        public readonly ?Cell $code = null,
        public readonly ?Cell $data = null,
    ) {}

    /**
     * @throws MessageException
     */
    public static function fromBase64(string $b64String): self
    {
        try {
            $cells = Cell::fromBoc(Bytes::base64ToBytes($b64String));
        } catch (CellException $e) {
            throw new MessageException(
                "StateInit string deserialization error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

        $cellCount = count($cells);

        if ($cellCount !== 1) {
            throw new MessageException(
                sprintf("Bad StateInit data, expected 1 cell, %d given", $cellCount),
            );
        }

        try {
            $slice = $cells[0]->beginParse();
            $b0 = $slice->loadBit();
            $b1 = $slice->loadBit();

            if ($b0 || $b1) {
                throw new \InvalidArgumentException(
                    "Not a StateInit cell, detected by first bits",
                );
            }

            $codeExists = $slice->loadBit();
            $dataExists = $slice->loadBit();
            $code = $data = null;

            if ($codeExists) {
                $code = $slice->loadRef();
            }

            if ($dataExists) {
                $data = $slice->loadRef();
            }

            $b2 = $slice->loadBit();

            if ($b2) {
                throw new \InvalidArgumentException(
                    "Not a StateInit cell, detected by last bit",
                );
            }

            return new self($code, $data);
        } catch (\Throwable $e) {
            throw new MessageException(
                "Cell parsing error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
    }

    /**
     * @throws MessageException
     */
    public function writeTo(Cell $cell): void
    {
        $bs = $cell->bits;

        try {
            $bs
                ->writeBit(0)
                ->writeBit(0)
                ->writeBit(!!$this->code)
                ->writeBit(!!$this->data)
                ->writeBit(0);
        // @codeCoverageIgnoreStart
        } catch (BitStringException $e) {
            throw new MessageException(
                "StateInit serialization error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }
        // @codeCoverageIgnoreEnd

        if ($this->code) {
            $cell->refs[] = $this->code;
        }

        if ($this->data) {
            $cell->refs[] = $this->data;
        }
    }

    /**
     * @throws MessageException
     */
    public function cell(): Cell
    {
        $cell = new Cell();
        $this->writeTo($cell);

        return $cell;
    }
}
