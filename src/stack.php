<?php declare(strict_types=1);

namespace Olifanton\Ton\Marshalling\Tvm;

use Brick\Math\BigInteger;

if (!function_exists("slice")) {
    function slice(\Olifanton\Interop\Boc\Slice $data): Slice
    {
        return new Slice($data);
    }
}

if (!function_exists("cell")) {
    function cell(\Olifanton\Interop\Boc\Cell $data): Cell
    {
        return new Cell($data);
    }
}

if (!function_exists("num")) {
    function num(int|BigInteger $data): Number
    {
        return new Number($data);
    }
}
