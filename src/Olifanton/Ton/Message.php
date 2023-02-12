<?php declare(strict_types=1);

namespace Olifanton\Ton;

use Olifanton\Interop\Boc\Cell;

interface Message
{
    /**
     * @throws \Olifanton\Interop\Boc\Exceptions\BitStringException
     */
    public function writeTo(Cell $cell): void;
}
