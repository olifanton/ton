<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Olifanton\Boc\Cell;

interface Message
{
    /**
     * @throws \Olifanton\Boc\Exceptions\BitStringException
     */
    public function writeTo(Cell $cell): void;
}
