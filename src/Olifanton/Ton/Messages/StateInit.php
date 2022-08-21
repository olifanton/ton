<?php declare(strict_types=1);

namespace Olifanton\Ton\Messages;

use Olifanton\Boc\Cell;
use Olifanton\Ton\Message;

class StateInit implements Message
{
    public function __construct(
        private readonly ?Cell $code = null,
        private readonly ?Cell $data = null,
    )
    {
    }

    public function writeTo(Cell $cell): void
    {
        $cell->bits->writeBit(0); // SplitDepth
        $cell->bits->writeBit(0); // TickTock
        $cell->bits->writeBit(!!$this->code); // Code presence
        $cell->bits->writeBit(!!$this->data); // Data presence
        $cell->bits->writeBit(0); // Library

        if ($this->code) {
            $cell->refs[] = $this->code;
        }

        if ($this->data) {
            $cell->refs[] = $this->data;
        }
    }
}
