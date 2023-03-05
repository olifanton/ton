<?php declare(strict_types=1);

namespace Olifanton\Ton\Contracts\Messages;

use Olifanton\Interop\Boc\Cell;
use Olifanton\Interop\Boc\Exceptions\BitStringException;
use Olifanton\Ton\Contracts\Messages\Exceptions\MessageException;

class StateInit
{
    public function __construct(
        private readonly ?Cell $code = null,
        private readonly ?Cell $data = null,
    )
    {
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
        } catch (BitStringException $e) {
            throw new MessageException(
                "StateInit serialization error: " . $e->getMessage(),
                $e->getCode(),
                $e,
            );
        }

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
